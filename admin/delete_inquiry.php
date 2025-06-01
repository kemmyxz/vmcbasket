<?php
require 'inc/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    $query = "DELETE FROM inquiries WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}