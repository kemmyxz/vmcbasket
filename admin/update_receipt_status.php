
<?php
require 'inc/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receipt_id'])) {
    $receipt_id = $_POST['receipt_id'];
    
    // Update order_receipt table
    $sql1 = "UPDATE order_receipt SET order_status = 'ToPickUp' WHERE receipt_id = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param('s', $receipt_id);
    $result1 = $stmt1->execute();

    // Update orders table
    $sql2 = "UPDATE orders SET status = 'ToPickUp' WHERE receipt_no = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param('s', $receipt_id);
    $result2 = $stmt2->execute();

    if ($result1 && $result2) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}