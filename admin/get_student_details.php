
<?php
require 'inc/config.php';

if(isset($_POST['student_id'])) {
    $student_id = $_POST['student_id'];
    
    // Get student details
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
    
    // Get student's orders with search parameter
    $search = isset($_POST['search']) ? '%' . $_POST['search'] . '%' : '%';
    
    $order_stmt = $conn->prepare("
        SELECT o.*, p.image as product_image, pr.rating, pr.review_text 
        FROM orders o
        LEFT JOIN products p ON o.product_id = p.id
        LEFT JOIN product_reviews pr ON o.id = pr.order_id
        WHERE o.user_id = ? 
        AND (o.product_name LIKE ? OR o.receipt_no LIKE ?)
        ORDER BY o.order_date DESC
    ");
    
    $order_stmt->bind_param("iss", $student_id, $search, $search);
    $order_stmt->execute();
    $orders = $order_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $response = [
        'student' => $student,
        'orders' => $orders
    ];
    
    echo json_encode($response);
}