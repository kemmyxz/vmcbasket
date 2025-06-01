<?php
session_start();
require('admin/inc/config.php');

// Ensure content type is JSON and handle any issues with errors/warnings
header('Content-Type: application/json');

// Assuming the user is logged in and user_id is stored in the session
$userId = $_SESSION['user_id'] ?? 1;  // Default value if not set

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $size = trim($_POST['size'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 1);

    // Validation
    if ($product_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }

    // Get product details from database
    $stmt = $conn->prepare("SELECT product_name, price, image FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        // Insert into basket with corrected param types for price
        $stmt = $conn->prepare("INSERT INTO basket (user_id, product_id, product_name, price, image, size, quantity)
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iidsssi", $userId, $product_id, $product['product_name'], $product['price'], $product['image'], $size, $quantity);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
            exit;
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to insert basket']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Product not found']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}
?>
