
<?php
session_start();
require 'admin/inc/config.php';

// Check if user is logged in
if (!isset($_SESSION['student_no'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if required parameters are present
if (!isset($_POST['receipt_no']) || !isset($_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$receipt_no = $_POST['receipt_no'];
$status = $_POST['status'];

try {
    // Begin transaction
    $conn->begin_transaction();

    // Update order_receipt table
    $sql = "UPDATE order_receipt SET order_status = ? WHERE receipt_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $status, $receipt_no);
    $stmt->execute();

    // Update orders table
    $sql = "UPDATE orders SET status = ? WHERE receipt_no = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $status, $receipt_no);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>