<?php
require 'inc/config.php';
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];  // Get product ID passed via the URL

    // Fetch product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if product exists
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Product not found!";
        exit;
    }

    // Fetch images if applicable
    $product_images = json_decode($product['images'], true); // Assuming images are stored as a JSON array
}
?>