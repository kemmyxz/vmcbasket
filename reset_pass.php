reset_password.php
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket-Reset Password</title>
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
                <!-- Left Side: Form -->
                <div class="col-md-7 p-5 d-flex flex-column justify-content-center text-center">
                    <h2 class="fw-bold reset-title">Reset Password</h2>
                    <p class="text-muted text-center">Please enter your phone number and we will send the OTP for you to reset your password.</p>

                    <form>
                        <div class="mb-4 mt-5">
                            <input type="email" class="form-control" placeholder="Please enter your email" required>
                        </div>
                        

                        <div class="d-flex justify-content-center gap-3 mt-5">
                            <button type="button" class="btn btn-outline-danger  w-50">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-submit w-50" >Continue</button>
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
    </div>

    <!-- Bootstrap 5.0 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

      <!-- <div class="card shadow p-4 mx-auto" style="max-width: 400px;">
        <h4 class="text-center">Forgot Password</h4>
        
        <p class="text-center text-danger">
        <?//= $response; ?>
        </p> -->

        <!-- Step 1: Enter Email -->
        <!-- <form id="step1" method="POST" action="">
            <label for="email" class="form-label">Enter your email:</label>
            <input type="email" name="email" class="form-control" required>
            <button class="btn btn-primary w-100 mt-3" name="send_otp">Send OTP</button>
        </form> -->
</body>
</html>