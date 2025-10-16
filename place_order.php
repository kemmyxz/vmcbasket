<?php
require 'admin/inc/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['basket_ids']) || !is_array($_POST['basket_ids'])) {
    echo json_encode(['success' => false, 'error' => 'No items selected']);
    exit;
}

// Store selected items in session
$_SESSION['selected_items'] = $_POST['basket_ids'];
$_SESSION['item_count'] = count($_POST['basket_ids']);

// Store quantities in session
$quantities = [];
foreach ($_POST['basket_ids'] as $id) {
    if (isset($_POST['quantity'][$id])) {
        $quantities[$id] = (int)$_POST['quantity'][$id];
    }
}
$_SESSION['quantities'] = $quantities;

echo json_encode(['success' => true]);
exit;