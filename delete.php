<?php

require('admin/inc/config.php');

session_start();

// Set the content type to JSON before any output
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = $_SESSION['user_id'] ?? 0;

    if ($action === 'clear_selected') {
        try {
            $ids = json_decode($_POST['ids'], true);

            if (!is_array($ids)) {
                throw new Exception('Invalid item IDs');
            }

            // Prepare the SQL with multiple placeholders
            $placeholders = str_repeat('?,', count($ids) - 1) . '?';
            $sql = "DELETE FROM basket WHERE id IN ($placeholders) AND user_id = ?";

            // Prepare statement
            $stmt = $conn->prepare($sql);

            // Create array of parameters including user_id at the end
            $params = array_merge($ids, [$userId]);

            // Bind parameters dynamically
            $types = str_repeat('i', count($params));
            $stmt->bind_param($types, ...$params);

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
                exit;
            } else {
                throw new Exception('Failed to delete items');
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false, 
                'error' => $e->getMessage()
            ]);
            exit;
        }
    }

    if ($action === 'remove_item') {
        $itemId = intval($_POST['id'] ?? 0);
        if ($itemId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid item ID']);
            exit;
        }

        // Remove the item from the basket
        $stmt = $conn->prepare("DELETE FROM basket WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $itemId, $userId);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to remove item']);
        }
        exit;
    }

    if ($action === 'clear_all') {
        // Remove all items from the basket for the current user
        $stmt = $conn->prepare("DELETE FROM basket WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to clear all items']);
        }
        exit;
    }

    if ($action === 'update_qty') {
        $itemId = intval($_POST['id'] ?? 0);
        $newQuantity = intval($_POST['quantity'] ?? 0);

        if ($itemId <= 0 || $newQuantity <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid item ID or quantity']);
            exit;
        }

        // Update the quantity in the basket
        $stmt = $conn->prepare("UPDATE basket SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $newQuantity, $itemId, $userId);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update quantity']);
        }
        exit;
    }
}

exit;
?>