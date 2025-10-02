<?php
session_start();
require 'inc/config.php';

if (isset($_GET['user_id'])) {
    $user_id = mysqli_real_escape_string($conn, $_GET['user_id']);
    
    // Mark messages as read
    $update_query = "UPDATE inquiries SET is_read = 1 
                    WHERE user_id = ? AND is_admin = 0";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    
    // Fetch messages
    $query = "SELECT i.*, u.student_fname, u.student_lname, u.photo 
              FROM inquiries i 
              JOIN users u ON i.user_id = u.id 
              WHERE i.user_id = ? 
              ORDER BY i.created_at DESC";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $messages = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $messages[] = array(
            'id' => $row['id'],
            'message' => $row['message'],
            'is_admin' => $row['is_admin'],
            'created_at' => date('M d, Y h:i A', strtotime($row['created_at'])),
            'user_name' => $row['student_fname'] . ' ' . $row['student_lname'],
            'photo' => $row['photo']
        );
    }
    
    echo json_encode($messages);
}