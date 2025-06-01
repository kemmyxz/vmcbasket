
<?php
require 'inc/config.php';

if (isset($_GET['receipt_id'])) {
    $receipt_id = $_GET['receipt_id'];
    
    $sql = "SELECT image_path FROM order_receipts_images WHERE receipt_id = ? ORDER BY uploaded_at DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $receipt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'image_path' => $row['image_path']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No receipt image found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Receipt ID not provided']);
}
?>