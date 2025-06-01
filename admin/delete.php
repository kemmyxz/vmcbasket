<?php
// Include your database connection file
require 'inc/config.php';
header('Content-Type: application/json');

// Check if the product ID is passed
if (isset($_POST['id'])) {
    $productId = (int)$_POST['id'];

    // SQL query to delete the product
    $query = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $productId);

    // Execute the deletion
    if ($stmt->execute()) {
        // Return success response
        echo json_encode(['success' => true]);
    } else {
        // Return failure response
        echo json_encode(['success' => false]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Product ID not provided']);
}
?>
