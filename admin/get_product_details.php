
<?php
require 'inc/config.php';

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    
    $stmt = $conn->prepare("SELECT tags, max_quantity FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // Convert tags from JSON if stored as JSON, or from comma-separated string
        $tags = [];
        if ($row['tags']) {
            if (json_decode($row['tags'])) {
                $tags = json_decode($row['tags']);
            } else {
                $tags = array_map('trim', explode(',', $row['tags']));
            }
        }
        
        $response = [
            'tags' => $tags,
            'max_quantity' => $row['max_quantity']
        ];
        
        header('Content-Type: application/json');
        echo json_encode($response);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Product ID not provided']);
}