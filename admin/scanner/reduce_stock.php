<?php
header('Content-Type: application/json');

// Prevent any unwanted output
ob_clean();

try {
    // Get database connection
    require_once '../../config/connection.php';

    // Get the POST data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['qr_code'])) {
        throw new Exception('QR code not provided');
    }

    $qr_code = $data['qr_code'];

    // Find the product with the matching QR code
    $stmt = $conn->prepare("SELECT id, product_name FROM products WHERE qr_code = ?");
    $stmt->bind_param("s", $qr_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Product not found');
    }

    $product = $result->fetch_assoc();
    
    // Start transaction
    $conn->begin_transaction();

    // Update the stock in product_variants
    $stmt = $conn->prepare("
        UPDATE product_variants 
        SET stock = stock - 1 
        WHERE product_id = ? 
        AND stock > 0 
        LIMIT 1
    ");
    $stmt->bind_param("i", $product['id']);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception('No stock available to reduce');
    }

    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Stock reduced successfully',
        'product_name' => $product['product_name']
    ]);

} catch (Exception $e) {
    if (isset($conn) && $conn->connect_errno === 0) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}