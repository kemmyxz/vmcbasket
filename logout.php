<?php
session_start();
require 'admin/inc/config.php';

// Log the user out and update status to 'Disable'
if (isset($_SESSION['student_no'])) {
    $student_no = $_SESSION['student_no'];

    // Destroy session
    session_destroy();
    header("Location: index.php"); // Redirect to login page
    exit();
} else {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit();
}
?>
