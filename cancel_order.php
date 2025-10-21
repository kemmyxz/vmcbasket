<?php
session_start();
require 'admin/inc/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not authenticated'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receipt_no = $_POST['receipt_no'] ?? '';
    $reason = $_POST['reason'] ?? '';
    $other_reason = $_POST['other_reason'] ?? '';
    
    if (empty($receipt_no)) {
        echo json_encode([
            'success' => false,
            'message' => 'Receipt number is required'
        ]);
        exit;
    }

    // Validate the order belongs to the current user
    $check_sql = "SELECT o.* FROM orders o 
                  WHERE o.receipt_no = ? AND o.user_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $receipt_no, $_SESSION['user_id']);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid order or unauthorized access'
        ]);
        exit;
    }

    // Combine reasons if "Others" is selected
    $final_reason = $reason;
    if ($reason === 'Others' && !empty($other_reason)) {
        $final_reason .= ": " . $other_reason;
    }

    // Begin transaction
    $conn->begin_transaction();
    try {
        // Update order status in orders table
        $sql1 = "UPDATE orders SET 
                 status = 'Cancelled',
                 cancellation_reason = ?,
                 cancelled_at = CURRENT_TIMESTAMP
                 WHERE receipt_no = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("ss", $final_reason, $receipt_no);
        $stmt1->execute();

        // Update order status in order_receipt table
        $sql2 = "UPDATE order_receipt SET 
                 order_status = 'Cancelled'
                 WHERE receipt_id = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("s", $receipt_no);
        $stmt2->execute();

        // Log the cancellation
        $sql3 = "INSERT INTO order_cancellations 
                 (receipt_no, user_id, reason, cancelled_at)
                 VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt3 = $conn->prepare($sql3);
        $stmt3->bind_param("sis", $receipt_no, $_SESSION['user_id'], $final_reason);
        $stmt3->execute();

        // Return stock to inventory
        $sql4 = "UPDATE product_variants pv 
                 INNER JOIN orders o ON o.product_id = pv.product_id 
                 SET pv.stock = pv.stock + o.quantity 
                 WHERE o.receipt_no = ? AND pv.size = o.size";
        $stmt4 = $conn->prepare($sql4);
        $stmt4->bind_param("s", $receipt_no);
        $stmt4->execute();

        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Order cancelled successfully'
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        
        error_log("Error cancelling order: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while cancelling the order'
        ]);
    }
    exit;
}

// Handle invalid request method
echo json_encode([
    'success' => false,
    'message' => 'Invalid request method'
]);