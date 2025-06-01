<?php
session_start();
require('admin/inc/config.php');


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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $product_id = intval($_POST['product_id']);
    $size = trim($_POST['size']);
    $quantity = intval($_POST['quantity']);

    if ($product_id <= 0 || $quantity <= 0 || empty($size)) {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }

    // Check if the product exists
    $stmt = $conn->prepare("SELECT product_name, size, price, image FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        // Create order entry
        $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, product_name, size, quantity, price, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $total_price = $product['price'] * $quantity;
        $stmt->bind_param("iissids", $_SESSION['user_id'], $product_id, $product['product_name'], $size, $quantity, $total_price, $product['image']);

        if ($stmt->execute()) {
            // Successfully inserted order
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to place order']);
        }
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => 'Product not found']);
    }
}
?>
