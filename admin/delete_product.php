<?php
require 'inc/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $product_id = (int)$_POST['id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First delete product variants
        $stmt = $conn->prepare("DELETE FROM product_variants WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();

        // Delete the product from favorites table if it exists
        $stmt = $conn->prepare("DELETE FROM favorites WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();

        // Delete the product from basket if it exists
        $stmt = $conn->prepare("DELETE FROM basket WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        
        // Finally delete the product
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
        } else {
            throw new Exception('Product not found');
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error deleting product: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}