<?php
require 'admin/inc/config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['student_no'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['product_id'])) {
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

// Get product details
$product_sql = "SELECT id, product_name, price, image FROM products WHERE id = ?";
$product_stmt = $conn->prepare($product_sql);
$product_stmt->bind_param("i", $data['product_id']);
$product_stmt->execute();
$product = $product_stmt->get_result()->fetch_assoc();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit();
}

// Check if product already exists in basket
$check_sql = "SELECT id, quantity FROM basket WHERE user_id = ? AND product_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $user_id, $data['product_id']);
$check_stmt->execute();
$existing = $check_stmt->get_result()->fetch_assoc();

if ($existing) {
    // Update quantity
    $new_quantity = $existing['quantity'] + ($data['quantity'] ?? 1);
    $update_sql = "UPDATE basket SET quantity = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $new_quantity, $existing['id']);
    $success = $update_stmt->execute();
} else {
    // Insert new item
    $insert_sql = "INSERT INTO basket (user_id, product_id, product_name, price, image, quantity) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $quantity = $data['quantity'] ?? 1;
    $insert_stmt->bind_param("iisdsi", 
        $user_id, 
        $product['id'],
        $product['product_name'],
        $product['price'],
        $product['image'],
        $quantity
    );
    $success = $insert_stmt->execute();
}

if ($success) {
    echo json_encode([
        'success' => true,
        'message' => 'Product added to basket successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to add product to basket'
    ]);
}

$conn->close();
?>
