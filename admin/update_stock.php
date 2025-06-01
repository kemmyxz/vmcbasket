<?php
require 'inc/config.php';

function updateProductStock($receipt_id) {
    global $conn;
    
    // Get all products from this order including product type
    $sql = "SELECT o.product_id, o.quantity, o.size, p.type 
            FROM orders o 
            JOIN products p ON o.product_id = p.id
            WHERE o.receipt_no = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $receipt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        // Different SQL based on product type
        if ($row['type'] == 'Supplies') { // Supplies
            $update_sql = "UPDATE product_variants 
                          SET stock = stock - ? 
                          WHERE product_id = ? AND size IS NULL";
            
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", 
                $row['quantity'], 
                $row['product_id']
            );
        } else { // Uniforms
            $update_sql = "UPDATE product_variants 
                          SET stock = stock - ? 
                          WHERE product_id = ? AND size = ?";
            
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("iis", 
                $row['quantity'], 
                $row['product_id'], 
                $row['size']
            );
        }
        
        if (!$update_stmt->execute()) {
            // Log error or handle it appropriately
            error_log("Failed to update stock for product ID: " . $row['product_id']);
            return false;
        }
    }
    
    return true;
}
?>