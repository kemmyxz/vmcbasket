<?php
session_start();
require('admin/inc/config.php');

// Prevent any output before headers
ob_start();

// Set JSON content type and disable error display
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

// Check for session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => true, 'message' => 'Not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Verify database connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed");
    }

    // Handle POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'send') {
            $message = trim($_POST['message']);
            if (empty($message)) {
                throw new Exception("Empty message");
            }

            // Determine sender
            $sender = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] ? 'admin' : 'user';

            $sql = "INSERT INTO inquiries (user_id, message, sender, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                throw new Exception($conn->error);
            }

            $stmt->bind_param("iss", $user_id, $message, $sender);

            if (!$stmt->execute()) {
                throw new Exception($stmt->error);
            }

            echo json_encode(['success' => true]);
            exit();
        }
    }

    // Handle GET requests
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
        if ($_GET['action'] === 'load') {
            $sql = "SELECT i.*, 
                    CASE WHEN i.sender = 'admin' THEN 1 ELSE 0 END as is_admin,
                    DATE_FORMAT(i.created_at, '%h:%i %p, %b %d') as created_at
                    FROM inquiries i
                    WHERE i.user_id = ? 
                    ORDER BY i.created_at ASC";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception($conn->error);
            }
            
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $messages = [];
            while ($row = $result->fetch_assoc()) {
                $messages[] = [
                    'message' => htmlspecialchars($row['message']),
                    'is_admin' => $row['is_admin'],
                    'created_at' => $row['created_at']
                ];
            }
            
            echo json_encode(['success' => true, 'messages' => $messages]);
            exit();
        }
    }

    // If no valid action is specified
    throw new Exception("Invalid action");

} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
    exit();
}