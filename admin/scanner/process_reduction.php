
<?php
header('Content-Type: application/json');
require '../inc/config.php'; // Database connection

try {
    // Get the POST data
    $data = json_decode(file_get_contents('php://input'), true);
    $variant_id = $data['variant_id'] ?? '';

    if (!$variant_id) {
        throw new Exception('Invalid QR code');
    }

    // Start transaction
    $conn->begin_transaction();

    // Get current stock and product info
    $stmt = $conn->prepare("
        SELECT pv.*, p.product_name 
        FROM product_variants pv
        JOIN products p ON p.id = pv.product_id
        WHERE pv.id = ?
    ");
    $stmt->bind_param("i", $variant_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Product variant not found');
    }

    $row = $result->fetch_assoc();

    if ($row['stock'] <= 0) {
        throw new Exception('No stock available');
    }

    // Reduce stock by 1
    $stmt = $conn->prepare("
        UPDATE product_variants 
        SET stock = stock - 1 
        WHERE id = ? AND stock > 0
    ");
    $stmt->bind_param("i", $variant_id);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception('Failed to update stock');
    }

    // Get new stock count
    $stmt = $conn->prepare("SELECT stock FROM product_variants WHERE id = ?");
    $stmt->bind_param("i", $variant_id);
    $stmt->execute();
    $new_stock = $stmt->get_result()->fetch_assoc()['stock'];

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Stock reduced successfully',
        'product_name' => $row['product_name'],
        'new_stock' => $new_stock
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();