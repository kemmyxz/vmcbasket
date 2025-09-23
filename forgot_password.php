<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <?php include 'links.php'; ?>
<style>
body {
    background-color: #fff;
    overflow-y: hidden;
    overflow-x: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}
/* Gradient circles on left side */
.gradient-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 60%;
    height: 100%;
    z-index: -1;
}

.circle1, .circle2 {
    position: absolute;
    border-radius: 862px;
    filter: blur(100px);
}

.circle1 {
    width: 700px;
    height: 700px;
    flex-shrink: 0;
    background: linear-gradient(136deg, #FFA6AB 17.46%, #5679FF 93.71%);
    top: -200px;
    left: -150px;
}

.circle2 {
    width: 700px;
    height: 700px;
    transform: rotate(-168.542deg);
    flex-shrink: 0;
    background: linear-gradient(135deg, #FFED98 22.87%, #5679FF 82.65%);
    top: 500px;
    right: -650px;
}

.container {
    display: none;
    height: 90vh;
    background: #fff;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    border-radius: 6px;
    border: 1px solid black;
    overflow: hidden;
    padding: 0px;
}
.right-panel {
    flex: 1;
    background: url('admin/images/log-in-bg.png') center center / cover no-repeat;
    background-color: #26387D;
    padding: 30px 40px;
    color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.right-panel h1 {
    font-family: 'Montserrat', sans-serif;
    font-size: 3.5rem;
    font-weight: 700;
    margin: 0; /* remove browser default margin */
}

.right-panel p {
    font-size: 1.4rem;
    margin: 15px 0 0 0; /* keep only top space */
}
.left-panel {
    display: flex;
    flex: 1;
    flex-direction: column;
    align-items: center; /* Center horizontally */
    justify-content: flex-start; /* Push content to top */
    padding: 40px;
}

.left-panel img {
    height: auto; 
    max-width: 30%;
    align-self: center;
}
.left-panel h2 {
    font-family: 'Montserrat', sans-serif;
    font-size: 2.5rem;
    font-weight: 700;
    color: #003153;
    text-align: center;
}
.left-panel form {
    padding: 50px;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.left-panel p {
    font-size: 1rem;
    text-align: center;
    margin-bottom: 20px;
}

.input-group {
    margin-bottom: 15px;
}

input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.otp-container {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    margin: 0px;
}
.otp-input {
    width: 50px;
    height: 50px;
    text-align: center;
    font-size: 15px;
    border: 1px solid #ccc;
    margin: 5px;
    border-radius: 4px;
    padding: 0px;
}
.resend-link {
    text-decoration: none;
    color: #3D87F5;
    cursor: pointer;
}
.resend-link:hover {
    text-decoration: underline;
}

@media (max-width: 991.98px) {
    .right-panel {
        display: none;
    }

    .container {
        width: 90%;
        height: auto;
        border-radius: 0;
        border: none;
        justify-content: center;
        align-items: center;
    }

    .left-panel {
        padding: 20px;
    }

    .left-panel form{
        padding: 20px;
    }

    .left-panel img {
        max-width: 50%; /* make logo a bit larger for smaller screens */
    }
    .left-panel h2 {
        font-size: 1.7rem;
    }

    .left-panel p {
        font-size: .9rem;
        max-width: 300px; 
        word-wrap: break-word;
        white-space: normal;
        text-align: center; 
        margin: 0 auto;
    }

    .circle2 {
        width: 300px;
        height: 300px;
        bottom: 100px;
        right: -120px;
        filter: blur(50px);
    }
    .circle1 {
        width: 350px;
        height: 300px;
        top: -100px;
        left: -70px;
        filter: blur(80px);
    }
    .successful_icon {
      width: 100%;
    }
    .otp-input {
      width: 32px;
      height: 32px;
    }
}
</style>
</head>

<body>
    <div class="gradient-bg">
        <div class="circle1"></div>
        <div class="circle2"></div>
    </div>

    <div class="container" id="step1">
        <div class = "left-panel">
            <!-- Left Side: Form -->
            <img src="admin/images/vmc_basket_logo.png" alt="VMC Basket Logo">
            <h2 class="mt-5">Reset Password</h2>
            <p>Please enter your email address and we will send the OTP for you to reset your password.</p>

            <form name="send_otp" method="POST">
                <div class="input-group mb-4 mt-5">
                <input type="email" name="email"  placeholder="Enter your email address" required>
                </div>

                <div class="d-flex justify-content-center gap-3 mt-5">
                    <button type="button" class="btn btn-outline-danger "onclick="history.back()">Cancel</button>
                    <button type="submit" class="custom-navy-btn" name="send_otp" data-next>Continue</button>
                </div>
            </form>
        </div>

        <!-- Right Side: Image and Info -->
        <div class = "right-panel">
            <h1>Want to reset your Password?</h1>
            <p>Don’t worry it happens. Reset your password and start shopping for all your academic essentials.</p>
        </div>
    </div>

    <!-- Step 2: Enter OTP -->
    <div class="container" id="step2">
        <div class = "left-panel"> 
            <img src="admin/images/vmc_basket_logo.png" alt="VMC Basket Logo">
            <h2 class="mt-5">Enter 6-digit OTP code</h2>
            <p>The OTP code was sent to your email address. Please enter the code.</p>

            <form name="verify_otp" method="POST">
                <div class="otp-container mt-3">
                    <input type="text" name="otp1" class="otp-input" maxlength="1" required>
                    <input type="text" name="otp2" class="otp-input" maxlength="1" required>
                    <input type="text" name="otp3" class="otp-input" maxlength="1" required>
                    <input type="text" name="otp4" class="otp-input" maxlength="1" required>
                    <input type="text" name="otp5" class="otp-input" maxlength="1" required>
                    <input type="text" name="otp6" class="otp-input" maxlength="1" required>
                </div>
                    
                <p class="mt-5 mb-3" id="timer">05:00</p>
                <div class = "d-flex justify-content-center gap-2 align-items-center"> 
                    <p>Doesn't received code? <a href="#" class="resend-link">Re-send</a> </p>
                </div>

                <input type="hidden" name="email" value="<?= isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                <button class="custom-navy-btn mt-4" name="verify_otp" data-next>Submit</button>
            </form>
        </div>

        <div class = "right-panel">
            <h1>You're one step closer to resetting your password</h1>
            <p>Verify your identity by entering the One-Time Password (OTP) we just sent you.</p>
        </div>
    </div>

    <!-- Step 3: Reset Password -->
    <div class="container" id="step3">
        <div class = "left-panel"> 
            <img src="admin/images/vmc_basket_logo.png" alt="VMC Basket Logo">
            <h2 class="mt-5">Create New Password</h2>
            <p>You can create your new password.</p>

            <form name="reset_password" method="POST">
                <div class="input-group">
                    <input type="password" name="new_password" placeholder="New Password" required>
                </div>

                <div class="input-group">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>

                <input type="hidden" name="email" value="<?= isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                <button class="custom-navy-btn mt-5" name="reset_password" data-next>Save</button>
            </form>
        </div>

        <div class = "right-panel">
            <h1>It's time to create your new password</h1>
            <p>Create something secure to keep your account safe and sound!</p>
        </div>
    </div>

    <!-- Success -->
    <div class="container" id="success">
         <div class = "left-panel"> 
            <img src="admin/images/vmc_basket_logo.png" alt="VMC Basket Logo">
            <h2 class="mt-5">Password Reset Successfully!</h2>
            <img src="admin/images/Admin Nav/succesful.png" alt="success icon" class="img-fluid successful_icon">
            <p class="text-muted text-center">You have successfully reset your password. Please use your new password in logging in.</p>
            <a href="login.php"><button class="custom-navy-btn mt-5">Log in now</button></a>
        </div>

        <div class = "right-panel">
            <h1>Great job! Your password has been successfully reset</h1>
            <p>Log in and continue exploring all your academic essentials.</p>
        </div>
    </div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const containers = ['step1', 'step2', 'step3', 'success'].map(id => document.getElementById(id));
    
    // Show initial step
    showStep(currentStep);

    // Handle Send OTP form
    document.querySelector('form[name="send_otp"]').addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.querySelector('input[name="email"]').value;
        
        fetch('forgot_password.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `send_otp=1&email=${encodeURIComponent(email)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showStep(2);
                startOTPTimer();
            } else {
                alert(data.message);
            }
        });
    });

    // Handle OTP verification
    document.querySelector('form[name="verify_otp"]').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('verify_otp', '1');
        
        fetch('forgot_password.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showStep(3);
            } else {
                alert(data.message);
            }
        });
    });

    // Handle Password Reset
    document.querySelector('form[name="reset_password"]').addEventListener('submit', function(e) {
        e.preventDefault();
        const password = this.querySelector('input[name="new_password"]').value;
        const confirmPassword = this.querySelector('input[name="confirm_password"]').value;
        
        if (password !== confirmPassword) {
            alert('Passwords do not match!');
            return;
        }

        const formData = new FormData(this);
        formData.append('reset_password', '1');
        
        fetch('forgot_password.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                showStep(4); // Show success screen
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 3000);
            } else {
                alert(data.message);
            }
        });
    });

    // OTP input handling
    const otpInputs = document.querySelectorAll('.otp-input');
    otpInputs.forEach((input, index) => {
        input.addEventListener('keyup', function(e) {
            if (e.key >= '0' && e.key <= '9') {
                if (index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            } else if (e.key === 'Backspace') {
                if (index > 0) {
                    otpInputs[index - 1].focus();
                }
            }
        });
    });

    function showStep(step) {
        containers.forEach((container, index) => {
            container.style.display = index + 1 === step ? 'flex' : 'none';
        });
        currentStep = step;
    }

    function startOTPTimer() {
        let timeLeft = 300; // 5 minutes in seconds
        const timerElement = document.getElementById('timer');
        const resendLink = document.querySelector('.resend-link');

        const timer = setInterval(() => {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                resendLink.style.display = 'inline';
                timerElement.textContent = "OTP Expired";
            }
            timeLeft--;
        }, 1000);

        resendLink.style.display = 'none';
    }
});
</script>


<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require 'admin/inc/config.php';

session_start();
$response = array('status' => '', 'message' => '');

// Step 1: Send OTP
if (isset($_POST['send_otp'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Generate OTP
        $otp = sprintf("%06d", mt_rand(0, 999999));
        $otp_hash = password_hash($otp, PASSWORD_BCRYPT);
        $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

        // Store OTP in database
        $update_stmt = $conn->prepare("UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?");
        $update_stmt->bind_param("sss", $otp_hash, $expiry, $email);
        
        if ($update_stmt->execute()) {
            // Send OTP via Email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'k.lopanggo14@gmail.com'; // Your email
                $mail->Password = 'ptik yanf brbo mxkn'; // Your app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                
                $mail->setFrom('k.lopanggo14@gmail.com', 'VMC BASKET');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset OTP';
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; padding: 20px;'>
                        <h2>Password Reset Request</h2>
                        <p>Your OTP for password reset is: <strong style='font-size: 24px;'>{$otp}</strong></p>
                        <p>This OTP will expire in 5 minutes.</p>
                        <p>If you didn't request this, please ignore this email.</p>
                    </div>";

                if ($mail->send()) {
                    $_SESSION['reset_email'] = $email;
                    $response['status'] = 'success';
                    $response['message'] = "OTP sent successfully!";
                }
            } catch (Exception $e) {
                $response['status'] = 'error';
                $response['message'] = "Failed to send OTP. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = "Error storing OTP!";
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = "Email not found!";
    }
    
    echo json_encode($response);
    exit;
}

// Step 2: Verify OTP
if (isset($_POST['verify_otp'])) {
    $email = $_SESSION['reset_email'] ?? '';
    $entered_otp = '';
    
    // Combine OTP digits
    for ($i = 1; $i <= 6; $i++) {
        $entered_otp .= $_POST["otp$i"];
    }

    $stmt = $conn->prepare("SELECT otp, otp_expiry FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($entered_otp, $user['otp'])) {
        if (strtotime($user['otp_expiry']) > time()) {
            $_SESSION['otp_verified'] = true;
            $response['status'] = 'success';
            $response['message'] = "OTP verified successfully!";
        } else {
            $response['status'] = 'error';
            $response['message'] = "OTP has expired!";
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = "Invalid OTP!";
    }
    
    echo json_encode($response);
    exit;
}

// Step 3: Reset Password
if (isset($_POST['reset_password'])) {
    if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified']) {
        $response['status'] = 'error';
        $response['message'] = "Unauthorized access!";
        echo json_encode($response);
        exit;
    }

    $email = $_SESSION['reset_email'] ?? '';
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $response['status'] = 'error';
        $response['message'] = "Passwords do not match!";
        echo json_encode($response);
        exit;
    }

    $password_hash = password_hash($new_password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("UPDATE users SET student_pass = ?, otp = NULL, otp_expiry = NULL WHERE email = ?");
    $stmt->bind_param("ss", $password_hash, $email);
    
    if ($stmt->execute()) {
        // Clear session
        unset($_SESSION['reset_email']);
        unset($_SESSION['otp_verified']);
        
        $response['status'] = 'success';
        $response['message'] = "Password reset successful!";
    } else {
        $response['status'] = 'error';
        $response['message'] = "Error updating password!";
    }
    
    echo json_encode($response);
    exit;
}
?>
</body>
</html>