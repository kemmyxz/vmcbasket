<?php
session_start();
require 'inc/config.php';


// Destroy session
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>
