<?php
session_start();
require 'admin/inc/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    // Check if file was uploaded
    if (!isset($_FILES['receipt_image']) || $_FILES['receipt_image']['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'No file uploaded or upload error';
        echo json_encode($response);
        exit;
    }

    $file = $_FILES['receipt_image'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 50 * 1024 * 1024; // 50MB

    // Validate file type and size
    if (!in_array($file['type'], $allowedTypes)) {
        $response['message'] = 'Invalid file type. Only JPG, JPEG, and PNG are allowed.';
        echo json_encode($response);
        exit;
    }

    if ($file['size'] > $maxSize) {
        $response['message'] = 'File is too large. Maximum size is 50MB.';
        echo json_encode($response);
        exit;
    }

    // Create upload directory if it doesn't exist
    $uploadDir = 'admin/uploads/receipts/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique filename using session ID or user ID
    $userId = $_SESSION['user_id'];
    $timestamp = time();
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'receipt_' . $userId . '_' . $timestamp . '.' . $extension;
    $filepath = $uploadDir . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Get the order ID for the current pending order
            $stmt = $conn->prepare("SELECT id FROM orders WHERE user_id = ? AND status = 'Pending' ORDER BY id DESC LIMIT 1");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $order = $result->fetch_assoc();
            
            if (!$order) {
                throw new Exception('No pending order found');
            }

            // Insert into order_receipts_images table
            $stmt = $conn->prepare("INSERT INTO order_receipts_images (receipt_id, image_path, order_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $receiptNo, $filepath, $order['id']);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to store receipt image information');
            }

            // Update the orders table with the receipt reference
            $stmt = $conn->prepare("UPDATE orders SET receipt_no = ? WHERE id = ?");
            $stmt->bind_param("si", $receiptNo, $order['id']);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to update order with receipt information');
            }

            // Commit transaction
            $conn->commit();
            
            $response['success'] = true;
            $response['message'] = 'Receipt uploaded and stored successfully';
            $response['filepath'] = $filepath;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            // Delete the uploaded file since database update failed
            unlink($filepath);
            $response['message'] = 'Error: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Error uploading file';
    }

    echo json_encode($response);
    exit;
}
?>