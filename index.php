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
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket-Homepage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"><link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="./CSS/style.css">
    <style>
        .row > div {
            display: flex; /* Ensure all cards in a row are the same height */
        }
        .product-card {
            background-color: #C8D9E6;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;

            /* New for equal height & layout */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            width: 100%; 
            min-height: 410px;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 2px 2px 15px rgba(0, 0, 0, 0.2);
        }

        .product-card img {
            width: 100%;
            height: 250px; /* or adjust as needed */
            object-fit: contain;
            margin-bottom: 10px;
        }

        .product-card:hover img {
            transform: scale(1.1); 
        }
        
        .product-title{
            font-weight: medium;
            margin-left: 100px;
            margin-top: 50px;
            margin-bottom: 20px;
        }

        /* Product Info */
        .product-info {
            color: #000000;
            text-align: left;
            padding: 10px 5px;
        }

        .product-info h3 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .product-info p {
            font-size: 14px;
            margin-bottom: 2px;
        }

        .price {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .product-line{
            border: 1px solid #000000;
        }

        /* Star Rating */
        .rating {
            color: #000000;
            font-size: 14px;
        }

        .rating i {
            margin-right: 2px;
        }

        .filter{
            background-color: white;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #00527F;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .filter-color{
            color: white;
            background-color:#00527F;
        }
        .filter-title{
            font-family: "Ubuntu", sans-serif;
            color: #00527F;
            font-weight: bold;
        }
        /* Heart Button */
        .heart-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        border: none;
        background: none;
        font-size: 22px;
        color: black;
        cursor: pointer;
        z-index: 10;
        }

        .heart-btn img {
        width: 24px; /* Adjust size */
        height: auto;
        }

        .rating {
        display: flex;
        align-items: center;
        color: #000000;
        font-size: 14px;
    }

    .rating i {
        margin-right: 2px;
        font-size: 16px;
    }

    .rating .text-warning {
        color: #FFC107 !important;
    }

    .rating span {
        font-size: 12px;
        color: #666;
    }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="top-text"><h1>ALL PRODUCTS ARE AVAILABLE FOR PICK-UP ONLY AT VILLAGERS MONTESSORI COLLEGE</h1></div>
        <div class="top-container">
            <ul>
                <li><a href="basket.php"><img src="admin/images/Home Page/basket-nav.png" alt="Basket"></a></li>  
                <li><a href="favorites.php"><img src="admin/images/Home Page/heart-nav.png"></a></li>
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
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="contact.php">Contact us</a></li>
            </ul>
        </nav>
        <div class="search" style="display: flex;  align-items: center; justify-content: space-between; width: auto;">
            <div class="search-container me-4">
                <input type="text" class="form-control" placeholder="">
                <button><img src="admin/images/search-icon.png" alt="Search"></button>
            </div>
        </div>
    </div>

    <!-- Main Sections of VMC Basket Homepage -->

<!-- HERO SECTION / CAROUSEL -->
<div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
        
    </div>
    <div class="carousel-inner">
        <div class="carousel-item active">
            <img src="admin/images/Home Page/VMC-Carousel1 copy.png" class="d-block w-100" alt="VMC Uniforms">
        </div>
        <div class="carousel-item">
            <img src="admin/images/vmc-carousel2.png" class="d-block w-100" alt="VMC Uniforms">
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
<div class="tagline d-flex justify-content-between align-items-center text-white text-center px-5 py-3">
    <h2>Total Quality Education, Our Thrust</h2>
    <h2>Shop Now Montessorians!</h2>
</div>

<!-- CATEGORY ICONS -->
<div class="custom-icon container text-center my-5">
    <div class="row justify-content-center">
        <div class="col-4 col-md-3">
            <a href="shop.php" style="text-decoration: none; color: black;">
                <img src="admin/images/Home Page/Uniform.png " class="category-icon" alt="Official Uniforms">
                <p class="container-text">Official Uniforms</p>
            </a>
        </div>
        <div class="col-4 col-md-3">
            <a href="shop.php" style="text-decoration: none; color: black;">
                <img src="admin/images/Home Page/Supplies.png " class="category-icon" alt="School Supplies">
                <p class="container-text">School Supplies</p>
            </a>
        </div>
        <div class="col-4 col-md-3">
            <a href="shop.php" style="text-decoration: none; color: black;">
                <img src="admin/images/Home Page/NewArrivals.png " class="category-icon" alt="New Release">
                <p class="container-text">New Release</p>
            </a>
        </div>
    </div>
</div>

<!-- ANNOUNCEMENT CORNER -->
<div class="announcement-container py-5">
    <div class="container text-center">
        <h2 class="homepage-title mb-3">Announcement Corner</h2>
        <p>Any announcements regarding our school uniforms or other school-related apparel will be displayed in this corner!</p>
        <div id="announcementCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#announcementCarousel" data-bs-slide-to="0" class="active"></button>
                <button type="button" data-bs-target="#announcementCarousel" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#announcementCarousel" data-bs-slide-to="2"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="admin/images/Home Page/Announcement Corner.png" class="d-block w-100 announcement-img" alt="Announcement">
                </div>
                <div class="carousel-item">
                    <img src="admin/images/Home Page/Announcement Corner.png" class="d-block w-100 announcement-img" alt="Announcement">
                </div>
                <div class="carousel-item">
                    <img src="admin/images/Home Page/Announcement Corner.png" class="d-block w-100 announcement-img" alt="Announcement">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fullscreen Image Viewer -->
<div id="fullscreenViewer" class="fullscreen-viewer">
    <img id="fullscreenImage" src="" alt="Full-size Announcement">
</div>


<!-- SHOP BY YEAR-LEVEL -->
<div class="container text-center my-5" style= "margin-top: 50px;">
    <h2 class="homepage-title mb-4">Shop by Year-level</h2>
    <div class="year-level row g-4 justify-content-center">
        <!-- First Row -->
        <div class="col-md-6">
            <a href="shop.php"><img src="admin/images/Home Page/Pre-School & Elementary.png" class="img-fluid rounded shadow" alt="Pre-School & Elementary"></a>
        </div>
        <div class="col-md-6">
            <a href="shop.php"><img src="admin/images/Home Page/Junior High School.png" class="img-fluid rounded shadow" alt="Junior High"></a>
        </div>
        <!-- Second Row -->
        <div class="col-md-6">
            <a href="shop.php"><img src="admin/images/Home Page/Senior High School.png" class="img-fluid rounded shadow" alt="Senior High"></a>
        </div>
        <div class="col-md-6">
            <a href="shop.php"><img src="admin/images/Home Page/College.png" class="img-fluid rounded shadow" alt="College"></a>
        </div>
    </div>
</div>

<!-- FEATURED PRODUCTS -->
<!-- Products -->
<div class="container text-center mb-4" style="margin-top: 100px;">
    <h2 class="homepage-title mb-4">Featured Products</h2>

        <div class="container mb-5">
            <div class="row g-4">
                <?php
                if ($rec_result && $rec_result->num_rows > 0):
                    $count = 0; // Initialize counter
                    while ($rec_row = $rec_result->fetch_assoc()):
                        if ($count >= 8) break; // Break loop after 8 products
                ?>
                <div class="col-md-4 col-lg-3">
                    <div class="product-card" onclick="location.href='product_details.php?id=<?= $rec_row['id'] ?>'">
                        <button class="heart-btn" onclick="toggleFavorite(event, this, <?= $rec_row['id'] ?>)">
                            <img src="./admin/images/heart-outline.png" alt="Favorite">
                        </button>
                        <img src="admin/<?= htmlspecialchars($rec_row['image']) ?>" alt="<?= htmlspecialchars($rec_row['product_name']) ?>">
                        <div class="product-info">
                            <h3><?= htmlspecialchars($rec_row['product_name']) ?></h3>
                            <?php if (strtolower($rec_row['type']) !== 'supplies'): ?>
                                <p>Available sizes: <?= htmlspecialchars($rec_row['sizes'] ?? 'N/A') ?></p>
                            <?php endif; ?>
                            <p><?= htmlspecialchars($rec_row['genders'] ?? '') ?></p>
                            <hr class="product-line">
                            <div class="d-flex justify-content-between">
                                <h4 class="price">₱<?= number_format($rec_row['price'], 2) ?></h4>
                                <div class="rating">
                                    <?php
                                    $rating = $rec_row['rating'] ?? 0;
                                    $fullStars = floor($rating);
                                    $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                    
                                    // Output full stars
                                    for ($i = 0; $i < $fullStars; $i++): ?>
                                        <i class="bi bi-star-fill text-warning"></i>
                                    <?php endfor;

                                    // Output half star if applicable
                                    if ($hasHalfStar): ?>
                                        <i class="bi bi-star-half text-warning"></i>
                                    <?php endif;

                                    // Output empty stars
                                    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                                    for ($i = 0; $i < $emptyStars; $i++): ?>
                                        <i class="bi bi-star text-warning"></i>
                                    <?php endfor; ?>
                                    <span class="ms-1">(<?= number_format($rating, 1) ?>)</span>
                                </div>
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


   
    <div class="text-center mt-4">
    <a href="shop.php" class="see-more-btn">See More</a>
    </div>

    
    <!-- FAQ SECTION -->
<div class="faq-container py-5">
    <div class="container">
        <h2 class="homepage-title mb-4 text-center">Frequently Asked Questions</h2>
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
                        VMC Basket offers a wide range of essential products that every student at Villagers Montessori College may need throughout the school year. This includes various school supplies, exclusive VMC notebooks, and official school apparel such as uniforms and PE uniforms available for all year levels.
                        Our goal is to make it easier for students and parents to find everything they need in one convenient online platform.
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
                        To help you choose the right fit, size information is provided in the description section of each product. We highly recommend reviewing the size chart carefully before placing your order to ensure the perfect fit, especially for uniforms and PE attire.
                        If you’re unsure, feel free to reach out to us or visit the school bookstore for sample sizing.
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
                        Currently, VMC Basket only accepts payment through over-the-counter transactions at the school's cashier.
                        Once you place your order online, you may settle your payment at the school, making it safe and secure for all students and parents.
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
                        What if I need to return or exchange an item?
                    </button>
                </h2>
                <div id="answer5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        If you need to return or exchange a product, we’re here to assist you. Simply go to the "My Purchase" page within your user account and locate the specific product you wish to return. You will find a return request form available there. Please note that the option to return will only be available if the product has not yet been rated. 
                        Once your request is submitted, our team will review it and assist you with the return or exchange process.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>

<script>
    function toggleFavorite(event, btn) {
    event.stopPropagation(); // Prevents redirection
    let heartImg = btn.querySelector("img");
    
    if (heartImg.src.includes("heart-outline.png")) {
        heartImg.src = "./Images/heart.png"; // Change to filled heart
    } else {
        heartImg.src = "./Images/heart-outline.png"; // Change back to outlined heart
    }
}

    document.addEventListener("DOMContentLoaded", function () {
        // Select all images inside the announcement carousel
        const announcementImages = document.querySelectorAll(".announcement-img");

        // Select fullscreen viewer elements
        const fullscreenViewer = document.getElementById("fullscreenViewer");
        const fullscreenImage = document.getElementById("fullscreenImage");

        // Show fullscreen image on click
        announcementImages.forEach(img => {
            img.addEventListener("click", function () {
                fullscreenImage.src = this.src; // Set the fullscreen image
                fullscreenViewer.style.display = "flex"; // Show fullscreen viewer
            });
        });

        // Hide fullscreen viewer when clicking outside the image
        fullscreenViewer.addEventListener("click", function () {
            fullscreenViewer.style.display = "none";
        });
    });
</script>

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