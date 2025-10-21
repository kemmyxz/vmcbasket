<?php
session_start();
require 'admin/inc/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

try {
    // Get POST data
    $receipt_no = $_POST['receipt_no'];
    $reason = $_POST['reason'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];

    // Start transaction
    $conn->begin_transaction();

    // Check if refund request already exists
    $check_sql = "SELECT id FROM refund_requests WHERE receipt_no = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('s', $receipt_no);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        throw new Exception('A refund request for this order already exists');
    }

    // Update order status
    $update_order = $conn->prepare("UPDATE order_receipt SET order_status = 'Refund Requested' WHERE receipt_id = ?");
    $update_order->bind_param('s', $receipt_no);
    $update_order->execute();

    // Insert refund request
    $insert_refund = $conn->prepare("INSERT INTO refund_requests (receipt_no, user_id, reason, description, status, created_at) VALUES (?, ?, ?, ?, 'Pending', NOW())");
    $insert_refund->bind_param('siss', $receipt_no, $user_id, $reason, $description);
    $insert_refund->execute();
    $refund_id = $conn->insert_id;

    // Handle image uploads
    if (!empty($_FILES['photos']['name'][0])) {
        $upload_dir = 'admin/uploads/refunds/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            $file_name = uniqid() . '_' . $_FILES['photos']['name'][$key];
            $file_path = $upload_dir . $file_name;

            if (move_uploaded_file($tmp_name, $file_path)) {
                $insert_image = $conn->prepare("INSERT INTO refund_images (refund_id, image_path) VALUES (?, ?)");
                $insert_image->bind_param('is', $refund_id, $file_path);
                $insert_image->execute();
            }
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Refund request submitted successfully']);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error submitting refund request: ' . $e->getMessage()]);
}

$conn->close();