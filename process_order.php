<?php
session_start();
require 'admin/inc/config.php';
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    if (!isset($_SESSION['selected_items']) || empty($_SESSION['selected_items'])) {
        throw new Exception('No items selected for order');
    }

    $userId = $_SESSION['user_id'];
    $paymentMethod = $_POST['payment_method'] ?? 'Cash (Pay at the Counter)';
    $receiptNo = 'ORD-' . time() . '-' . $userId;

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Handle receipt upload if present
        $receiptPath = null;
        if (isset($_FILES['receipt_image']) && $_FILES['receipt_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'admin/uploads/receipts/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileExtension = strtolower(pathinfo($_FILES['receipt_image']['name'], PATHINFO_EXTENSION));
            $newFileName = $receiptNo . '.' . $fileExtension;
            $uploadPath = $uploadDir . $newFileName;

            if (!move_uploaded_file($_FILES['receipt_image']['tmp_name'], $uploadPath)) {
                throw new Exception('Failed to upload receipt');
            }
            
            $receiptPath = $uploadPath;
        }

        // First, create the order_receipt record
        $stmt = $conn->prepare("INSERT INTO order_receipt (receipt_id, order_status) VALUES (?, 'Pending')");
        $stmt->bind_param('s', $receiptNo);
        if (!$stmt->execute()) {
            throw new Exception('Failed to create order receipt record');
        }

        // Process each selected item
        foreach ($_SESSION['selected_items'] as $basketId) {
            // Get basket item with product type
            $stmt = $conn->prepare("
                SELECT b.*, p.type as product_type, u.student_fname, u.student_mname, 
                       u.student_lname, u.student_no, u.email, u.phone_number, u.year_level 
                FROM basket b 
                JOIN products p ON b.product_id = p.id 
                JOIN users u ON b.user_id = u.id
                WHERE b.id = ? AND b.user_id = ?
            ");
            
            $stmt->bind_param('ii', $basketId, $userId);
            $stmt->execute();
            $item = $stmt->get_result()->fetch_assoc();

            if (!$item) {
                throw new Exception('Invalid basket item');
            }

            // Create full customer name
            $customerName = $item['student_fname'] . ' ' . 
                           $item['student_mname'] . ' ' . 
                           $item['student_lname'];

            // Set size from basket table
            $size = ($item['product_type'] === 'Uniform') ? $item['size'] : null;

            // Insert into orders with size
            $stmt = $conn->prepare("
                INSERT INTO orders (
                    receipt_no, product_id, product_name, size, quantity, 
                    price, total_price, user_id, customer_name, school_id,
                    email, phone, payment_method, image,
                    order_date, status
                ) VALUES (
                    ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    CURRENT_DATE, 'Pending'
                )
            ");

            $totalPrice = $item['price'] * $item['quantity'];
            
            $stmt->bind_param('sissiiddssssss', 
                $receiptNo,
                $item['product_id'],
                $item['product_name'],
                $size,
                $item['quantity'],
                $item['price'],
                $totalPrice,
                $userId,
                $customerName,
                $item['student_no'],    // maps to school_id
                $item['email'],         // maps to mail
                $item['phone_number'],  // maps to phone
                $paymentMethod,
                $item['image']
            );

            if (!$stmt->execute()) {
                throw new Exception('Failed to create order: ' . $stmt->error);
            }

            // Delete from basket after successful order
            $stmt = $conn->prepare("DELETE FROM basket WHERE id = ? AND user_id = ?");
            $stmt->bind_param('ii', $basketId, $userId);
            if (!$stmt->execute()) {
                throw new Exception('Failed to remove item from basket');
            }
        }

        // Save receipt image path if exists
        if ($receiptPath) {
            $stmt = $conn->prepare("INSERT INTO order_receipts_images (receipt_id, image_path) VALUES (?, ?)");
            $stmt->bind_param('ss', $receiptNo, $receiptPath);
            if (!$stmt->execute()) {
                throw new Exception('Failed to save receipt image');
            }
        }

        // Commit transaction
        $conn->commit();

        // Clear session data
        unset($_SESSION['selected_items']);

        // Add this before sending the JSON response
        $_SESSION['last_receipt_no'] = $receiptNo;

        // Store user information in session for order completion page
        $_SESSION['order_customer_info'] = [
            'customer_name' => $customerName,
            'student_no' => $item['student_no'],
            'email' => $item['email'],
            'phone_number' => $item['phone_number'],
            'year_level' => $item['year_level']
        ];

        echo json_encode([
            'success' => true,
            'message' => 'Order processed successfully',
            'receipt_no' => $receiptNo
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}