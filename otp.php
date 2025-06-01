OTP.php
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket-OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"><link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body class="resetpassBG">
    <div class="container-fluid d-flex align-items-center justify-content-center vh-100">
        <div class="reset-container">
            <div class="row g-4">
                <!-- Left Side: OTP Form -->
                <div class="col-md-7 p-5 d-flex flex-column justify-content-center align-items-center text-center">
                    <h2 class="fw-bold reset-title">Enter 4-digit OTP code</h2>
                    <p class="text-muted">The OTP code was sent to your email address. Please enter the code.</p>

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

                    <button class="btn btn-primary btn-submit mt-5">Submit</button>
                </div>

                <!-- Right Side: Image and Info -->
                <div class="col-md-5 d-none d-md-block reset-info">
                    <div class="d-flex align-items-center ms-3 mt-3">
                        <div class="logo-circle">
                        <img src="admin/images/Admin Nav/VMS-LOGO-Alternative-03.png" alt="VMC Basket Logo" class="vmc-logo">
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
    </div>

<!--Timer-->
<script>
    function moveNext(current, nextFieldID) {
        if (current.value.length === 1) {
            document.getElementById(nextFieldID)?.focus();
        }
    }
    
    let countdown;
    let interval;
    const timerElement = document.getElementById("timer");
    const otpInputs = document.querySelectorAll(".otp-input");
    const resendButton = document.querySelector(".resend-link");

    function setInputState(enabled) {
        otpInputs.forEach(input => input.disabled = !enabled);
    }

    function setResendState(enabled) {
        resendButton.style.pointerEvents = enabled ? "auto" : "none";
        resendButton.style.opacity = enabled ? "1" : "0.5";
    }

    function startTimer() {
        clearInterval(interval);  // Clear any existing interval
        countdown = 300; // Set countdown to 5 minutes (300 seconds)
        updateTimerDisplay();

        setInputState(true);  // Enable OTP inputs
        setResendState(false); // Disable resend button

        interval = setInterval(() => {
            countdown--;
            updateTimerDisplay();

            if (countdown <= 0) {
                clearInterval(interval);
                timerElement.textContent = "Time expired!";
                setInputState(false); // Disable OTP inputs
                setResendState(true); // Enable resend button
            }
        }, 1000);
    }

    function updateTimerDisplay() {
        let minutes = Math.floor(countdown / 60);
        let seconds = countdown % 60;
        timerElement.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds} Sec`;
    }

    resendButton.addEventListener("click", function (event) {
        event.preventDefault(); // Prevent default link behavior
        startTimer(); // Restart the timer
    });

    startTimer(); // Start timer when page loads
</script>



    <!-- Bootstrap 5.0 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>