
<?php
session_start();
require('admin/inc/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'User not logged in']);
        exit;
    }

    $payment_method = $_POST['payment_method'];
    $user_id = $_SESSION['user_id'];

    // Update payment method for pending orders
    $stmt = $conn->prepare("UPDATE orders SET payment_method = ? WHERE user_id = ? AND receipt_no IS NULL");
    $stmt->bind_param("si", $payment_method, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update payment method']);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request method']);