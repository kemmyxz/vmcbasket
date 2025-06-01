
<?php
require 'inc/config.php';

if(isset($_GET['month']) && isset($_GET['year'])) {
    $month = $_GET['month'];
    $year = $_GET['year'];
    
    // Update the session to store filter parameters
    session_start();
    $_SESSION['filter_month'] = $month;
    $_SESSION['filter_year'] = $year;
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
}