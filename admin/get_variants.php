<?php
// get_variants.php - Returns all variants for a specific product

require 'inc/config.php'; // Database connection

// Check if product_id is provided
if (!isset($_GET['product_id'])) {
    echo json_encode(['error' => 'Product ID is required']);
    exit;
}

$product_id = $_GET['product_id'];

// Prepare and execute the query
$stmt = $conn->prepare("SELECT id, size, gender, stock FROM product_variants WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch all variants
$variants = [];
while ($row = $result->fetch_assoc()) {
    $variants[] = $row;
}

// Return as JSON
header('Content-Type: application/json');
echo json_encode($variants);
?>