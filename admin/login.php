<?php
session_start(); // Start session
require 'inc/config.php'; // Include database connection

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
    <style>
    .login-body{
        background-size: cover;
        background-image: url(../admin/images/userlogin/LoginBG.png);
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
        background: url(../admin/images/userlogin/Log\ In\ Picture.png) center/cover;
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
                <img src="images/userlogin/Log In Logo.png" alt="Logo">
                <h3>VMC Basket</h3>
             </div>
            <h1>Hello, Admin!</h1>
            <p>Log in now to Showcase all VMC academic essentials.</p>
        </div>
        <div class="right-panel">
            <div class="login-content">
                <h2>Log In</h2>
                <p>Please use your Administrator Account to log in.</p>
                <form id="loginForm" method="POST" action="login.php">
                    <div class="input-group">
                        <input type="text" id="AdminName" name="AdminName" placeholder="Admin Name" required>
                    </div>
                    <div class="input-group">
                        <input type="password" id="password" name="password" placeholder="Password" required>
                    </div>
                    <button type="submit" name="login">Log In</button>
                </form>
            </div>
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
