<?php
session_start();
require 'admin/inc/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receipt_no = $_POST['receipt_no'];
    $reason = $_POST['reason'] ?? '';
    $other_reason = $_POST['other_reason'] ?? '';
    
    // Combine reasons if "Others" is selected
    $final_reason = $reason;
    if ($reason === 'Others') {
        $final_reason .= ": " . $other_reason;
    }

    // Begin transaction
    $conn->begin_transaction();
    try {
        // Update order status in orders table
        $sql1 = "UPDATE orders SET status = 'Cancelled' WHERE receipt_no = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("s", $receipt_no);
        $stmt1->execute();

        // Update order status in order_receipt table
        $sql2 = "UPDATE order_receipt SET order_status = 'Cancelled' WHERE receipt_id = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("s", $receipt_no);
        $stmt2->execute();

        // Commit transaction
        $conn->commit();
        
        $response = [
            'success' => true,
            'message' => 'Order cancelled successfully'
        ];
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        $response = [
            'success' => false,
            'message' => 'Error cancelling order: ' . $e->getMessage()
        ];
    }
    
    echo json_encode($response);
    exit;
}