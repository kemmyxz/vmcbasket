
<?php
require 'inc/config.php';

// Get JSON data from request
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['ids'])) {
    // Bulk delete
    $ids = array_map('intval', $data['ids']);
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete from product_variants
        $stmt = $conn->prepare("DELETE FROM product_variants WHERE product_id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        $stmt->execute();

        // Delete from favorites
        $stmt = $conn->prepare("DELETE FROM favorites WHERE product_id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        $stmt->execute();

        // Delete from basket
        $stmt = $conn->prepare("DELETE FROM basket WHERE product_id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        $stmt->execute();

        // Finally delete the products
        $stmt = $conn->prepare("DELETE FROM products WHERE id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Products deleted successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} elseif (isset($data['id'])) {
    // Single delete
    $id = intval($data['id']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete from product_variants
        $stmt = $conn->prepare("DELETE FROM product_variants WHERE product_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Delete from favorites
        $stmt = $conn->prepare("DELETE FROM favorites WHERE product_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        // Delete from basket
        $stmt = $conn->prepare("DELETE FROM basket WHERE product_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Finally delete the product
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();