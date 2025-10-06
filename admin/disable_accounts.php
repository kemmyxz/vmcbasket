
<?php
// require 'inc/config.php';

// // Get POST data
// $data = json_decode(file_get_contents('php://input'), true);
// $ids = $data['ids'] ?? [];

// if (empty($ids)) {
//     echo json_encode(['success' => false, 'message' => 'No accounts selected']);
//     exit;
// }

// // Convert array to comma-separated string for SQL
// $id_list = implode(',', array_map('intval', $ids));

// // Update accounts status to Disable
// $sql = "UPDATE users SET active_status = 'Disabled' WHERE id IN ($id_list)";

// if ($conn->query($sql)) {
//     echo json_encode(['success' => true]);
// } else {
//     echo json_encode(['success' => false, 'message' => $conn->error]);
// }

// $conn->close();
?>