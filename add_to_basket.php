<?php
session_start();
require('admin/inc/config.php');

// Ensure content type is JSON and handle any issues with errors/warnings
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['student_no'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data received']);
    exit();
}

// Get user ID from session
$student_no = $_SESSION['student_no'];
$sql = "SELECT id FROM users WHERE student_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_no);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$user_id = $user['id'];

// Check if product already exists in user's basket
$check_sql = "SELECT id, quantity FROM basket WHERE user_id = ? AND product_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $user_id, $data['product_id']);
$check_stmt->execute();
$existing_item = $check_stmt->get_result()->fetch_assoc();

if ($existing_item) {
    // Update quantity if product already exists
    $new_quantity = $existing_item['quantity'] + 1;
    $update_sql = "UPDATE basket SET quantity = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $new_quantity, $existing_item['id']);
    $success = $update_stmt->execute();
} else {
    // Insert new product into basket
    $insert_sql = "INSERT INTO basket (user_id, product_id, product_name, price, image, quantity) VALUES (?, ?, ?, ?, ?, 1)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("iisds", $user_id, $data['product_id'], $data['product_name'], $data['price'], $data['image']);
    $success = $insert_stmt->execute();
}

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Product added to basket successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add product to basket']);
}

$conn->close();
?>
