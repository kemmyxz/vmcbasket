<?php

require('admin/inc/config.php');

// Set the content type to JSON
header('Content-Type: application/json');

// Start the session to access the session variables
session_start(); // Make sure the session is started
$userId = $_SESSION['user_id'];

$basketSql = "SELECT * FROM basket WHERE user_id = ?";
$stmt = $conn->prepare($basketSql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$basketResult = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_item') {
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


  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'clear_all') {
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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_qty') {
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




?>