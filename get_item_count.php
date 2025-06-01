<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemCount = isset($_POST['item_count']) ? (int)$_POST['item_count'] : 0;
    
    // Store the count in session to persist it between requests
    session_start();
    $_SESSION['item_count'] = $itemCount;
    
    exit;
}
?>