<?php
// Include database connection
require('admin/inc/config.php');

// Set the content type to JSON
header('Content-Type: application/json');

// Start the session to access the session variables
session_start(); // Make sure the session is started

// Check if the user is logged in by verifying if user_id is in the session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Get the user ID from the session (this should be set during the login process)
$userId = $_SESSION['user_id'];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get product ID and favorite status from the POST data
    $product_id = intval($_POST['product_id'] ?? 0);
    $favoriteStatus = intval($_POST['favorite'] ?? 0);  // 1 for favorited, 0 for unfavorited

    // Validate the incoming data
    if ($product_id <= 0 || ($favoriteStatus !== 1 && $favoriteStatus !== 0)) {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }

    // Check if the product is already in favorites for the current user
    $stmt = $conn->prepare("SELECT * FROM favorites WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // If the product exists in favorites, update its status
        $stmt = $conn->prepare("UPDATE favorites SET favorite = ? WHERE user_id = ? AND product_id = ?");
        $stmt->bind_param("iii", $favoriteStatus, $userId, $product_id);
    } else {
        // If the product doesn't exist in favorites, insert it
        $stmt = $conn->prepare("INSERT INTO favorites (user_id, product_id, favorite) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $userId, $product_id, $favoriteStatus);
    }

    // Execute the query and return success or error
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update favorite']);
    }
    exit;
} else {
    // Handle invalid request method
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}
?>
