<?php
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);
session_start();
require 'inc/config.php';

try {
    if (!isset($_GET['user_id'])) {
        throw new Exception('No user ID provided');
    }

    $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
    
    // Mark messages as read
    $update_query = "UPDATE inquiries SET is_read = 1 
                    WHERE user_id = ? AND sender = 'user'";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    
    // Fetch messages
    $query = "SELECT i.*, u.student_fname, u.student_lname, u.photo 
              FROM inquiries i 
              JOIN users u ON i.user_id = u.id 
              WHERE i.user_id = ? 
              ORDER BY i.created_at ASC";
    
    $stmt = mysqli_prepare($conn, $query);
    if (!$stmt) {
        throw new Exception('Query preparation failed');
    }
    
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Query execution failed');
    }
    
    $result = mysqli_stmt_get_result($stmt);
    $messages = [];  // Initialize as empty array
    
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = [
            'id' => $row['id'],
            'message' => $row['message'],
            'sender' => $row['sender'],
            'created_at' => date('M d, Y h:i A', strtotime($row['created_at'])),
            'user_name' => $row['student_fname'] . ' ' . $row['student_lname'],
            'photo' => $row['photo']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'messages' => $messages // Always return an array, even if empty
    ]);

} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage(),
        'messages' => [] // Include empty array even on error
    ]);
}