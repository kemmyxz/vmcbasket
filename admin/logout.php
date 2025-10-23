<?php
session_start();
require 'inc/config.php';

// Log the user out and update status to 'Disable'
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];

    // Destroy session
    session_destroy();
    header("Location: login.php"); // Redirect to login page
    exit();
} else {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}
?>
