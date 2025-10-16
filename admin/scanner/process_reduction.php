<?php
header('Content-Type: application/json');
require '../inc/config.php'; // Database connection

try {
    // Get the POST data
    $data = json_decode(file_get_contents('php://input'), true);
    $variant_id = $data['variant_id'] ?? '';
    $quantity = $data['quantity'] ?? 1; // Default to 1 if not specified
    $receipt_id = $data['receipt_id'] ?? null; // Get receipt_id from QR data

    if (!$variant_id) {
        throw new Exception('Invalid QR code');
    }

    if (!is_numeric($quantity) || $quantity <= 0) {
        throw new Exception('Invalid quantity');
    }

    // Start transaction
    $conn->begin_transaction();

    // Get current stock and product info
    $stmt = $conn->prepare("
        SELECT pv.*, p.product_name 
        FROM product_variants pv
        JOIN products p ON p.id = pv.product_id
        WHERE pv.id = ?
    ");
    $stmt->bind_param("i", $variant_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Product variant not found');
    }

    $row = $result->fetch_assoc();

    if ($row['stock'] < $quantity) {
        throw new Exception('Insufficient stock available');
    }

    // Reduce stock by specified quantity
    $stmt = $conn->prepare("
        UPDATE product_variants 
        SET stock = stock - ? 
        WHERE id = ? AND stock >= ?
    ");
    $stmt->bind_param("iii", $quantity, $variant_id, $quantity);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception('Failed to update stock');
    }

    // Update order receipt status if receipt_id is provided
    if ($receipt_id) {
        $stmt = $conn->prepare("
            UPDATE order_receipt 
            SET order_status = 'Complete' 
            WHERE receipt_id = ? AND order_status != 'Complete'
        ");
        $stmt->bind_param("s", $receipt_id);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            // Log but don't throw exception as stock update was successful
            error_log("Failed to update receipt status or already completed: " . $receipt_id);
        }
    }

    // Get new stock count
    $stmt = $conn->prepare("SELECT stock FROM product_variants WHERE id = ?");
    $stmt->bind_param("i", $variant_id);
    $stmt->execute();
    $new_stock = $stmt->get_result()->fetch_assoc()['stock'];

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Stock reduced successfully',
        'product_name' => $row['product_name'],
        'quantity' => $quantity,
        'new_stock' => $new_stock,
        'receipt_updated' => ($receipt_id !== null)
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();