<?php
require 'inc/config.php';

if (isset($_GET['receipt_id'])) {
    $receipt_id = $_GET['receipt_id'];
    
    $sql = "SELECT order_status FROM order_receipt WHERE receipt_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $receipt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['status' => $row['order_status']]);
    } else {
        echo json_encode(['status' => 'not_found']);
    }
}