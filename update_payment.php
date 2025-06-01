<?php
session_start();
require('admin/inc/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'User not logged in']);
        exit;
    }

    $payment_method = $_POST['payment_method'];
    $order_id = intval($_POST['order_id']);

    // Validate order belongs to user
    $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid order']);
        exit;
    }

    // Update payment method
    $update_stmt = $conn->prepare("UPDATE orders SET payment_method = ? WHERE id = ?");
    $update_stmt->bind_param("si", $payment_method, $order_id);

    if ($update_stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update payment method']);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request method']);
?>
