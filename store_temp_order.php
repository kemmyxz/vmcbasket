<?php
session_start();
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Please log in to place an order');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid input data');
    }

    // Validate required fields
    $required = ['product_id', 'product_name', 'size', 'quantity', 'price', 'image'];
    foreach ($required as $field) {
        if (!isset($input[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }

    // Store order details in session
    $_SESSION['temp_order'] = [
        'product_id' => (int)$input['product_id'],
        'product_name' => $input['product_name'],
        'size' => $input['size'],
        'quantity' => (int)$input['quantity'],
        'price' => (float)$input['price'],
        'image' => $input['image']
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Order details stored successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}