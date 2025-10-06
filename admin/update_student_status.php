
<?php
require 'inc/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bulk_disable') {
    try {
        $ids = json_decode($_POST['ids'], true);
        
        if (!is_array($ids) || empty($ids)) {
            throw new Exception('No valid IDs provided');
        }

        // Prepare the SQL statement
        $sql = "UPDATE users SET active_status = 'Disabled' WHERE id IN (" . 
               str_repeat('?,', count($ids) - 1) . '?)';
        
        $stmt = $conn->prepare($sql);
        
        // Bind parameters
        $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Failed to update status');
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}