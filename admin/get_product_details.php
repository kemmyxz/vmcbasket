<?php
require 'inc/config.php'; // Database connection

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode([
        'error' => 'Product ID is required'
    ]);
    exit;
}

try {
    $product_id = (int)$_GET['id'];
    
    // Fetch product details including tags and max_quantity
    $stmt = $conn->prepare("SELECT tags, max_quantity FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'error' => 'Product not found'
        ]);
        exit;
    }
    
    $product = $result->fetch_assoc();
    
    // Convert tags string back to array
    $tags = [];
    if (!empty($product['tags'])) {
        $tags = array_map('trim', explode(',', $product['tags']));
    }
    
    echo json_encode([
        'tags' => $tags,
        'max_quantity' => (int)$product['max_quantity']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}