<?php
require 'inc/config.php';

header('Content-Type: application/json');

if (!isset($_POST['receipt_id'])) {
    echo json_encode(['success' => false, 'error' => 'Receipt ID is required']);
    exit;
}

$receipt_id = $_POST['receipt_id'];
$action = $_POST['action'] ?? '';

try {
    if ($action === 'topickup') {
        // Update order status to ToPickUp
        $stmt = $conn->prepare("UPDATE order_receipt SET order_status = 'ToPickUp' WHERE receipt_id = ?");
        $stmt->bind_param('s', $receipt_id);
        
        if ($stmt->execute()) {
            // Also update the status in orders table if needed
            $stmt2 = $conn->prepare("UPDATE orders SET status = 'ToPickUp' WHERE receipt_no = ?");
            $stmt2->bind_param('s', $receipt_id);
            $stmt2->execute();
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update order status']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();