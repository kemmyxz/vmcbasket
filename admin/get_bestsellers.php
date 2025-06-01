<?php


function getBestSellers($limit = 7) {
    global $conn;
    
    $sql = "SELECT 
        p.product_name,
        COALESCE(SUM(o.quantity), 0) as total_sold
    FROM products p
    LEFT JOIN orders o ON p.id = o.product_id
    WHERE MONTH(o.order_date) = MONTH(CURRENT_DATE)
    AND YEAR(o.order_date) = YEAR(CURRENT_DATE)
    AND o.status = 'Complete'
    GROUP BY p.id, p.product_name
    ORDER BY total_sold DESC
    LIMIT ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $labels = [];
    $data = [];
    
    while($row = $result->fetch_assoc()) {
        $labels[] = $row['product_name'];
        $data[] = $row['total_sold'];
    }
    
    return [
        'labels' => $labels,
        'data' => $data
    ];
}