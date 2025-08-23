<?php
require 'admin/inc/config.php';
session_start();

// Prevent caching of the page
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Redirect if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get the user ID from session
$user_id = $_SESSION['user_id'] ?? 0;

// Initialize the conditions array for filtering products
$conditions = [];

// Filtering logic
if (!empty($_GET['type'])) {
    $type = $conn->real_escape_string($_GET['type']);
    $conditions[] = "type = '$type'";
}
if (!empty($_GET['size'])) {
    $size = $conn->real_escape_string($_GET['size']);
    $conditions[] = "size = '$size'";
}
if (!empty($_GET['gender'])) {
    $gender = $conn->real_escape_string($_GET['gender']);
    $conditions[] = "gender = '$gender'";
}

// Build WHERE clause
$whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Fetch products based on filter conditions
$sql = "SELECT * FROM products $whereClause ORDER BY date_modified DESC";
$result = $conn->query($sql);

// Fetch user's favorite products
$favoriteSql = "SELECT p.id, p.product_name, p.price, p.image, p.type,
                GROUP_CONCAT(DISTINCT pv.size) as sizes, 
                GROUP_CONCAT(DISTINCT pv.gender) as genders,
                COALESCE(AVG(r.rating), 0) as rating,
                COUNT(r.id) as rating_count
                FROM favorites f
                JOIN products p ON f.product_id = p.id
                JOIN product_variants pv ON p.id = pv.product_id
                LEFT JOIN product_reviews r ON p.id = r.product_id
                WHERE f.user_id = ? AND f.favorite = 1
                GROUP BY p.id, p.product_name, p.price, p.image, p.type";
$stmt = $conn->prepare($favoriteSql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$favoriteResult = $stmt->get_result();

// Fetch recommended products
$recommendations = "SELECT p.*, 
                   GROUP_CONCAT(DISTINCT pv.gender) as genders,
                   GROUP_CONCAT(DISTINCT pv.size) as sizes,
                   COALESCE(AVG(r.rating), 0) as rating,
                   COUNT(r.id) as rating_count
                   FROM products p
                   LEFT JOIN product_variants pv ON p.id = pv.product_id
                   LEFT JOIN product_reviews r ON p.id = r.product_id
                   WHERE p.type='uniform' OR p.type='supplies'
                   GROUP BY p.id, p.product_name, p.price, p.image, p.type";
$rec_result = $conn->query($recommendations);
?>







<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket - My Favorites</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="style.css">

    <style>
/* Responsive styles */

    .fav-icon{
        max-width: 300px;
        height: auto;
    }
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
        .fav-icon{
            max-width: 200px;
            height: auto;
        }
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
        /* Extra small: 1 column for product cards */
        .col-md-4,
        .col-lg-3 {
            flex: 0 0 100%;
            max-width: 100%;
        }
        .product-card img {
            height: 180px;
        }
        .highlight-pink {
            font-size: 1rem;
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
            <a href="basket.php" class=" basket-btn text-decoration-none">
                <i class="fas fa-shopping-basket"></i>
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
                <img src="admin/images/profile_pic.png">
                <h4 class="mt-2">Janella Clare Gomez</h4>
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

    <!-- Favorites Page -->
    <div class="container" style="margin-top: 110px;">
            <h2 class="mt-4 mb-5">
                <span class="highlight-pink">My Favorites</span>
            </h2>
            <div class="row g-4">
                <?php if ($favoriteResult->num_rows > 0): ?>
                    <?php while ($row = $favoriteResult->fetch_assoc()): ?>
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
                    <?php endwhile; ?>
                <?php else: ?>
                    <section class="text-center py-3 mb-5">
                        <div class="container">
                            <img src="./admin/images/favorites.png" alt="Empty Favorites" class="img-fluid mb-4 fav-icon">
                            <h4 class="title-text fw-bold mt-1">Your Favorites is empty.</h4>
                            <p class="text-muted mb-5">Start shopping and find your new academic essentials.</p>
                            <a href="shop_uniforms.php" class="custom-navy-btn text-decoration-none">Go to Shop</a>
                        </div>
                    </section>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container p-5">

            <!-- Logo Row -->
            <div class="row justify-content-start mb-4">
            <div class="col-auto d-flex justify-content-center align-items-center gap-3 footer-logo">
                <img src="admin/images/vmc_basket_logo.png" alt="VMC Basket Logo" class="footer-logo" >
                <img src="admin/images/VMC School logo.png" alt="School Logo" class="footer-logo">
            </div>
            </div>

            <!-- Links & Contacts Row -->
            <div class="row text-start gy-3">

            <!-- Quick Links -->
            <div class="col-md-3">
                <h5 class="fw-bold">Quick Links</h5>
                <ul class="list-unstyled">
                <li><a href="#" class="footer-link">Home</a></li>
                <li><a href="#" class="footer-link">Shop</a></li>
                </ul>
            </div>

            <!-- Contacts -->
            <div class="col-md-7">
                <h5 class="fw-bold">Contacts</h5>
                <p class="mb-1">
                <i class="bi bi-geo-alt-fill"></i>
                18 Dalsol Rd. GSIS Village, Sangandaan, Quezon City, 1116 Metro Manila, Philippines
                </p>
                <p class="mb-1">
                <i class="bi bi-telephone-fill"></i>
                +63 2 8929 0856
                </p>
                
                <div class="d-flex gap-3">
                    <p class="mb-1 fw-medium">Socials Media</p>
                    <a href="#" class="footer-icon fs-5"><i class="bi bi-globe"></i></a>
                    <a href="#" class="footer-icon fs-5"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="footer-icon fs-5"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="footer-icon fs-5"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

            <!-- Back to top -->
            <div class="col-md-2 d-flex align-items-end justify-content-md-end">
                <a href="#" class="footer-link">↑ Back to top</a>
            </div>
            </div>

            <!-- Divider -->
            <hr class="mt-5 mb-3">

            <!-- Copyright -->
            <div class="sub-footer text-center small">
            © 2024 Villager’s Montessori College. All rights served.
            </div>
        </div>
    </footer>



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
