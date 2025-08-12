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
<title>VMC Basket - Log In</title>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="./CSS/style.css">

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

.container-login {
    display: flex;
    width: 90%;
    height: 90vh;
    background: #fff;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    border-radius: 6px;
    border: 1px solid black;
    overflow: hidden;
    padding: 0px;
}
.left-panel {
    flex: 1;
    background: url('admin/images/log-in-bg.png') center center / cover no-repeat;
    background-color: #26387D;
    padding: 40px;
    color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.left-panel h1 {
    font-family: 'Montserrat', sans-serif;
    font-size: 4.5rem;
    font-weight: 700;
    margin: 0; /* remove browser default margin */
}

.left-panel p {
    font-size: 1.4rem;
    margin: 15px 0 0 0; /* keep only top space */
}

.right-panel {
    display: flex;
    flex: 1;
    flex-direction: column;
    align-items: center; /* Center horizontally */
    justify-content: flex-start; /* Push content to top */
    padding: 30px 40px;
}

.right-panel img {
    height: auto; 
    max-width: 30%;
    align-self: center;
}
.right-panel h2 {
    font-family: 'Montserrat', sans-serif;
    font-size: 2.5rem;
    font-weight: 700;
    color: #003153;
    text-align: center;
}
.right-panel form {
    padding: 50px;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.right-panel p {
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

/* Space between inputs */
.right-panel .input-group {
  width: 100%;
  margin-bottom: 15px;
}

/* Move link above button with spacing */
.right-panel a {
  margin: 10px 0 30px 0; 
  text-decoration: none;
  color: #3D87F5;
  font-size: 0.9rem;
}

/* Full width button */
.right-panel .custom-navy-btn {
  width: 100%;
  padding: 10px;
  font-size: 1rem;
}

a:hover {
    text-decoration: underline;
}
/* Phones and tablets */
@media (max-width: 991.98px) {
    .left-panel {
        display: none;
    }

    .container-login {
        width: 90%;
        height: auto;
        border-radius: 0;
        border: none;
    }

    .right-panel {
        flex: 1;
        padding: 20px;
    }

    .right-panel img {
        max-width: 50%; /* make logo a bit larger for smaller screens */
    }
    .right-panel h2 {
        font-size: 2rem;
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
}

</style>
</head>

<body>

<div class="gradient-bg">
    <div class="circle1"></div>
    <div class="circle2"></div>
</div>

<div class="container-login">
    <div class="left-panel">
        <h1>Hello Montessorians!</h1>
        <p>Log in now to start shopping for all your academic essentials.</p>
    </div>

    <div class="right-panel">
        <img src="admin/images/VMC Basket Logo.png" alt="VMC Basket Logo">
        <h2>Log In</h2>
        <p>Please use your student number to log in.</p>

        <form id="loginForm" method="POST" action="">
            <?php if (!empty($error)): ?>
                <div style="color: red; margin-bottom: 15px; text-align: center;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="input-group">
                <input type="text" id="studentNumber" name="studentNumber" placeholder="Student Number" required>
            </div>

            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="Password" required>
            </div>

            <a href="forgot_password.php">Forgot Password?</a>
            <button type="submit" name="login" class="custom-navy-btn">Log In</button>
        </form>
    </div>
</div>
</body>
</html>
