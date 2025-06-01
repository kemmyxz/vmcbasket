<?php
session_start();
require('admin/inc/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get user ID and order ID
        $user_id = $_SESSION['user_id'] ?? null;
        $order_id = $_POST['order_id'] ?? null;

        if (!$user_id || !$order_id) {
            throw new Exception('Missing required information');
        }

        // Prepare and execute delete statement
        $stmt = $conn->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $order_id, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Failed to delete order');
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>