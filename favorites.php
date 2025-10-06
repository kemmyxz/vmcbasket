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
$favoriteSql = "SELECT p.id, p.product_name, p.price, p.image, p.type, p.tags,
                GROUP_CONCAT(DISTINCT pv.size) as sizes, 
                GROUP_CONCAT(DISTINCT pv.gender) as genders,
                COALESCE(AVG(r.rating), 0) as rating,
                COUNT(r.id) as rating_count
                FROM favorites f
                JOIN products p ON f.product_id = p.id
                JOIN product_variants pv ON p.id = pv.product_id
                LEFT JOIN product_reviews r ON p.id = r.product_id
                WHERE f.user_id = ? AND f.favorite = 1
                GROUP BY p.id, p.product_name, p.price, p.image, p.type, p.tags";
$stmt = $conn->prepare($favoriteSql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$favoriteResult = $stmt->get_result();


$student_no = $_SESSION['student_no'];
$sql = "SELECT * FROM users WHERE student_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_no);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Profile image fallback and full name
$profilePic = $user['photo'] ?? 'profile_pic.png';
$fullName = $user['student_fname'] . " " . $user['student_lname'];


?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket - My Favorites</title>
    <?php include 'links.php'; ?>
    <style>
        /* Responsive styles */
        .fav-icon {
            max-width: 250px;
            height: auto;
        }

        .margin-top {
            margin-top: 80px;
        }

        .fav-button.active {
            background: #e8261a;
            color: #fff;
        }

        .fav-button.active i {
            color: #fff;
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
            .fav-icon {
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
            .margin-top {
                margin-top: 20px;
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

            .highlight-pink {
                font-size: 1.2rem;
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
            <button class="btn btn-link text-dark me-3" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#sideMenu">
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
                <button type="button" class="btn-close btn btn-light" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>
            <div class="profile-section">
                <img src="admin/uploads/<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture">
                <h4 class="mt-2"><?php echo htmlspecialchars($fullName); ?></h4>
            </div>

            <div class="px-3">
                <div class="mb-2">
                    <button class="btn btn-link text-white w-100 text-start dropdown-toggle text-decoration-none"
                        data-bs-toggle="collapse" data-bs-target="#profileMenu">
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
                    <button class="btn btn-link text-white w-100 text-start dropdown-toggle text-decoration-none"
                        data-bs-toggle="collapse" data-bs-target="#shopMenu">
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
    <div class="container margin-top p-5">
        <h2 class="mt-4 mb-5">
            <span class="highlight-pink">My Favorites</span>
        </h2>
        <div class="row g-4">
            <?php if ($favoriteResult->num_rows > 0): ?>
                <?php while ($row = $favoriteResult->fetch_assoc()): ?>
                    <div class="col-md-4 col-lg-3">
                        <div class="product-card" onclick="location.href='product_details.php?id=<?= $row['id'] ?>'">

                            <!-- Product Image -->
                            <img src="admin/<?= htmlspecialchars($row['image']) ?>"
                                alt="<?= htmlspecialchars($row['product_name']) ?>">

                            <!-- Product Info -->
                            <div class="product-info">
                                <h3><?= htmlspecialchars($row['product_name']) ?></h3>
                                <?php
                                $genders = trim($row['genders'] ?? '');

                                if ($genders !== ''): ?>
                                    <p>
                                        <?= htmlspecialchars($genders) ?>
                                    </p>
                                <?php else: ?>
                                    <p>&nbsp;</p>
                                <?php endif; ?>

                                <div class="badges">
                                    <?php
                                   

                                    // Display badges for tags if they exist
                                    if (!empty($row['tags'])) {
                                        $tags = explode(',', $row['tags']);
                                        foreach ($tags as $tag): ?>
                                            <span class="badge tag_badge">
                                                <?= htmlspecialchars(ucfirst(trim($tag))) ?>
                                            </span><br>
                                        <?php endforeach;
                                    } 
                                    
                                     // Display badge for product type
                                    if (!empty($row['type'])): ?>
                                        <span class="badge <?= strtolower($row['type']) ?>_badge" style="margin-top: 5px;">
                                            <?= htmlspecialchars(ucfirst($row['type'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Price Range -->
                                <div class="price">
                                    ₱<?= number_format($row['price'], 2) ?>
                                </div>

                                <!-- Rating -->
                                <div class="rating">
                                    <?php
                                    $rating = $row['rating'] ?? 0;
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
                                    <button class="fav-button active"
                                        onclick="event.stopPropagation(); toggleFavorite(this, <?= $row['id'] ?>)">
                                        <i class="bi bi-heart-fill"></i>
                                    </button>
                                    <button class="basket-button"
                                        onclick="event.stopPropagation(); addToBasket(<?= $row['id'] ?>)">
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
                        <img src="./admin/images/favorites.png" alt="Empty Favorites" class="mb-4 fav-icon">
                        <h4 class="title-text fw-bold mt-1">Your Favorites is empty.</h4>
                        <p class="text-muted mb-5">Start shopping and find your new academic essentials.</p>
                        <a href="shop_uniforms.php" class="custom-navy-btn text-decoration-none">Go to Shop</a>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </div>
    </div>

    <!-- Footer and chat-->
    <?php include 'footer.php'; ?>
    <?php include 'chat.php'; ?>



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

    <script>

         function addToBasket(productId) {
            // Directly redirect to product details page
            window.location.href = `product_details.php?id=${productId}`;
        }

        function toggleFavorite(button, productId) {
            fetch('toggle_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const heartIcon = button.querySelector('i');
                        if (data.isFavorite) {
                            heartIcon.classList.remove('bi-heart');
                            heartIcon.classList.add('bi-heart-fill');
                            button.classList.add('active');
                        } else {
                            heartIcon.classList.remove('bi-heart-fill');
                            heartIcon.classList.add('bi-heart');
                            button.classList.remove('active');
                            // Remove the product card from favorites page
                            button.closest('.col-md-4').remove();

                            // Check if there are any products left
                            const productsContainer = document.querySelector('.row.g-4');
                            if (!productsContainer.children.length) {
                                // Show empty state
                                productsContainer.innerHTML = `
                        <section class="text-center py-3 mb-5">
                            <div class="container">
                                <img src="./admin/images/favorites.png" alt="Empty Favorites" class="mb-4 fav-icon">
                                <h4 class="title-text fw-bold mt-1">Your Favorites is empty.</h4>
                                <p class="text-muted mb-5">Start shopping and find your new academic essentials.</p>
                                <a href="shop_uniforms.php" class="custom-navy-btn text-decoration-none">Go to Shop</a>
                            </div>
                        </section>
                    `;
                            }
                        }
                    } else {
                        alert(data.message || 'Failed to update favorite');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating favorite');
                });
        }
    </script>
</body>

</html>