<?php
require 'inc/config.php';

function checkSufficientStock($receipt_id) {
    global $conn;
    
    $sql = "SELECT o.product_id, o.quantity, o.size, pv.stock 
            FROM orders o 
            JOIN product_variants pv ON o.product_id = pv.product_id 
                AND o.size = pv.size 
            WHERE o.receipt_no = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $receipt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if ($row['stock'] < $row['quantity']) {
            return false; // Insufficient stock
        }
    }
    
    return true; // Sufficient stock for all products
}

// Update stock based on product type
if ($product['type'] === 'Supplies') { // Supplies
    $update_sql = "UPDATE product_variants 
                   SET stock = stock - ? 
                   WHERE product_id = ? 
                   AND size IS NULL 
                   AND gender IS NULL";
    
    $stmt_update = $conn->prepare($update_sql);
    $stmt_update->bind_param("ii", 
        $product['quantity'],
        $product['product_id']
    );
} else { // Uniforms
    $update_sql = "UPDATE product_variants 
                   SET stock = stock - ? 
                   WHERE id = ?";
    
    $stmt_update = $conn->prepare($update_sql);
    $stmt_update->bind_param("ii", 
        $product['quantity'],
        $product['variant_id']
    );
}
?>