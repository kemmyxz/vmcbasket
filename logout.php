<?php
session_start();
require 'admin/inc/config.php';

// Log the user out and update status to 'Inactive'
if (isset($_SESSION['student_no'])) {
    $student_no = $_SESSION['student_no'];

    // Update status to inactive on logout
    $update_status_sql = "UPDATE users SET active_status = 'Inactive' WHERE student_no = ?";
    $update_status_stmt = $conn->prepare($update_status_sql);
    $update_status_stmt->bind_param("s", $student_no);
    $update_status_stmt->execute();
    $update_status_stmt->close();

    // Destroy session
    session_destroy();
    header("Location: login.php"); // Redirect to login page
    exit();
} else {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}
?>
