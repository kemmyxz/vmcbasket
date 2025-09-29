
<?php
require 'inc/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['ids']) || empty($data['ids'])) {
        echo json_encode(['success' => false, 'message' => 'No IDs provided']);
        exit;
    }
    
    $ids = array_map('intval', $data['ids']);
    $idList = implode(',', $ids);
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Delete related records first
        // Delete from orders
        $conn->query("DELETE FROM orders WHERE user_id IN ($idList)");
        
        // Delete from product_reviews
        $conn->query("DELETE FROM product_reviews WHERE user_id IN ($idList)");
        
        // Delete from inquiries
        $conn->query("DELETE FROM inquiries WHERE user_id IN ($idList)");
        
        // Delete from favorites
        $conn->query("DELETE FROM favorites WHERE user_id IN ($idList)");
        
        // Delete from basket
        $conn->query("DELETE FROM basket WHERE user_id IN ($idList)");
        
        // Finally, delete the users
        $result = $conn->query("DELETE FROM users WHERE id IN ($idList)");
        
        if ($result) {
            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => count($ids) . ' user(s) have been deleted successfully'
            ]);
        } else {
            throw new Exception("Failed to delete users");
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
    
    $conn->close();
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request method']);