<?php
require 'inc/config.php';

if (isset($_GET['user_id'])) {
    $userId = mysqli_real_escape_string($conn, $_GET['user_id']);
    
    $query = "SELECT student_no, student_fname, student_lname, year_level, photo, email 
              FROM users 
              WHERE id = '$userId'";
    
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
} else {
    echo json_encode(['error' => 'No user ID provided']);
}