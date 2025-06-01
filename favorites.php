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
    <title>VMC Basket-Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/style.css">

    <style>
        .fav-title{
            font-family: "Ubuntu", sans-serif;
            font-weight: bold;
            color: #00527F;
        }

           
        .carousel-container {
            background-color: #E8EDEF;
            padding: 40px 20px;
        }

        .carousel-control-prev, .carousel-control-next {
            width: 40px;
            height: 40px;
            background: rgba(0, 0, 0, 0.5); /* Semi-transparent */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
        }

        .carousel-control-prev {
            left: 0px; /* Adjust this to move the button inside */
        }

        .carousel-control-next {
            right: 0px; /* Adjust this to move the button inside */
        }

        .carousel-control-prev-icon, 
        .carousel-control-next-icon {
            width: 15px;
            height: 15px;
        }

        /* Product Card */
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

        /* Heart Button */

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
        .product-rating {
            color: #000000;
            font-size: 14px;
            display: flex;
            align-items: center;
        }

        .text-warning {
            color: #FFD700 !important;
        }

        .bi-star-fill.text-warning {
            color: #FFD700;
        }

        .bi-star-half.text-warning {
            color: #FFD700;
        }

        .bi-star.text-warning {
            color: #ccc;
        }

        .product-rating span {
            font-size: 12px;
            color: #666;
            margin-left: 5px;
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
        .form-select {
            appearance: none; /* Hides default arrow */
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="white"><path d="M1.5 5.5l6.5 6.5 6.5-6.5H1.5z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 16px;
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
                <li><a href="profile.php"><img src="admin/images/Home Page/profile-user-nav.png" alt="profile"></a></li>
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
                <li><a href="index.php">Home</a></li>
                <li><a href="shop.php" class="active">Shop</a></li>
                <li><a href="contact.php">Contact us</a></li>
            </ul>
        </nav>
        <div class="search" style="display: flex; align-items: center; justify-content: space-between; width: auto;">
            <div class="search-container me-4">
                <input type="text" class="form-control" placeholder="Search...">
                <button><img src="admin/images/search-icon.png" alt="Search"></button>
            </div>
        </div>
    </div>

    <!-- Favorites Page -->
    <h2 class="text-start fav-title ms-4 mt-3 mb-4">My Likes</h2>

    <div class="container mb-5">
        <div class="row g-4">
            <?php if ($favoriteResult->num_rows > 0): ?>
                <?php while ($row = $favoriteResult->fetch_assoc()): ?>
                    <div class="col-md-4 col-lg-3">
                        <div class="product-card" onclick="location.href='product_details.php?id=<?= $row['id'] ?>'">
                            <button class="heart-btn" onclick="toggleFavorite(event, this, <?= $row['id'] ?>)">
                                <img src="./admin/images/heart.png" alt="Favorite">
                            </button>
                            <img src="./admin/<?= $row['image'] ?>" alt="<?= htmlspecialchars($row['product_name']) ?>">
                            <div class="product-info">
                                <h3><?= htmlspecialchars($row['product_name']) ?></h3>
                                <?php if (strtolower($row['type']) !== 'supplies'): ?>
                                    <p>Available sizes: <?= htmlspecialchars($row['sizes']) ?></p>
                                <?php endif; ?>
                                <p>For: <?= htmlspecialchars($row['genders']) ?></p>
                                <hr class="product-line">
                                <div class="d-flex justify-content-between">
                                    <h4 class="price">₱<?= number_format($row['price'], 2) ?></h4>
                                    <div class="product-rating">
                                        <?php
                                        $rating = $row['rating'] ?? 0;
                                        $fullStars = floor($rating);
                                        $halfStar = round($rating - $fullStars, 1) >= 0.5;
                                        
                                        // Display full stars
                                        for ($i = 0; $i < $fullStars; $i++) {
                                            echo '<i class="bi bi-star-fill text-warning"></i>';
                                        }
                                        
                                        // Display half star if applicable
                                        if ($halfStar) {
                                            echo '<i class="bi bi-star-half text-warning"></i>';
                                            $i++;
                                        }
                                        
                                        // Display empty stars
                                        for (; $i < 5; $i++) {
                                            echo '<i class="bi bi-star text-warning"></i>';
                                        }
                                        
                                        echo '<span class="ms-1">(' . number_format($rating, 1) . ')</span>';
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <section class="text-center py-5">
                    <div class="container">
                        <img src="./admin/images/favorites.png" alt="Empty Favorites" style="max-width: 300px;">
                        <h4 class="fav-title mt-1">Your Favorites is empty.</h4>
                        <p class="text-muted">Start shopping and find your new uniform.</p>
                        <a href="shop.php" class="btn btn-secondary px-4 py-2 shadow-sm mt-2">Go to Shop</a>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </div>

    <!-- Product Listing / Recommendations Section -->
    <div class="carousel-container">
        <h2 class="mb-4 fav-title">Buy your VMC Essentials</h2>

        <div class="container mb-5">
            <div class="row g-4">
                <?php
                if ($rec_result && $rec_result->num_rows > 0):
                    while ($rec_row = $rec_result->fetch_assoc()):
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
                            <p> <?= htmlspecialchars($rec_row['genders'] ?? '') ?></p>
                            <hr class="product-line">
                            <div class="d-flex justify-content-between">
                                <h4 class="price">₱<?= number_format($rec_row['price'], 2) ?></h4>
                                <div class="product-rating">
                                    <?php
                                    $rating = $rec_row['rating'] ?? 0;
                                    $fullStars = floor($rating);
                                    $halfStar = round($rating - $fullStars, 1) >= 0.5;
                                    
                                    // Display full stars
                                    for ($i = 0; $i < $fullStars; $i++) {
                                        echo '<i class="bi bi-star-fill text-warning"></i>';
                                    }
                                    
                                    // Display half star if applicable
                                    if ($halfStar) {
                                        echo '<i class="bi bi-star-half text-warning"></i>';
                                        $i++;
                                    }
                                    
                                    // Display empty stars
                                    for (; $i < 5; $i++) {
                                        echo '<i class="bi bi-star text-warning"></i>';
                                    }
                                    
                                    echo '<span class="ms-1">(' . number_format($rating, 1) . ')</span>';
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; else: ?>
                    <p>No recommended products available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    // Toggle favorite status
    function toggleFavorite(event, btn, productId) {
        event.stopPropagation(); // Prevents redirection
      
        
        let heartImg = btn.querySelector("img");
        let isFavorited = heartImg.src.includes("heart.png") ? 1 : 0;
        let newStatus = isFavorited ? 0 : 1; // Toggle the current status

        // Change heart icon immediately
        heartImg.src = newStatus ? "./admin/images/heart.png" : "./admin/images/heart-outline.png";

        // Send AJAX request to update favorite status
        const data = `product_id=${productId}&favorite=${newStatus}`;
        fetch('update_favorites.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: data
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Failed to update favorite: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong.');
        });
    }
    </script>
</body>
</html>
