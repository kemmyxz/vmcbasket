<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require 'admin/inc/config.php';

$response = "";

// Step 1: Send OTP
if (isset($_POST['send_otp'])) {
    $email = $_POST['email'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $otp = rand(100000, 999999);
        $otp_hash = password_hash($otp, PASSWORD_BCRYPT);
        $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        // Store OTP in database
        $update_stmt = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?");
        $update_stmt->bind_param("sss", $otp_hash, $expiry, $email);
        $update_stmt->execute();

        // Send OTP via Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'k.lopanggo14@gmail.com';
            $mail->Password = 'ptik yanf brbo mxkn';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->setFrom('k.lopanggo14@gmail.com', 'VMC BASKET');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset OTP';
            $mail->Body = "Your OTP for password reset is: <strong>$otp</strong>. It is valid for 10 minutes.";

            if ($mail->send()) {
                $response = "OTP sent successfully!";
            } else {
                $response = "Failed to send OTP.";
            }
        } catch (Exception $e) {
            $response = "Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $response = "Email not found!";
    }
}

// Step 2: Verify OTP
if (isset($_POST['verify_otp'])) {
    $email = $_POST['email'];
    $otp = $_POST['otp'];

    $stmt = $conn->prepare("SELECT otp, otp_expiry FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($stored_otp, $otp_expiry);
    $stmt->fetch();

    if ($stored_otp && password_verify($otp, $stored_otp)) {
        if (strtotime($otp_expiry) > time()) {
            $response = "OTP verified! You can now reset your password.";
        } else {
            $response = "OTP expired!";
        }
    } else {
        $response = "Invalid OTP!";
    }
}

// Step 3: Reset Password
if (isset($_POST['reset_password'])) {
    $email = $_POST['email'];
    $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE users SET student_pass = ?, otp = NULL, otp_expiry = NULL WHERE email = ?");
    $stmt->bind_param("ss", $new_password, $email);
    
    if ($stmt->execute()) {
        $response = "Password reset successful!";
        header("Location: login.php");
    } else {
        $response = "Error updating password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="CSS/style.css">
<style>
/*For Reset Password*/
.resetpassBG {
    background: url('./admin/images/userlogin/LoginBG.png') no-repeat center center/cover;
}

.reset-container {
    width: 85%;
    background: white;
    -webkit-box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    -moz-box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    overflow: hidden;
}

.reset-title {
    font-family: "Ubuntu", sans-serif;
    font-size: 35px;
    color: #003153;
}

.logo-title-right{
    font-family: "Ubuntu", sans-serif;
    font-size: 30px;
    color: #FFFFFF;
}

.reset-title-right{
    font-family: "Ubuntu", sans-serif;
    font-size: 35px;
    color: #FFFFFF;
}

.reset-info {
    background: url('./admin/images/userlogin/Log in Picture.png') no-repeat center center/cover;
    position: relative;
}

.reset-input {
    padding: 12px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

input.form-control {
    border-radius: 5px;
    padding: 10px;
    font-size: 16px;
}

.otp-input.form-control {
    border-radius: 5px;
    padding: 0px;
    font-size: 16px;
}

.logo-circle {
    background: white;
    width: 120px;
    height: 70px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.vmc-logo {
    width: 60px;
}

/* Right Side Text */
.reset-text {
    font-size: 14px;
    max-width: 80%;
}

.otp-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.otp-input {
    width: 50px;
    height: 50px;
    text-align: center;
    font-size: 15px;
    border: 2px solid #000;
    margin: 5px;
    border-radius: 5px;
}
.resend-link {
    text-decoration: none;
    color: #007bff;
    font-weight: bold;
    cursor: pointer;
}
.resend-link:hover {
    text-decoration: underline;
}
.btn-submit {
    width: 50%;
    padding: 10px;
    font-size: 18px;
    border-radius: 5px;
    text-align: center;
}

.successful_icon{
    width: 50%;
}
</style>
</head>
<body class="resetpassBG">
<div class="container-fluid d-flex align-items-center justify-content-center vh-100">
    

        <div class="reset-container" id="step1">
            <div class="row g-4">
                <!-- Left Side: Form -->
                <div class="col-md-7 p-5 d-flex flex-column justify-content-center text-center">
                    <h2 class="fw-bold reset-title">Reset Password</h2>
                    <p class="text-muted text-center">Please enter your email address and we will send the OTP for you to reset your password.</p>

                    <form method="POST" action="">
                        <div class="mb-4 mt-5">
                        <input type="email" name="email" class="form-control" placeholder="Enter your email address" required>
                        </div>

                        <div class="d-flex justify-content-center gap-3 mt-5">
                            
                            <button type="submit" class="btn btn-primary btn-submit w-50" name="send_otp">Continue</button>
                        </div>
                    </form>
                </div>

                <!-- Right Side: Image and Info -->
                <div class="col-md-5 d-none d-md-block reset-info">

                    <div class="d-flex justify-space-between align-items-center ms-3 mt-3"> 
                       <div class="logo-circle">
                       <img src="admin/images/Admin Nav/VMS-LOGO-Alternative-03.png" alt="VMC Basket Logo" class="vmc-logo">
                       </div>
                        <h4 class="logo-title-right ms-2">VMC Basket</h4>
                     </div>

                    <div class="mt-5 ms-5 me-5 p-2 d-flex flex-column justify-content-center align-items-center">
                        <h3 class="reset-title-right fw-bold text-start">Want to reset your Password?</h3>
                        <p class="text-white text-start ms-3 mt-2 ms-md-1">Don’t worry it happens. Reset your password and start shopping for all your academic essentials.</p>
                    </div>
                </div>
            </div>
        </div>


        <!-- Step 2: Enter OTP -->
        <form id="step2" method="POST" action="" style="display: none;">
            <label for="otp" class="form-label">Enter OTP:</label>
            <input type="text" name="otp" class="form-control" required>
            <input type="hidden" name="email" value="<?//= isset($_POST['email']) ? $_POST['email'] : ''; ?>">
            <button class="btn btn-success w-100 mt-3" name="verify_otp">Verify OTP</button>
        </form>

        <div class="reset-container" id="step2" style="display: none;">
    <div class="row g-4"> 
        <div class="col-md-7 p-5 d-flex flex-column justify-content-center align-items-center text-center">
            <h2 class="fw-bold reset-title">Enter 6-digit OTP code</h2>
            <p class="text-muted">The OTP code was sent to your email address. Please enter the code.</p>

            <form method="POST" action="">
                <div class="d-flex justify-content-center mt-3 flex-wrap">
                    <input type="text" maxlength="1" class="otp-input form-control" oninput="moveNext(this, 'otp2')">
                    <input type="text" maxlength="1" class="otp-input form-control m-1" id="otp2" oninput="moveNext(this, 'otp3')">
                    <input type="text" maxlength="1" class="otp-input form-control m-1" id="otp3" oninput="moveNext(this, 'otp4')">
                    <input type="text" maxlength="1" class="otp-input form-control m-1" id="otp4" oninput="moveNext(this, 'otp5')">
                    <input type="text" maxlength="1" class="otp-input form-control m-1" id="otp5" oninput="moveNext(this, 'otp6')">
                    <input type="text" maxlength="1" class="otp-input form-control m-1" id="otp6">
                </div>
                
                <p class="mt-5 mb-3" id="timer">05:00</p>
                <p><a href="#" class="resend-link mt-3">Re-send</a></p>

                <input type="hidden" name="email" value="<?= isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                <button class="btn btn-primary btn-submit mt-5" name="verify_otp">Submit</button>
            </form>
        </div>

        <div class="col-md-5 d-none d-md-block reset-info">
            <div class="d-flex align-items-center ms-3 mt-3">
                <div class="logo-circle">
                    <img src="admin\images\Home Page\VMS-LOGO-Alternative-03.png" alt="VMC Basket Logo" class="vmc-logo">
                </div>
                <h4 class="logo-title-right ms-2">VMC Basket</h4>
            </div>

            <div class="mt-5 ms-5 me-5 p-2">
                <h3 class="reset-title-right fw-bold text-start">You're one step closer to resetting your password</h3>
                <p class="text-white text-start mt-3">Verify your identity by entering the One-Time Password (OTP) we just sent you.</p>
            </div>
        </div>
    </div>
</div>

        <!-- Step 3: Reset Password -->
        <div class="reset-container" id="step3" style="display: none;">
    <div class="row g-4">
        <div class="col-md-7 p-5 d-flex flex-column justify-content-center text-center">
            <h2 class="fw-bold reset-title">Create New Password</h2>
            <p class="text-muted text-center">You can create your new password.</p>

            <form method="POST" action="">
                <div class="mt-5">
                    <input type="password" name="new_password" class="form-control" placeholder="New Password" required>
                </div>

                <div class="mb-4 mt-4">
                    <input type="password" class="form-control" placeholder="Confirm Password" required>
                </div>

                <input type="hidden" name="email" value="<?= isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                <button class="btn btn-primary btn-submit mt-5" name="reset_password">Save</button>
            </form>
        </div>

        <div class="col-md-5 d-none d-md-block reset-info">
            <div class="d-flex justify-space-between align-items-center ms-3 mt-3"> 
                <div class="logo-circle">
                    <img src="admin/images/Admin Nav/VMS-LOGO-Alternative-03.png" alt="VMC Basket Logo" class="vmc-logo">
                </div>
                <h4 class="logo-title-right ms-2">VMC Basket</h4>
            </div>

            <div class="mt-5 ms-5 me-5 p-2 d-flex flex-column justify-content-center align-items-center">
                <h3 class="reset-title-right fw-bold text-start">It's time to create your new password</h3>
                <p class="text-white text-start mt-2">Create something secure to keep your account safe and sound!</p>
            </div>
        </div>
    </div>
</div>
    </div>
</div>

<script>
$(document).ready(function() {
    let responseText = "<?= $response; ?>";

    if (responseText.includes("OTP sent")) {
        $("#step1").hide();
        $("#step2").show();
        $("#step3").hide();
    }
    if (responseText.includes("OTP verified")) {
        $("#step1").hide();
        $("#step2").hide();
        $("#step3").show();
    }
});


    // Timer countdown logic
    let timeLeft = 5 * 60; // 5 minutes in seconds
    const timerElement = document.getElementById("timer");
    const resendLink = document.getElementById("resend-link");
    const submitButton = document.getElementById("submit-btn");

    // Update the timer every second
    function updateTimer() {
        let minutes = Math.floor(timeLeft / 60);
        let seconds = timeLeft % 60;

        // Format minutes and seconds as mm:ss
        timerElement.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

        // If time is up
        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            resendLink.style.display = 'block'; // Show the resend link
            submitButton.disabled = true; // Disable submit button after timer ends
        } else {
            timeLeft--;
        }
    }

    // Start the timer when the page loads
    const timerInterval = setInterval(updateTimer, 1000);

    // When the user clicks on "Re-send", reset the timer and resend OTP
    resendLink.addEventListener('click', function (e) {
        e.preventDefault();
        // Reset the timer and hide the resend link
        timeLeft = 5 * 60; // Reset to 5 minutes
        resendLink.style.display = 'none';
        submitButton.disabled = false; // Enable the submit button
        updateTimer(); // Restart the timer
        // Add logic here to resend the OTP, e.g., making an AJAX request to the server
        console.log("Re-sending OTP...");
    });

    // Move to next OTP input field when one is filled
    function moveNext(current, nextFieldId) {
        if (current.value.length == current.maxLength) {
            document.getElementById(nextFieldId).focus();
        }
    }

</script>

</body>
</html>
