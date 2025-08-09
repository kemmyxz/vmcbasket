<?php
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $otp = $_POST['otp'];

    // Get OTP from database
    $stmt = $conn->prepare("SELECT otp, otp_expiry FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($stored_otp, $otp_expiry);
    $stmt->fetch();

    if ($stored_otp && password_verify($otp, $stored_otp)) {
        if (strtotime($otp_expiry) > time()) {
            echo json_encode(["status" => "success", "message" => "OTP verified! You can now reset your password."]);
        } else {
            echo json_encode(["status" => "error", "message" => "OTP expired."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid OTP."]);
    }
}
?>
