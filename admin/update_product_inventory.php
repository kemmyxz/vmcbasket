
<?php
require 'inc/config.php';

function updateInventoryAfterOrder($receipt_id) {
    global $conn;
    
    // Get order details with product variants
    $sql = "SELECT o.product_name, o.quantity, o.size, p.id as product_id 
            FROM orders o 
            JOIN products p ON o.product_name = p.product_name 
            WHERE o.receipt_no = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $receipt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($order = $result->fetch_assoc()) {
        // Update stock in product_variants table
        $update_sql = "UPDATE product_variants 
                      SET stock = stock - ? 
                      WHERE product_id = ? AND size = ?";
                      
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("iis", 
            $order['quantity'],
            $order['product_id'],
            $order['size']
        );
        
        if (!$update_stmt->execute()) {
            return false;
        }
    }
    
    return true;
}