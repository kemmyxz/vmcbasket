<?php
session_start();
require 'admin/inc/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    // Check if receipt_no is provided
    if (!isset($_POST['receipt_no'])) {
        $response['message'] = 'Receipt number is required';
        echo json_encode($response);
        exit;
    }

    $receipt_no = $_POST['receipt_no'];
    
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

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'receipt_' . $receipt_no . '_' . time() . '.' . $extension;
    $filepath = $uploadDir . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Store file info in database
        $sql = "INSERT INTO order_receipts_images (receipt_id, image_path) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $receipt_no, $filepath);
        
        if ($stmt->execute()) {
            // Update order status to 'ToPickUp'
            $updateSql = "UPDATE order_receipt SET order_status = 'Pending' WHERE receipt_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("s", $receipt_no);
            $updateStmt->execute();

            $response['success'] = true;
            $response['message'] = 'Receipt uploaded successfully';
        } else {
            $response['message'] = 'Error saving to database';
        }
    } else {
        $response['message'] = 'Error uploading file';
    }

    echo json_encode($response);
    exit;
}
?>