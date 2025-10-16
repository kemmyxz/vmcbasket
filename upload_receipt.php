
<?php
session_start();
require 'admin/inc/config.php';

header('Content-Type: application/json');

try {
    if (!isset($_FILES['receipt_image'])) {
        throw new Exception('No receipt image uploaded');
    }

    $file = $_FILES['receipt_image'];
    
    // Validate file
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Only JPG, JPEG, and PNG are allowed.');
    }

    // Create upload directory if it doesn't exist
    $uploadDir = 'admin/uploads/receipts/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Generate unique filename
    $filename = uniqid() . '_' . time() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Failed to upload file');
    }

    // Store receipt information in session for order processing
    $_SESSION['receipt_image'] = $filename;

    echo json_encode([
        'success' => true,
        'message' => 'Receipt uploaded successfully',
        'filename' => $filename
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}