
<?php
require 'admin/inc/config.php';
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['receipt_id']) || !isset($data['items'])) {
        throw new Exception('Invalid QR code data');
    }

    $conn->begin_transaction();

    // Verify receipt exists and is in ToPickUp status
    $check_receipt = $conn->prepare("
        SELECT order_status FROM order_receipt 
        WHERE receipt_id = ? AND order_status = 'ToPickUp'
    ");
    $check_receipt->bind_param("s", $data['receipt_id']);
    $check_receipt->execute();
    $result = $check_receipt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Invalid receipt or wrong status');
    }

    // Process each item
    foreach ($data['items'] as $item) {
        // Update stock
        $update_stock = $conn->prepare("
            UPDATE product_variants 
            SET stock = stock - ? 
            WHERE id = ? AND stock >= ?
        ");
        $update_stock->bind_param("iii", 
            $item['quantity'], 
            $item['variant_id'], 
            $item['quantity']
        );
        $update_stock->execute();

        if ($update_stock->affected_rows === 0) {
            throw new Exception('Insufficient stock for one or more items');
        }
    }

    // Update receipt status
    $update_receipt = $conn->prepare("
        UPDATE order_receipt 
        SET order_status = 'Complete' 
        WHERE receipt_id = ?
    ");
    $update_receipt->bind_param("s", $data['receipt_id']);
    $update_receipt->execute();

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($conn->connect_errno) {
        $conn->rollback();
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}