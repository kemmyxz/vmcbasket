<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Load PHPMailer via Composer

$mail = new PHPMailer(true);

try {
    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // ✅ Correct Gmail SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'lopanggokem@gmail.com'; // ✅ Your Gmail email
    $mail->Password = 'ptik yanf brbo mxkn'; // ✅ Use 16-character App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // ✅ TLS Encryption
    $mail->Port = 587; // ✅ Use 587 for TLS or 465 for SSL

    // Enable debugging (optional, for troubleshooting)
    $mail->SMTPDebug = 2; // 0 = No debug, 2 = Detailed debug output
    $mail->Debugoutput = 'html'; 

    // Sender & Recipient
    $mail->setFrom('lopanggokem@gmail.com', 'VMC BASKET'); // ✅ Your Gmail address
    $mail->addAddress('lopanggokem@gmail.com', 'KEM ENTIC'); // ✅ Change this to the recipient's email

    // Email Content
    $mail->isHTML(true);
    $mail->Subject = 'VMC BASKET Test Email';
    $otp = rand(100000, 999999); // Generate a 6-digit OTP
    $mail->Body = "Hello, this is a recovery code email from VMC BASKET.<br>Your OTP code is: <strong>$otp</strong>";

    // Send Email
    if ($mail->send()) {
        echo '✅ Email sent successfully!';
    } else {
        echo '❌ Email sending failed!';
    }
} catch (Exception $e) {
    echo "❌ Email sending failed: {$mail->ErrorInfo}";
}
?>