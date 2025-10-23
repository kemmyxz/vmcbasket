<?php
session_start(); // Start session
require 'inc/config.php'; // Include database connection


// If user is already logged in, redirect to home page
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_name = trim($_POST["AdminName"]);
    $password = trim($_POST["password"]);

    // Query the database for the admin_name
    $sql = "SELECT admin_id, admin_name, admin_pass FROM admin_login WHERE admin_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $admin_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $admin['admin_pass'])) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['admin_name'];
            $_SESSION['login'] = true;

            header("Location: index.php"); // Redirect to dashboard
            exit(); // Stop further execution
        } else {
            $_SESSION['error'] = "Incorrect Password!";
        }
    } else {
        $_SESSION['error'] = "Administrator not found!";
    }
    $stmt->close();
    $conn->close();

    // Redirect back to login.php to prevent form resubmission
    header("Location: login.php");
    exit();
}

// Show the error message if it exists
$error = isset($_SESSION['error']) ? $_SESSION['error'] : "";
unset($_SESSION['error']); // Clear error after displaying
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href='https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Sawarabi+Mincho&display=swap'>
    <title>Admin login</title>
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
    background: url('images/log-in-bg.png') center center / cover no-repeat;
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
        <h1 class="text-start">Hello, Admin!</h1>
        <p>Log in now to Showcase all VMC academic essentials.</p>
    </div>

       <div class="right-panel">
        <img src="images/vmc_basket_logo.png" alt="VMC Basket Logo">
                <h2 class="mt-5">Log In</h2>
                <p>Please use your Administrator Account to log in.</p>
                <form id="loginForm" method="POST" action="login.php">
                    <div class="input-group">
                        <input type="text" id="AdminName" name="AdminName" placeholder="Admin Name" required>
                    </div>
                    <div class="input-group">
                        <input type="password" id="password" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" class="custom-navy-btn" name="login">Log In</button>
                </form>
        </div>
    </div>
    <!-- JavaScript Alert for Error -->
    <script>
        var errorMessage = "<?php echo $error; ?>";
        if (errorMessage) {
            alert(errorMessage); // Show alert box
        }
    </script>
</body>
</html>
