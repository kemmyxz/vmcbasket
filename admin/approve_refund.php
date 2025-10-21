
<?php
require 'inc/config.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $receipt_id = $data['receipt_id'];
    $products = $data['products'];

    // Start transaction
    $conn->begin_transaction();

    // Update order status
    $update_status = $conn->prepare("UPDATE order_receipt SET order_status = 'Refunded' WHERE receipt_id = ?");
    $update_status->bind_param('s', $receipt_id);
    $update_status->execute();

    // Update product quantities
    $update_quantity = $conn->prepare("
        UPDATE product_variants 
        SET stock = stock + ? 
        WHERE product_id = ? AND id = ?
    ");

    foreach ($products as $product) {
        $update_quantity->bind_param('iii', 
            $product['quantity'], 
            $product['product_id'],
            $product['product_variant_id']
        );
        $update_quantity->execute();
    }

    // Update refund request status
    $update_refund = $conn->prepare("
        UPDATE refund_requests 
        SET status = 'Approved', 
            updated_at = NOW() 
        WHERE receipt_no = ?
    ");
    $update_refund->bind_param('s', $receipt_id);
    $update_refund->execute();

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($conn->connect_error) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();