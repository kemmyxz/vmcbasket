<?php
header('Content-Type: application/json');
error_reporting(0); // Disable error reporting
ini_set('display_errors', 0); // Don't display errors

require 'inc/config.php';

try {
    if (!isset($_GET['user_id'])) {
        throw new Exception('No user ID provided');
    }

    $userId = mysqli_real_escape_string($conn, $_GET['user_id']);
    
    $query = "SELECT student_no, student_fname, student_lname, year_level, photo, email 
              FROM users 
              WHERE id = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception('Query preparation failed');
    }
    
    mysqli_stmt_bind_param($stmt, "i", $userId);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Query execution failed');
    }
    
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode($row);
    } else {
        throw new Exception('User not found');
    }

} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}