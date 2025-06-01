<?php
require('inc/config.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Start transaction
        $conn->begin_transaction();

        // Insert into order_receipt table with status 'Complete'
        $receipt_id = $data['receipt_id'];
        $payment_method = $data['payment_method'];
        $customer_name = $data['customer_name'];

        // First, insert into order_receipt table
        $sql = "INSERT INTO order_receipt (receipt_id, order_status) VALUES (?, 'Complete')";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $receipt_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Receipt insert failed: " . $stmt->error);
        }

        // Insert each product into orders table
        foreach ($data['products'] as $product) {
            // Get product image from products table
            $sql = "SELECT image FROM products WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed for product image query: " . $conn->error);
            }
            
            $stmt->bind_param("i", $product['id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $productData = $result->fetch_assoc();
            $productImage = $productData['image'];

            // Insert order with image, payment method and customer name
            $sql = "INSERT INTO orders (receipt_no, product_id, product_name, customer_name, size, price, 
                    total_price, quantity, image, payment_method, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Complete')";
            
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                throw new Exception("Prepare failed for orders: " . $conn->error);
            }

            $stmt->bind_param("sisssdisss", 
                $receipt_id,
                $product['id'],
                $product['name'],
                $customer_name,
                $product['size'],
                $product['price'],
                $product['subtotal'],
                $product['quantity'],
                $productImage,
                $payment_method
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Order insert failed: " . $stmt->error);
            }
        }

        // Commit transaction
        $conn->commit();
        
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        file_put_contents('error_log.txt', date('Y-m-d H:i:s') . ': ' . $e->getMessage() . "\n", FILE_APPEND);
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}