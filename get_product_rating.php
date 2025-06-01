
<?php
require 'admin/inc/config.php';

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    
    $sql = "SELECT rating FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'rating' => $product['rating'] ?? 0
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Product ID not provided'
    ]);
}
?>