
<?php
require('admin/inc/config.php');
session_start();

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

// Check if product is already in favorites
$check_sql = "SELECT id, favorite FROM favorites WHERE user_id = ? AND product_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $user_id, $data['product_id']);
$check_stmt->execute();
$existing = $check_stmt->get_result()->fetch_assoc();

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
    $insert_stmt->bind_param("ii", $user_id, $data['product_id']);
    $success = $insert_stmt->execute();
    $new_status = 1;
}

if ($success) {
    echo json_encode([
        'success' => true, 
        'isFavorite' => $new_status == 1,
        'message' => $new_status == 1 ? 'Added to favorites' : 'Removed from favorites'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update favorites']);
}

$conn->close();