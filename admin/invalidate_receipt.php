
<?php
require 'inc/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receipt_id = $_POST['receipt_id'];
    
    // Update order status to Cancelled
    $sql = "UPDATE order_receipt SET order_status = 'Cancelled' WHERE receipt_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $receipt_id);
    
    $response = ['success' => false];
    
    if ($stmt->execute()) {
        // Also update the orders table
        $sql_orders = "UPDATE orders SET status = 'Cancelled' WHERE receipt_no = ?";
        $stmt_orders = $conn->prepare($sql_orders);
        $stmt_orders->bind_param("s", $receipt_id);
        $stmt_orders->execute();
        
        $response['success'] = true;
    }
    
    echo json_encode($response);
}