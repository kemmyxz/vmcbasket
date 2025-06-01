<?php
session_start();
require('admin/inc/config.php');
// Add this after session_start()
if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']);
}

if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
    unset($_SESSION['error']);
}

// Prevent browser from caching the page
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket-Contact Us</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"><link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="CSS/style.css">

</head>
<body>
    <!-- Header -->
    <header>
        <div class="top-text"><h1>ALL PRODUCTS ARE AVAILABLE FOR PICK-UP ONLY AT VILLAGERS MONTESSORI COLLEGE</h1></div>
        <div class="top-container">
            <ul>
                <li><a href="#"><img src="admin/images/Home Page/basket-nav.png" alt="Basket"></a></li>  
                <li><a href="#"><img src="admin/images/Home Page/heart-nav.png"></a></li>
                <li><a href="profile.php"> <img src="admin/images/Home Page/profile-user-nav.png" alt="profile"></a></li>
            </ul>
        </div>
    </header>

    <!-- Navbar -->
    <div class="navbar shadow-sm">
        <div class="logo ms-4">
            <a href="index.php"><img src="admin/images/Admin Nav/VMS-LOGO-Alternative-03.png" alt="logo"></a>
            <h2>VMC Basket</h2>
        </div>
        <nav>
            <ul>
                <li><a href="index.php" >Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="contact.php" class="active">Contact us</a></li>
            </ul>
        </nav>
        <div class="search" style="display: flex;  align-items: center; justify-content: space-between; width: auto;">
            <div class="search-container me-4">
                <input type="text" class="form-control" placeholder="">
                <button><img src="admin/images/search-icon.png" alt="Search"></button>
            </div>
        </div>
    </div>

    <!-- Contact Us -->
    <!-- Header Image -->
    <img src="admin/images/contactus-header.png" alt="Team Photo" class="img-fluid w-100">

    <!-- Contact Us Title Section -->
    <section class="container my-5">
        <h2 class="text-center contact-title">Contact Information</h2>
        <p class="text-center">Here is the list of contacts that you can use to reach us. Whether you have questions, need assistance, or want to provide feedback, feel free to use any of the following contact methods.</p>
    </section>

    <!-- Left Side: Contact Information -->
    <div class="container contact-container">
        <div class="contact-info">

            <!-- Contact Cards -->
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <div class="contact-text">
                    <h5>Telephone</h5>
                    <p>+63 929 8329 0986</p>
                </div>
            </div>

            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <div class="contact-text">
                    <h5>Official Website</h5>
                    <p><a href="https://vmc.edu.ph" target="_blank" style="text-decoration: none; color: inherit;">Villagers Montessori College VMC</a></p>
                </div>
            </div>

            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fab fa-facebook"></i>
                </div>
                <div class="contact-text">
                    <h5>Facebook Page</h5>
                    <p><a href="https://www.facebook.com/villagersmontessoricollege1983" target="_blank" style="text-decoration: none; color: inherit;">Villagers Montessori College VMC</a></p>
                </div>
            </div>

            <div class="contact-card">
                    <img src="admin/images/gps.png" alt="Location Icon" style="width: 50px; height: 50px;">
                <div class="contact-text ms-4">
                    <h5>Location</h5>
                    <p>18 Dalsol Rd, QBS Village, Sangandaan, Quezon City, Metro Manila, Philippines</p>
                </div>
            </div>
        </div>

        <!-- Right Side: Contact Form -->
        <div class="contact-form">
            <h3 class="contact-title">Send Message</h3>
            <p>Please feel free to send us a message or provide feedback. Your input is valuable to us and helps us improve our services.</p>
            
            <?php
            // Get user data from session if logged in
            $userData = [];
            if (isset($_SESSION['user_id'])) {
                
                $userId = $_SESSION['user_id'];
                $query = "SELECT student_fname, student_lname, student_no, email FROM users WHERE id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                $userData = $result->fetch_assoc();
            }
            ?>

            <form action="process_contact.php" method="POST">
                <div class="mb-3">
                    <input type="text" 
                           class="form-control" 
                           name="name" 
                           placeholder="Name" 
                           value="<?php echo isset($userData['student_fname']) ? htmlspecialchars($userData['student_fname'] . ' ' . $userData['student_lname']) : ''; ?>" 
                           required 
                           readonly>
                </div>
                <div class="mb-3">
                    <input type="text" 
                           class="form-control" 
                           name="student_number" 
                           placeholder="Student Number" 
                           value="<?php echo isset($userData['student_no']) ? htmlspecialchars($userData['student_no']) : ''; ?>" 
                           required 
                           readonly>
                </div>
                <div class="mb-3">
                    <input type="email" 
                           class="form-control" 
                           name="email" 
                           placeholder="Email Address" 
                           value="<?php echo isset($userData['email']) ? htmlspecialchars($userData['email']) : ''; ?>" 
                           required 
                           readonly>
                </div>
                <div class="mb-3">
                    <textarea class="form-control" 
                              name="message" 
                              rows="4" 
                              placeholder="Message" 
                              required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Send</button>
            </form>
        </div>
    </div>

    
    <!-- Map Section -->
    <section class="container my-3 mb-5">
        <h3 class="contact-title">Map</h3>
        <div class="ratio ratio-21x9 mb-5">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d30877.603389030355!2d120.98188517431639!3d14.672934999999994!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397b6c4906da6a9%3A0x8b5c49392e291c8!2sVMC!5e0!3m2!1sen!2sph!4v1742208760377!5m2!1sen!2sph" 
                allowfullscreen="" 
                loading="lazy">
            </iframe>
        </div>
    </section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>

<!-- Footer -->
<footer>
    <div class="footer-container">
        <div class="footer-logo">
            <img src="admin/images/Footer/VMS-LOGO-Official-01.png" alt="logo">
            <div class="logo-text">
                <h2>VMC Basket</h2>
                <h4>Villagers Montesorri College E-commerce Website</h4>
            </div>
        </div>

        <div class="footer-links mt-5">
            <div class="about">
                <p>your one-stop destination for all university merchandise needs! Discover a vast collection of high-quality uniforms, organizational shirts, and accessories tailored to showcase your university pride.</p>
            </div>
            <div class="footer-nav">
                <h4>Links</h4>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="shop.html">Shop</a></li>
                    <li><a href="contact.html">Contact us</a></li>
                </ul>
            </div>
            <div class="services">
                <h4>Customer Services</h4>
                <ul>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Size Guide</a></li>
                    <li><a href="#">Exchange & Returns</a></li>
                </ul>
            </div>
            <div class="myAccount">
                <h4>My Account</h4>
                <ul>
                    <li><a href="#">Submit Feedback</a></li>
                    <li><a href="#">Favorites</a></li>
                    <li><a href="#">Shopping cart</a></li>
                </ul>
            </div>
        </div>

        <div class="socials mt-4">
            <div class="footer-acknowledgement">
                <div class="policy">
                    <ul>
                        <li><a href="#">About |</a></li>
                        <li><a href="#">Privacy Policy |</a></li>
                        <li><a href="#">Terms of Services</a></li>
                    </ul>
                </div>
                <div class="copy">
                    <h4>©2024 Villagers Montesorri College. All rights reserved.</h4>
                </div>
            </div>
            
            <div class="footer-social mt-4">
                <a href="#"><img src="admin/images/Footer/www.png" alt="Website"></a> 
                <a href="facebook.com"><img src="admin/images/Footer/facebook-footer.png" alt="facebook"></a>
                <a href="#"><img src="admin/images/Footer/instagram.png" alt="instagram"></a>
                <a href="#"><img src="admin/images/Footer/youtube.png" alt="youtube"></a> 
            </div>
        </div>
    </div> 
</footer>
</html>
``` 