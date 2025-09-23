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

            <form method="POST" action="">
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

            <form method="POST" action="">
                <div class="otp-container mt-3">
                    <input type="text" maxlength="1" class="otp-input" oninput="moveNext(this, 'otp2')">
                    <input type="text" maxlength="1" class="otp-input m-1" id="otp2" oninput="moveNext(this, 'otp3')">
                    <input type="text" maxlength="1" class="otp-input m-1" id="otp3" oninput="moveNext(this, 'otp4')">
                    <input type="text" maxlength="1" class="otp-input m-1" id="otp4" oninput="moveNext(this, 'otp5')">
                    <input type="text" maxlength="1" class="otp-input m-1" id="otp5" oninput="moveNext(this, 'otp6')">
                    <input type="text" maxlength="1" class="otp-input m-1" id="otp6">
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

            <form method="POST" action="">
                <div class="input-group">
                    <input type="password" name="new_password" placeholder="New Password" required>
                </div>

                <div class="input-group">
                    <input type="password" placeholder="Confirm Password" required>
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
// For handling the steps and OTP input view
document.addEventListener("DOMContentLoaded", function () {
    // Only include the IDs you actually have
    const steps = ["step1", "step2", "step3", "success"];
    let currentStep = 0; // Start at step1

    function showStep(stepIndex) {
        steps.forEach((id, index) => {
            const el = document.getElementById(id);
            if (el) el.style.display = (index === stepIndex) ? "flex" : "none";
        });
        currentStep = stepIndex;
    }

    // Show first step on load
    showStep(currentStep);

    // Event delegation for next/back buttons
    document.body.addEventListener("click", function (e) {
        if (e.target.matches("[data-next]")) {
            e.preventDefault(); // Stop form from submitting
            let nextStep = currentStep + 1;
            if (nextStep < steps.length) {
                showStep(nextStep);
            }
        }
        if (e.target.matches("[data-prev]")) {
            e.preventDefault();
            let prevStep = currentStep - 1;
            if (prevStep >= 0) {
                showStep(prevStep);
            }
        }
    });
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