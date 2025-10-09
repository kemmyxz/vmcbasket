<?php
require('admin/inc/config.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['product_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data received']);
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $data['product_id'];

// Check if product is already in favorites
$check_sql = "SELECT id, favorite FROM favorites WHERE user_id = ? AND product_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $user_id, $product_id);
$check_stmt->execute();
$result = $check_stmt->get_result();
$existing = $result->fetch_assoc();

if ($existing) {
    // Toggle favorite status
    $new_status = $existing['favorite'] == 1 ? 0 : 1;
    $update_sql = "UPDATE favorites SET favorite = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ii", $new_status, $existing['id']);
    $success = $update_stmt->execute();
} else {
    // Insert new favorite
    $insert_sql = "INSERT INTO favorites (user_id, product_id, favorite) VALUES (?, ?, 1)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ii", $user_id, $product_id);
    $success = $insert_stmt->execute();
    $new_status = 1;
}

// Make sure we send a proper JSON response
header('Content-Type: application/json');

if ($success) {
    echo json_encode([
        'success' => true,
        'isFavorite' => $new_status == 1,
        'message' => $new_status == 1 ? 'Added to favorites' : 'Removed from favorites'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $conn->error
    ]);
}

$conn->close();



