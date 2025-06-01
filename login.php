<?php
// Start session
session_start();
require 'admin/inc/config.php'; // Include database connection

function updateUserActivityStatus($conn) {
    // Define the inactivity threshold (24 hours = 86400 seconds)
    $inactivity_threshold = 86400; 

    // Update all users' status based on their last activity
    $update_inactive_sql = "UPDATE users 
                          SET active_status = CASE 
                              WHEN TIMESTAMPDIFF(SECOND, last_activity, NOW()) > ? THEN 'Inactive'
                              ELSE 'Active'
                          END
                          WHERE last_activity IS NOT NULL";
    
    $stmt = $conn->prepare($update_inactive_sql);
    $stmt->bind_param("i", $inactivity_threshold);
    $stmt->execute();
    $stmt->close();
}

updateUserActivityStatus($conn);

// If user is already logged in, redirect to index.php
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input
    $student_no = trim($_POST["studentNumber"]);
    $password = trim($_POST["password"]);

    if (empty($student_no) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Query to check if student exists
        $sql = "SELECT id, student_no, student_pass, active_status FROM users WHERE student_no = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $student_no);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();

                // Verify password
                if (password_verify($password, $user['student_pass'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['student_no'] = $user['student_no'];
                    $_SESSION['login'] = true;

                    // Update last activity timestamp and set status as Active
                    $update_last_activity_sql = "UPDATE users 
                                                SET last_activity = NOW(), 
                                                    active_status = 'Active' 
                                                WHERE student_no = ?";
                    $update_last_activity_stmt = $conn->prepare($update_last_activity_sql);
                    $update_last_activity_stmt->bind_param("s", $student_no);
                    $update_last_activity_stmt->execute();
                    $update_last_activity_stmt->close();

                    // Redirect to dashboard
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Incorrect Password!";
                }
            } else {
                $error = "Student number not found!";
            }

            $stmt->close();
        } else {
            $error = "Database query failed.";
        }
    }

    $conn->close();
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
    <link rel="stylesheet" href='https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900&family=Sawarabi+Mincho&display=swap'>
    <title>Login</title>

    <style>
    .login-body{
        background-size: cover;
        background-image: url(admin/images/userlogin/LoginBG.png);
        background-repeat: no-repeat;
        display: flex;
        align-items: center;
        justify-content: center;
    
    }


    .container {
        display: flex;
        width: 70%;
        height: 80vh;
        background: rgb(234, 227, 227);
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        margin-top: 5%;
    }

    .left-panel {
        background: url(admin/images/userlogin/Log\ In\ Picture.png) center/cover;
        width: 50%;
        color: white;
        text-align: center;
        padding-left: 2%;
        padding-top: 2%;
    }

    .left-panel .login-logo{
        width: 71%;
        display: flex;
        align-items: center;
        margin-top: 2%;
        margin: 0;
        justify-content: space-between;
    }
    .login-logo img{
        height: 120px;
        width: auto;   
    }
    .login-logo h3{
        font-family: 'ubuntu sans', sans-serif;
        font-size: 35px;
        font-weight: bold;
        width: 100%;
        display: flex;
    }
    .left-panel h1{
        font-family: 'ubuntu sans', sans-serif;
        font-size: 55px;
        font-weight: bold;
        text-align: left;
        margin-top: 15%;
        margin-left: 5%;
    }
    .left-panel p{
        font-family: 'poppins', sans-serif;
        font-size: 20px;
        text-align: left;
        margin-top: 5%;
        margin-left: 5%;
    }
    .right-panel {
        justify-content: center;
        align-items: center;
        width:55%;
        background-color: #f2f2f2;
    }
    .login-content h2{
        font-family: 'ubuntu sans', sans-serif;
        font-size: 64px;
        font-weight: bold;
        margin: 0%;
    }
    .login-content p{
        font-family: 'poppins', sans-serif;
        font-size: 20px;
        margin-top: 5%; 
        padding: 0;
    }

    .login-content{
        width: auto;
        height: 50vh;
        text-align: center;
        margin: 15% 5%;
    }
    .input-group {
        margin: 10px 0;
    }

    input {
        width: 80%;
        height: 3vh;
        padding: 10px;
        margin-top: 5%;
        border: 2px solid #003153;
        border-radius: 4px;
    }

    button {
        width: 30%;
        height: 5vh;
        padding: 10px;
        background: #003153;
        color: white;
        border: none;
        cursor: pointer;
        border-radius: 4px;
        margin-top: 5%;
    }

    button:hover {
        background: #0963bd;
    }


    </style>
</head>

<body class="login-body">
    <div class="container">
        <div class="left-panel">
            <div class="login-logo">
                <img src="admin/images/userlogin/Log In Logo.png" alt="Logo">
                <h3>VMC Basket</h3>
            </div>
            <h1>Hello, Montessarians!</h1>
            <p>Log in now to start shopping for all your academic essentials.</p>
        </div>
        <div class="right-panel">
            <div class="login-content">
                <h2>Log In</h2>
                <p>Please use your student number to log in.</p>

                <form id="loginForm" method="POST" action="">
                <?php if (!empty($error)): ?>
                    <div class="error-message" style="color: red; margin-bottom: 15px; text-align: center;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>


                    <div class="input-group">
                        <input type="text" id="studentNumber" name="studentNumber" placeholder="Student Number" required>
                    </div>
                    <div class="input-group">
                        <input type="password" id="password" name="password" placeholder="Password" required>
                    </div>
                    <p><a href="forgot_pass.php">Forgot Password?</a></p>
                    <button type="submit" name="login">Log In</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>