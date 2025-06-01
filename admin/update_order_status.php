<?php
require 'inc/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receipt_id'])) {
    $receipt_id = $_POST['receipt_id'];
    
    $conn->begin_transaction();
    
    try {
        // Modified SQL query to first get variant ID then stock
        $sql_get_products = "SELECT 
            o.product_id, 
            o.quantity, 
            o.size, 
            p.type, 
            p.product_name,
            COALESCE(
                CASE p.type 
                    WHEN 'Supplies' THEN (
                        SELECT pv.id 
                        FROM product_variants pv 
                        WHERE pv.product_id = o.product_id 
                        AND (pv.size IS NULL OR pv.size = '')
                        LIMIT 1
                    )
                    ELSE (
                        SELECT pv.id 
                        FROM product_variants pv 
                        WHERE pv.product_id = o.product_id 
                        AND pv.size = o.size
                        LIMIT 1
                    )
                END
            ) as variant_id,
            COALESCE(
                CASE p.type
                    WHEN 'Supplies' THEN (
                        SELECT pv.stock
                        FROM product_variants pv 
                        WHERE pv.product_id = o.product_id 
                        AND (pv.size IS NULL OR pv.size = '')
                        LIMIT 1
                    )
                    ELSE (
                        SELECT pv.stock
                        FROM product_variants pv 
                        WHERE pv.product_id = o.product_id 
                        AND pv.size = o.size
                        LIMIT 1
                    )
                END
            ) as current_stock
            FROM orders o 
            JOIN products p ON o.product_id = p.id
            WHERE o.receipt_no = ?";
        
        $stmt_products = $conn->prepare($sql_get_products);
        $stmt_products->bind_param("s", $receipt_id);
        $stmt_products->execute();
        $products_result = $stmt_products->get_result();
        
        $stock_updates = [];
        $insufficient_stock = [];
        
        // Check stock availability for all products
        while ($product = $products_result->fetch_assoc()) {
            if ($product['current_stock'] === null) {
                $insufficient_stock[] = [
                    'product_name' => $product['product_name'],
                    'error' => 'No stock record found'
                ];
                continue;
            }
            
            // Convert to integers for proper comparison
            $current_stock = (int)$product['current_stock'];
            $requested_quantity = (int)$product['quantity'];
            
            if ($current_stock < $requested_quantity) {
                $insufficient_stock[] = [
                    'product_name' => $product['product_name'],
                    'requested' => $requested_quantity,
                    'available' => $current_stock,
                    'type' => $product['type']
                ];
                continue;
            }
            
            // Store stock update information
            $stock_updates[] = [
                'product_name' => $product['product_name'],
                'old_stock' => $current_stock,
                'new_stock' => $current_stock - $requested_quantity
            ];
        }
        
        // If any products have insufficient stock, throw exception
        if (!empty($insufficient_stock)) {
            throw new Exception(json_encode([
                'message' => 'insufficient_stock',
                'details' => $insufficient_stock
            ]));
        }
        
        // Update stock for each product
        $products_result->data_seek(0);
        while ($product = $products_result->fetch_assoc()) {
            // Simplified update query using variant_id
            $update_sql = "UPDATE product_variants 
                          SET stock = stock - ? 
                          WHERE id = ?";
            
            $stmt_update = $conn->prepare($update_sql);
            $stmt_update->bind_param("ii", 
                $product['quantity'],
                $product['variant_id']
            );
            
            if (!$stmt_update->execute()) {
                throw new Exception("Failed to update stock for " . $product['product_name']);
            }
        }
        
        // Update order statuses
        $update_receipt_sql = "UPDATE order_receipt 
                             SET order_status = 'Complete' 
                             WHERE receipt_id = ?";
        
        $stmt_receipt = $conn->prepare($update_receipt_sql);
        $stmt_receipt->bind_param("s", $receipt_id);
        
        if (!$stmt_receipt->execute()) {
            throw new Exception("Failed to update order status");
        }
        
        $update_orders_sql = "UPDATE orders 
                            SET status = 'Complete' 
                            WHERE receipt_no = ?";
        
        $stmt_orders = $conn->prepare($update_orders_sql);
        $stmt_orders->bind_param("s", $receipt_id);
        
        if (!$stmt_orders->execute()) {
            throw new Exception("Failed to update orders status");
        }
        
        // Commit transaction
        $conn->commit();
        
        // Return success with stock updates
        echo json_encode([
            'success' => true,
            'stockUpdates' => $stock_updates
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        $error_data = json_decode($e->getMessage(), true);
        if ($error_data && isset($error_data['message']) && $error_data['message'] === 'insufficient_stock') {
            echo json_encode([
                'success' => false,
                'error' => 'insufficient_stock',
                'details' => $error_data['details']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request'
    ]);
}

$conn->close();