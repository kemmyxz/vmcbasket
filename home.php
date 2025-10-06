<?php
session_start();
require 'admin/inc/config.php';
// If user is not logged in, redirect to login
// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }

// Prevent browser from caching the page
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Fetch recommended products

$recommendations = "SELECT p.*, 
                   GROUP_CONCAT(DISTINCT pv.gender) as genders,
                   GROUP_CONCAT(DISTINCT pv.size) as sizes,
                   p.rating
                   FROM products p
                   LEFT JOIN product_variants pv ON p.id = pv.product_id
                   WHERE p.type='uniform' OR p.type='supplies'
                   GROUP BY p.id, p.product_name, p.price, p.image, p.type, p.rating";
$rec_result = $conn->query($recommendations);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user information
$user_id = $_SESSION['user_id'];
$user_query = "SELECT student_fname, student_lname, photo FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Set default profile picture if none exists
$profile_pic = !empty($user['photo']) ? "admin/uploads/" . $user['photo'] : "admin/images/profile_pic.png";
$full_name = $user['student_fname'] . " " . $user['student_lname'];

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket-Home</title>
    <?php include 'links.php'; ?>
    <style>
        .highlight-yellow {
            background-color: #fff4bf;
            padding: 0.3rem 1rem;
            border-radius: 6px;
            border: 1px solid black;
            box-shadow: 3px 3px 0px #000;
            font-weight: 600;
        }
        /* Search bar */
        .search-bar input {
            border-radius: 20px;
            border: 1px solid #ccc;
            padding-left: 15px;
        }
        /* Carousel */
        .carousel-item img {
            width: 100%;
            height: auto;
        }
        /* Section titles */
        .section-title {
            font-weight: bold;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        /* Product cards */
        .product-card {
            border: 1px solid #eee;
            border-radius: 10px;
            transition: 0.3s;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 5px 15px rgba(0,0,0,0.1);
        }
        .product-card img {
            border-radius: 10px 10px 0 0;
            width: 100%;
        }
        .product-info {
            padding: 10px;
        }

        /* FAQ */
        .faq-section {
            background: #FFF4C2;
            padding: 2rem;
            border: 1px solid black
        }

        .tagline {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
            margin: 0px;
            background: #003153;
            color: white;
        }

        /* Fade-in animation */
        /* Initial hidden state */
        .fade-section {
          opacity: 0;
          transform: translateY(30px);
          transition: opacity 0.8s ease, transform 0.8s ease;
        }

        /* When visible */
        .fade-section.visible {
          opacity: 1;
          transform: translateY(0);
        }

        .margin-top{
            margin-top: 85px;
        }

        /* Responsive styles */
        @media (max-width: 991.98px) {
        /* Tablet: 3 columns for product cards */
        .product-card img {
            height: 180px;
        }
        .col-lg-3,
        .col-md-4 {
            flex: 0 0 33.3333%;
            max-width: 33.3333%;
        }

        .highlight-pink {
            font-size: 1.5rem;
        }

        .fav-button {
            width: 30px;
            height: 30px;
            font-size: 0.9rem;
        }
        .basket-button {
            font-size: 0.65rem;
            padding: 0.1rem 0.3rem;
        }

        .product-info h3 {
            font-size: 0.8rem;
        }
        .product-info p {
            font-size: 0.65rem;
        }
        .badges .badge {
            font-size: 0.55rem;
        }
        .price {
            font-size: 0.85rem;
        }
        .rating {
            font-size: 0.65rem;
        }
        .rating i {
            font-size: 0.65rem;
        }
        }
        @media (max-width: 767.98px) {
        /* Phone: 2 columns for product cards, smaller card */
        .product-card img {
            height: 180px;
        }
        .col-md-4,
        .col-lg-3 {
            flex: 0 0 50%;
            max-width: 50%;
        }
        .product-info h3 {
            font-size: 0.95rem;
        }
        .product-info p,
        .price,
        .rating {
            font-size: 0.8rem;
        }
        .icon-buttons .basket-button {
            padding: 0 10px;
            font-size: 0.9rem;
        }
        }
        @media (max-width: 575.98px) {

        .margin{
            margin-top: 0px;
            margin-bottom: 0px;
            padding: 20px 50px 50px 20px;
        }
        .fade-section{
            opacity: 1;
            transform: translateY(0);
        }
        /* Extra small: 1 column for product cards */
        .col-md-4,
        .col-lg-3 {
            flex: 0 0 100%;
            max-width: 100%;
        }
        .product-card img {
            height: 180px;
        }
        .highlight-pink,
        .highlight-blue,
        .highlight-yellow {
            font-size: 1rem;
        }
        .tagline {
            font-size: 0.8rem;
            text-align: center;
        }
        .navbar-custom {
            padding: 0.5rem 1rem;
            flex-direction: column;
            align-items: flex-start;
        }
        .container-fluid.d-flex.align-items-center {
            justify-content: start;
        }
        .vmc-logo {
            max-width: 90px;
        }
        .search-box {
            width: 100%;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }

        .basket-btn {
            width: 38px;
            height: 38px;
            font-size: 1.2rem;
            margin-right: 5px;
        }

        .profile-section img {
            width: 70px;
            height: 70px;
        }
        footer {
            font-size: 1rem;
        }
    }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-custom shadow-sm fixed-top">
        <div class="container-fluid d-flex align-items-center">
            <!-- Hamburger -->
            <button class="btn btn-link text-dark me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sideMenu">
                <i class="fas fa-bars fa-lg"></i>
            </button>

            <!-- Logo -->
            <a class="navbar-brand" href="home.php">
                <img src="admin/images/vmc_basket_logo.png" alt="VMC Basket" class="vmc-logo">
            </a>

            <!-- Search bar (desktop) -->
            <div class="flex-grow-1 position-relative me-3 d-none d-sm-block">
                <input type="text" class="form-control search-box" placeholder="Search products here...">
                <i class="fas fa-search search-icon"></i>
            </div>

            <!-- Right-aligned buttons for small devices -->
            <div class="d-flex d-sm-none ms-auto align-items-center" style="margin-right: 10px;">
                <!-- Search icon (mobile) -->
                <button class="btn p-0" type="button" id="mobileSearchToggle">
                    <i class="fas fa-search fa-lg"></i>
                </button>
            </div>
            <!-- Cart -->
            <?php
            // Fetch basket count for the logged-in user
            $basket_count = 0;
            if (isset($_SESSION['user_id'])) {
                $basket_query = "SELECT SUM(quantity) as total FROM basket WHERE user_id = ?";
                $basket_stmt = $conn->prepare($basket_query);
                $basket_stmt->bind_param("i", $_SESSION['user_id']);
                $basket_stmt->execute();
                $basket_result = $basket_stmt->get_result();
                if ($basket_row = $basket_result->fetch_assoc()) {
                    $basket_count = (int)$basket_row['total'];
                }
                $basket_stmt->close();
            }
            ?>
            <a href="basket.php" class="basket-btn text-decoration-none position-relative">
                <i class="fas fa-shopping-basket"></i>
                <?php if ($basket_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.8rem;">
                        <?php echo $basket_count; ?>
                    </span>
                <?php endif; ?>
            </a>
            

            <!-- Collapsible search bar (mobile) -->
            <div class="w-100 mt-2 d-none" id="mobileSearchBar">
                <input type="text" class="form-control search-box" placeholder="Search products here...">
            </div>
        </div>
    </nav>

    <!-- Offcanvas Sidebar -->
   
    <div class="offcanvas offcanvas-start offcanvas-custom" tabindex="-1" id="sideMenu">
        <div class="offcanvas-body p-0">
            <div class="d-flex justify-content-end p-2 close d-block d-lg-none" data-bs-theme="dark">
                <button type="button" class="btn-close btn btn-light" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="profile-section">
                <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture">
                <h4 class="mt-2"><?php echo htmlspecialchars($full_name); ?></h4>
            </div>
    
            <div class="px-3">
                <div class="mb-2">
                    <button class="btn btn-link text-white w-100 text-start dropdown-toggle text-decoration-none" data-bs-toggle="collapse" data-bs-target="#profileMenu">
                    Profile
                    </button>
                    <div class="collapse ps-3" id="profileMenu">
                    <a href="profile.php">My Account</a>
                    <a href="purchase_history.php">My Purchase</a>
                    <a href="favorites.php">My Favorites</a>
                    </div>
                </div>
    
                <a href="home.php">Home</a>
    
                <div class="mt-2">
                    <button class="btn btn-link text-white w-100 text-start dropdown-toggle text-decoration-none" data-bs-toggle="collapse" data-bs-target="#shopMenu">
                    Shop
                    </button>
                    <div class="collapse ps-3" id="shopMenu">
                    <a href="shop_uniforms.php">Uniforms</a>
                    <a href="shop_supplies.php">School Supplies</a>
                    <a href="shop_merch.php">School-related Merchandise</a>
                    </div>
                </div>
    
                <a href="logout.php" class="mt-3 d-block">Log out</a>
            </div>
        </div>
    </div>

    <!-- HERO SECTION / CAROUSEL -->
    <div id="heroCarousel" class="carousel slide fade-section margin-top" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
            
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="admin/images/Home Page/vmc-carousel.png" class="d-block w-100" alt="VMC Uniforms">
            </div>
            <div class="carousel-item">
                <img src="admin/images/Home Page/vmc-carousel2.png" class="d-block w-100" alt="VMC Uniforms">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
    </div>

    <!-- TAGLINE -->
    <div class="tagline fade-section">
        All products are available for pick-up only at Villagers Montessori College
    </div>


    <!-- SHOP BY YEAR-LEVEL -->
    <div class="container text-start p-md-5 margin fade-section">
        <h2 class="mb-5">
            <span class="highlight-pink">Shop By Year-level</span>
        </h2>
        <div class="year-level row g-4 justify-content-center">
            <!-- First Row -->
            <div class="col-md-6">
                <a href="shop_uniforms.php"><img src="admin/images/Home Page/Pre-School & Elementary.png" class="img-fluid rounded shadow" alt="Pre-School & Elementary"></a>
            </div>
            <div class="col-md-6">
                <a href="shop_uniforms.php"><img src="admin/images/Home Page/Junior High School.png" class="img-fluid rounded shadow" alt="Junior High"></a>
            </div>
            <!-- Second Row -->
            <div class="col-md-6">
                <a href="shop_uniforms.php"><img src="admin/images/Home Page/Senior High School.png" class="img-fluid rounded shadow" alt="Senior High"></a>
            </div>
            <div class="col-md-6">
                <a href="shop_uniforms.php"><img src="admin/images/Home Page/College.png" class="img-fluid rounded shadow" alt="College"></a>
            </div>
        </div>
    </div>

    <!-- FEATURED PRODUCTS -->
    <div class="container p-md-5 fade-section margin">
        <h2 class="mb-5 text-center">
            <span class="highlight-blue">Featured Products</span>
        </h2>

        <div class="container mb-3">
            <div class="row g-4">
                <?php
                if ($rec_result && $rec_result->num_rows > 0):
                    $count = 0; // Initialize counter
                    while ($rec_row = $rec_result->fetch_assoc()):
                        if ($count >= 8) break; // Break loop after 8 products
                ?>
                <div class="col-md-4 col-lg-3">
                    <div class="product-card" onclick="location.href='product_details.php?id=<?= $rec_row['id'] ?>'">

                        <!-- Product Image -->
                        <img src="admin/<?= htmlspecialchars($rec_row['image']) ?>" alt="<?= htmlspecialchars($rec_row['product_name']) ?>">

                        <!-- Product Info -->
                        <div class="product-info">
                            <h3><?= htmlspecialchars($rec_row['product_name']) ?></h3>
                            <?php
                            $genders = trim($rec_row['genders'] ?? '');
                            $sizes = trim($rec_row['sizes'] ?? '');
                            if ($genders !== '' || $sizes !== ''): ?>
                                <p>
                                    <?= htmlspecialchars($genders) ?>
                                    <?php if ($sizes !== ''): ?>
                                        (<?= htmlspecialchars($sizes) ?>)
                                    <?php endif; ?>
                                </p>
                            <?php else: ?>
                                <p>&nbsp;</p>
                            <?php endif; ?>

                            <div class="badges">
                                <span class="badge preschool_badge">Pre-School</span>
                                <span class="badge uniform_badge">Uniform</span>
                            </div>

                            <!-- Price Range -->
                            <div class="price">
                                ₱<?= number_format($rec_row['price'], 2) ?>
                            </div>

                            <!-- Rating -->
                            <div class="rating">
                                <?php
                                $rating = $rec_row['rating'] ?? 0;
                                $fullStars = floor($rating);
                                $hasHalfStar = ($rating - $fullStars) >= 0.5;

                                for ($i = 0; $i < $fullStars; $i++): ?>
                                    <i class="bi bi-star-fill text-warning"></i>
                                <?php endfor;

                                if ($hasHalfStar): ?>
                                    <i class="bi bi-star-half text-warning"></i>
                                <?php endif;

                                $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                                for ($i = 0; $i < $emptyStars; $i++): ?>
                                    <i class="bi bi-star text-warning"></i>
                                <?php endfor; ?>
                                <span><?= number_format($rating, 1) ?></span>
                            </div>

                            <!-- Icon Buttons (Heart & Cart) -->
                            <div class="icon-buttons">
                                <button class="fav-button" onclick="event.stopPropagation(); toggleFavorite(this, <?= $rec_row['id'] ?>)">
                                    <i class="bi bi-heart"></i>
                                </button>
                                <button class="basket-button" onclick="event.stopPropagation(); addToCart(<?= $rec_row['id'] ?>)">
                                    <i class="bi bi-basket me-2"></i>
                                    Add to Basket
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php 
                        $count++; // Increment counter
                    endwhile; 
                else: ?>
                    <p>No recommended products available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

   
    <div class="text-center">
        <a href="shop_uniforms.php" class="text-decoration-none">
            <button class="custom-navy-btn">See More</button>
        <a>
    </div>

    
    <!-- FAQ SECTION -->
    <div class="py-md-4 fade-section">
        <div class="container p-5">
            <h2 class="mb-5 text-end">
                <span class="highlight-yellow">Frequently Asked Questions</span>
            </h2>
            <div class="accordion" id="faqAccordion">
                <!-- Question 1 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faq1">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer1">
                            What types of products are available on this site?
                        </button>
                    </h2>
                    <div id="answer1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            The website offers a variety of official uniforms, which you can easily filter based on your year level to find exactly what you need.
                             You’ll also find a selection of school supplies and other school-related merchandise designed for the VMC community.
                        </div>
                    </div>
                </div>
                <!-- Question 2 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faq2">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer2">
                            How do I know which size to order?
                        </button>
                    </h2>
                    <div id="answer2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                           Log in to your VMC Basket account using your registered student number and password. Browse through the available merchandise in the “Shop” section,
                           select the items you need, and add them to your cart. Once ready, proceed to checkout and choose your preferred payment method and upload your E-receipt.
                            Follow the instructions carefully to finalize your purchase.
                        </div>
                    </div>
                </div>
                <!-- Question 3 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faq3">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer3">
                            What payment methods do you accept?
                        </button>
                    </h2>
                    <div id="answer3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            VMC Basket accepts both over-the-counter and online payments.<br><br> For over-the-counter payments, you can pay directly at the VMC Accounting Office after placing your order online.<br><br>
                            For online payments, you may choose GCash at checkout. Send the total amount to the official VMC Basket GCash number, then upload a screenshot or photo of your payment receipt during checkout. This allows the admin to verify your payment and ensures your order is processed smoothly
                        </div>
                    </div>
                </div>
                <!-- Question 4 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faq4">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer4">
                            Is delivery available for my order?
                        </button>
                    </h2>
                    <div id="answer4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            VMC Basket does not offer door-to-door delivery at this time. All orders must be claimed physically at the school's bookstore. 
                            This ensures that students and parents can personally check their orders upon claiming and helps maintain smooth and organized distribution within the campus.
                        </div>
                    </div>
                </div>
                <!-- Question 5 -->
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faq5">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer5">
                            How will I know if my order is ready for pickup?
                        </button>
                    </h2>
                    <div id="answer5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Once your order has been verified and confirmed by the admin team, it will be marked as “To Pick Up” in the “My Purchase” section of your account. You will also receive an order summary with all relevant pickup instructions and details.
                        </div>
                    </div>
                </div>
                <!-- Hidden Questions Start -->
                <div id="faqMore" style="display:none;">
                    <!-- Question 6 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq6">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer6">
                                Can I cancel my order after placing it?
                            </button>
                        </h2>
                        <div id="answer6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, you can cancel your order as long as it has not yet been processed or verified by the admin. Simply go to the “My Purchase” section, locate the order, and choose the cancel option. Once an order has been verified, it can no longer be canceled.
                            </div>
                        </div>
                    </div>
                    <!-- Question 7 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq7">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer7">
                                Can I use the platform if I’m not a VMC student or staff?
                            </button>
                        </h2>
                        <div id="answer7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                No. The VMC Basket is exclusively for currently enrolled students and official staff of Villagers Montessori College. Only administrators can register users, and each account is tied to a valid VMC student number.
                            </div>
                        </div>
                    </div>
                    <!-- Question 8 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq8">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer8">
                                Is my personal information secure on this platform?
                            </button>
                        </h2>
                        <div id="answer8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes. The platform is designed with data security in mind. Only administrators can create accounts, and sensitive details such as your full name and student number cannot be edited after registration. All user data is stored securely and handled with strict confidentiality.
                            </div>
                        </div>
                    </div>
                    <!-- Question 9 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq9">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer9">
                               Is there a mobile app available?
                            </button>
                        </h2>
                        <div id="answer9" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Not at this time. The VMC Basket is a web-based platform optimized for use on desktop and mobile browsers. You can conveniently access it via any modern browser on your computer or smartphone.
                            </div>
                        </div>
                    </div>
                    <!-- Question 10 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq10">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer10">
                                How can I reset my password if I forget it?
                            </button>
                        </h2>
                        <div id="answer10" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                If you forget your password, simply click on the “Forgot Password” option on the login page. Enter your email address, and a One-Time Password (OTP) will be sent to your registered email address. Use the OTP to verify your identity and reset your password securely.
                            </div>
                        </div>
                    </div>
                    <!-- Question 11 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq11">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer11">
                                Can I change my personal information after registration?
                            </button>
                        </h2>
                        <div id="answer11" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                               Yes, you can update select details such as your phone number, email address, birthdate, and year level by visiting the Account Settings page. However, your full name and student number are locked for identity verification purposes and cannot be edited.
                            </div>
                        </div>
                    </div>
                    <!-- Question 12 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq12">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer12">
                                Can I leave feedback about a product I purchased?
                            </button>
                        </h2>
                        <div id="answer12" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                            Yes! After you've received your order, you can go to the My Purchase page to leave a star rating, write a review, and upload a photo of the item.                        </div>
                        </div>
                    </div>
                    <!-- Question 13 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq13">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer13">
                                Can I request customized uniform sizes?
                            </button>
                        </h2>
                        <div id="answer13" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                No. We currently do not accept custom uniform size requests. Only the standard sizes listed on the site are available for purchase.                    </div>
                            </div>
                    </div>
                    <!-- Question 14 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq14">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer14">
                                Can I return a product after receiving it?
                            </button>
                        </h2>
                        <div id="answer14" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, you may request a return if the product meets our return policy conditions. To do this, go to the “My Purchase” section, locate the order, and select the Return option. Please provide the reason for the return and upload photos of the product you want to return, then click Send Request. Our team will review your request and let you know if your return has been approved.
                            </div>
                        </div>
                    </div>
                    <!-- Question 15 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faq15">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#answer15">
                                What should I do if I have questions or concerns not addressed here?
                            </button>
                        </h2>
                        <div id="answer15" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                               If you can’t find the answer you’re looking for, simply use the chat feature on our website to talk directly with an admin for real-time assistance.
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Hidden Questions End -->
            </div>
            <div class="text-center mt-4">
                <button id="faqReadMoreBtn" class="custom-navy-btn px-4">Read More</button>
            </div>
        </div>
    </div>

    <!-- Footer and chat -->
    <?php include 'footer.php'; ?>
    <?php include 'chat.php'; ?>


    <!-- Animation Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            var more = document.getElementById('faqMore');
            var btn = document.getElementById('faqReadMoreBtn');
            var expanded = false;
            btn.addEventListener('click', function () {
                expanded = !expanded;
                if (expanded) {
                    more.style.display = '';
                    btn.textContent = 'Show Less';
                    // Scroll to the button if needed
                    btn.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    more.style.display = 'none';
                    btn.textContent = 'Read More';
                    // Optionally scroll back to top of FAQ
                    document.getElementById('faqAccordion').scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>

    <!-- Animation Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const sections = document.querySelectorAll(".fade-section");

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    // Add visible class when in viewport
                    if (entry.isIntersecting) {
                        entry.target.classList.add("visible");
                        observer.unobserve(entry.target); // run once per section
                    }
                });
            }, { threshold: 0.2 });

            sections.forEach(section => observer.observe(section));
        });
    </script>

    <!-- Collapse Search for small device Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const toggleBtn = document.getElementById('mobileSearchToggle');
            const searchBar = document.getElementById('mobileSearchBar');
            if (toggleBtn && searchBar) {
                toggleBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    searchBar.classList.toggle('d-none');
                    if (!searchBar.classList.contains('d-none')) {
                        searchBar.querySelector('input').focus();
                    }
                });
                // Optional: Hide search bar when clicking outside
                document.addEventListener('click', function (e) {
                    if (!searchBar.classList.contains('d-none') && !searchBar.contains(e.target) && e.target !== toggleBtn) {
                        searchBar.classList.add('d-none');
                    }
                });
            }
        });
    </script>

</body>
</html>