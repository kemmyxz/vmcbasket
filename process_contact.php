
<?php
session_start();
require('admin/inc/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $userId = $_SESSION['user_id'];
    $message = trim($_POST['message']);

    // Insert the inquiry into database
    $query = "INSERT INTO inquiries (user_id, message) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $userId, $message);

    if ($stmt->execute()) {
        // Set success message in session
        $_SESSION['message'] = "Your message has been sent successfully!";
        header("Location: contact.php");
    } else {
        // Set error message in session
        $_SESSION['error'] = "Failed to send message. Please try again.";
        header("Location: contact.php");
    }
    exit();
}