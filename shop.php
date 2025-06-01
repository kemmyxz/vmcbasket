<?php 
require('admin/inc/config.php');
// If user is not logged in, redirect to login


// Prevent browser from caching the page
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Set number of items per page
$items_per_page = 8;

// Get current page number from URL parameter
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

$search = $_GET['search'] ?? '';

?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket-Shop Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"><link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="CSS/style.css">

    <style>
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

        .stock-info {
        color: #666;
        font-size: 14px;
        margin: 5px 0;
        padding: 2px 5px;
        background-color: #f8f9fa;
        border-radius: 3px;
        }

        .stock-info.out-of-stock {
            color: #dc3545;
            background-color: #f8d7da;
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
     <div class="navbar shadow-sm mb-4">
        <div class="logo ms-4">
            <a href="index.php"><img src="admin/images/Admin Nav/VMS-LOGO-Alternative-03.png" alt="logo"></a>
            <h2>VMC Basket</h2>
        </div>
        <nav>
            <ul>
                <li><a href="index.php" >Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="contact.php">Contact us</a></li>
            </ul>
        </nav>
        <div class="search" style="display: flex;  align-items: center; justify-content: space-between; width: auto;">
                    <div class="search-container me-4">
            <form action="shop.php" method="GET" class="d-flex">
                <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <button type="submit"><img src="admin/images/search-icon.png" alt="Search"></button>
            </form>
        </div>
        </div>
    </div>

    <!-- Shop Page -->
    <!-- Filter Section -->
    <form method="GET" action="">
    <div class="container filter my-4">
        <div class="d-flex align-items-center flex-wrap">
            <h4 class="me-4 filter-title mt-1">
                <img src="admin/images/sort.png" alt="Filter Icon" style="width: 50px; height:auto;"> Filter
            </h4>
            <div class="row flex-grow-1 g-2">
                <div class="col-md-4 col-6">
                    <select name="type" class="filter-color form-select py-2">
                        <option disabled <?= !isset($_GET['type']) ? 'selected' : '' ?>>Type</option>
                        <option <?= @$_GET['type'] == 'Uniform' ? 'selected' : '' ?>>Uniforms</option>
                        <option <?= @$_GET['type'] == 'Supplies' ? 'selected' : '' ?>>School Supplies</option>
                    </select>
                </div>

                <div class="col-md-4 col-6">
                    <select name="size" class="filter-color form-select py-2">
                        <option disabled <?= !isset($_GET['size']) ? 'selected' : '' ?>>Sizes</option>
                        <option <?= @$_GET['size'] == 'XS' ? 'selected' : '' ?>>XS</option>
                        <option <?= @$_GET['size'] == 'S' ? 'selected' : '' ?>>S</option>
                        <option <?= @$_GET['size'] == 'M' ? 'selected' : '' ?>>M</option>
                        <option <?= @$_GET['size'] == 'L' ? 'selected' : '' ?>>L</option>
                        <option <?= @$_GET['size'] == 'XL' ? 'selected' : '' ?>>XL</option>
                    </select>
                </div>

                <div class="col-md-3 col-6">
                    <select name="gender" class="filter-color form-select py-2">
                        <option disabled <?= !isset($_GET['gender']) ? 'selected' : '' ?>>Gender</option>
                        <option <?= @$_GET['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                        <option <?= @$_GET['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                        <option <?= @$_GET['gender'] == 'Unisex' ? 'selected' : '' ?>>Unisex</option>
                    </select>
                </div>

                <div class="col-md-1 col-6">
                    <button type="submit" class="btn btn-primary w-100">Enter</button>
                </div>
            </div>
        </div>
    </div>
</form>

<?php
$type = $_GET['type'] ?? '';
$size = $_GET['size'] ?? '';
$gender = $_GET['gender'] ?? '';

$hasFilter = $type || $size || $gender;

if (!empty($search)) {
    // Base query for search
    $sql = "SELECT DISTINCT p.*, 
            MIN(pv.gender) as gender, 
            GROUP_CONCAT(DISTINCT pv.size) as sizes,
            SUM(pv.stock) as total_stock
            FROM products p
            LEFT JOIN product_variants pv ON p.id = pv.product_id
            WHERE p.product_name LIKE ?
            GROUP BY p.id
            LIMIT ? OFFSET ?";

    // Count total searched items
    $count_sql = "SELECT COUNT(DISTINCT p.id) as total 
                  FROM products p 
                  WHERE p.product_name LIKE ?";
    
    $search_param = "%{$search}%";
    
    // Get total count
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("s", $search_param);
    $count_stmt->execute();
    $total_items = $count_stmt->get_result()->fetch_assoc()['total'];
    $total_pages = ceil($total_items / $items_per_page);
    
    // Prepare and execute main query
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $search_param, $items_per_page, $offset);
    $stmt->execute();
    $searchResults = $stmt->get_result();
    
    // Display search results
    if ($searchResults->num_rows > 0): ?>
        <h3 class="text-start product-title">Search Results for: "<?= htmlspecialchars($search) ?>"</h3>
        <div class="container mb-5">
            <div class="row g-4">
                <?php while ($row = $searchResults->fetch_assoc()): ?>
                    <div class="col-md-4 col-lg-3">
                        <div class="product-card" onclick="location.href='product_details.php?id=<?= $row['id'] ?>'">
                            <button class="heart-btn" onclick="toggleFavorite(event, this, <?= $row['id'] ?>)">
                                <img src="admin/images/heart-outline.png" alt="Favorite">
                            </button>
                            <img src="admin/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>">
                            <div class="product-info">
                                <h3><?= htmlspecialchars($row['product_name']) ?></h3>
                                <?php if (!empty($row['gender'])): ?>
                                    <p><?= htmlspecialchars($row['gender']) ?></p>
                                <?php endif; ?>
                                <hr class="product-line">
                                <div class="d-flex justify-content-between">
                                    <h4 class="price">₱<?= number_format($row['price'], 2) ?></h4>
                                    <div class="product-rating">
                                        <?php
                                        // Your existing rating display code here
                                        $rating = $row['rating'] ?? 0;
                                        $fullStars = floor($rating);
                                        $halfStar = round($rating - $fullStars, 1) >= 0.5;
                                        
                                        for ($i = 0; $i < $fullStars; $i++) {
                                            echo '<i class="bi bi-star-fill text-warning"></i>';
                                        }
                                        
                                        if ($halfStar) {
                                            echo '<i class="bi bi-star-half text-warning"></i>';
                                            $i++;
                                        }
                                        
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
            </div>
        </div>
        <?php if ($total_pages > 1): ?>
            <div aria-label="Page navigation">
                <ul class="pagination justify-content-end" style="margin-right: 5%;">
                    <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                        <li class="page-item <?= $page == $current_page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page])) ?>"><?= $page ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="container">
            <div class="alert alert-info">
                No products found matching "<?= htmlspecialchars($search) ?>".
            </div>
        </div>
    <?php endif;
} elseif ($hasFilter) {
    // Base query
    $sql = "SELECT DISTINCT p.*, ";
    
    // Only include variant information for uniforms
    if ($type !== 'School Supplies') {
        $sql .= "MIN(pv.gender) as gender, 
                GROUP_CONCAT(DISTINCT pv.size) as sizes,
                SUM(pv.stock) as total_stock";
    } else {
        $sql .= "NULL as gender, 
                NULL as sizes,
                NULL as total_stock";
    }
    
    $sql .= " FROM products p";
    
    // Only join variants table for uniforms
    if ($type !== 'School Supplies') {
        $sql .= " LEFT JOIN product_variants pv ON p.id = pv.product_id";
    }
    
    $sql .= " WHERE 1=1";
    
    $params = array();
    
    // Handle type filter
    if (!empty($type)) {
        $dbType = ($type === 'Uniforms') ? 'Uniform' : ($type === 'School Supplies' ? 'Supplies' : $type);
        $sql .= " AND p.type = ?";
        $params[] = $dbType;
        
        // Only apply size and gender filters for uniforms
        if ($dbType !== 'Supplies') {
            if (!empty($size)) {
                $sql .= " AND pv.size = ?";
                $params[] = $size;
            }
            
            if (!empty($gender)) {
                $sql .= " AND pv.gender = ?";
                $params[] = $gender;
            }
        }
    }
    
    $sql .= " GROUP BY p.id";
    
    // Count total filtered items
    $count_sql = "SELECT COUNT(DISTINCT p.id) as total FROM products p";
    if ($type !== 'School Supplies') {
        $count_sql .= " LEFT JOIN product_variants pv ON p.id = pv.product_id";
    }
    $count_sql .= " WHERE 1=1";
    // ... (rest of your WHERE conditions)
    
    $count_stmt = $conn->prepare($count_sql);
    if (!empty($params)) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $total_items = $count_stmt->get_result()->fetch_assoc()['total'];
    $total_pages = ceil($total_items / $items_per_page);
    
    // Add LIMIT and OFFSET to your main query
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $items_per_page;
    $params[] = $offset;
    
    // Update your bind_param call with two more integers
    $types .= 'ii';
    
    // Prepare and execute statement
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $filterResults = $stmt->get_result();
}
?>


<!-- Filtered Products -->
<?php if ($hasFilter): ?>
    <h3 class="text-start product-title">
        Filtered Results: 
        <?php 
        $filterLabels = [];
        if ($type) $filterLabels[] = $type;
        if ($gender) $filterLabels[] = $gender;
        if ($size) $filterLabels[] = "Size " . $size;
        echo implode(' - ', $filterLabels);
        ?>
    </h3>
    <div class="container mb-5">
        <div class="row g-4">
            <?php if ($filterResults && $filterResults->num_rows > 0): ?>
                <?php while ($row = $filterResults->fetch_assoc()): ?>
                    <div class="col-md-4 col-lg-3">
                        <div class="product-card" onclick="location.href='product_details.php?id=<?= $row['id'] ?>'">
                            <button class="heart-btn" onclick="toggleFavorite(event, this, <?= $row['id'] ?>)">
                                <img src="admin/images/heart-outline.png" alt="Favorite">
                            </button>
                            <img src="admin/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>">
                            <div class="product-info">
                                <h3><?= htmlspecialchars($row['product_name']) ?></h3>
                                <?php if ($type !== 'School Supplies'): ?>
                                    <?php if (!empty($row['gender'])): ?>
                                        <p><?= htmlspecialchars($row['gender']) ?></p>
                                    <?php endif; ?>

                                <?php endif; ?>
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
                <div class="col-12">
                    <div class="alert alert-info">
                        No products found matching your filter criteria.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div aria-label="Page navigation">
        <ul class="pagination d-flex justify-content-end" style="margin-right: 5%;">
            <?php for ($page = 1; $page <= $total_pages; $page++): ?>
                <li class="page-item <?= $page == $current_page ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page])) ?>"><?= $page ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </div>
<?php endif; ?>


<?php if (!$hasFilter && empty($search)): ?>
    <!-- BASIC EDUCATION UNIFORMS -->
    <h3 class="text-start product-title">Basic Education Uniforms</h3>
    <div class="container mb-5">
        <div class="row g-4">
            <?php
            $uniforms_sql = "SELECT COUNT(*) as total FROM products WHERE type='Uniform'";
            $uniforms_result = $conn->query($uniforms_sql);
            $uniforms_total = $uniforms_result->fetch_assoc()['total'];
            $uniforms_total_pages = ceil($uniforms_total / $items_per_page);

            $uniforms_sql = "SELECT p.*, pv.gender, pv.size 
                             FROM products p 
                             LEFT JOIN product_variants pv ON p.id = pv.product_id 
                             WHERE p.type='Uniform'
                             GROUP BY p.id
                             LIMIT $items_per_page OFFSET $offset";
            $result = $conn->query($uniforms_sql);

            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
            ?>
            <div class="col-md-4 col-lg-3">
                <div class="product-card" onclick="location.href='product_details.php?id=<?= $row['id'] ?>'">
                    <button class="heart-btn" onclick="toggleFavorite(event, this,  <?= $row['id'] ?>)">
                        <img src="admin/images/heart-outline.png" alt="Favorite">
                    </button>
                    <img src="admin/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>">
                    <div class="product-info">
                        <h3><?= htmlspecialchars($row['product_name']) ?></h3>
                        <?php if (!empty($row['gender'])): ?>
                            <p><?= htmlspecialchars($row['gender']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($_GET['size'])): ?>
                            <p class="stock-info <?= $row['stock_quantity'] == 0 ? 'out-of-stock' : '' ?>">
                                Stock: <?= (int)$row['stock_quantity'] ?>
                            </p>
                        <?php endif; ?>
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
            <?php endwhile; else: ?>
                <p>No uniforms found.</p>
            <?php endif; ?>
        </div>
        
    </div>
   

    <!-- SCHOOL SUPPLIES -->
    <h3 class="text-start product-title">School Supplies</h3>
    <div class="container mb-5">
        <div class="row g-4">
            <?php
            $supplies_sql = "SELECT COUNT(*) as total FROM products WHERE type='Supplies'";
            $supplies_result = $conn->query($supplies_sql);
            $supplies_total = $supplies_result->fetch_assoc()['total'];
            $supplies_total_pages = ceil($supplies_total / $items_per_page);

            $supplies_sql = "SELECT * FROM products 
                            WHERE type='Supplies'
                            LIMIT $items_per_page OFFSET $offset";
            $result = $conn->query($supplies_sql);

            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
            ?>
            <div class="col-md-4 col-lg-3">
                <div class="product-card" onclick="location.href='product_details.php?id=<?= $row['id'] ?>'">
                    <button class="heart-btn" onclick="toggleFavorite(event, this,  <?= $row['id'] ?>)">
                        <img src="admin/images/heart-outline.png" alt="Favorite">
                    </button>
                    
                    <img src="admin/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['product_name']) ?>">
                    <div class="product-info">
                        <h3><?= htmlspecialchars($row['product_name']) ?></h3>
                        <?php if (!empty($row['gender'])): ?>
                            <p><?= htmlspecialchars($row['gender']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($_GET['size'])): ?>
                            <p class="stock-info <?= $row['stock_quantity'] == 0 ? 'out-of-stock' : '' ?>">
                                Stock: <?= (int)$row['stock_quantity'] ?>
                            </p>
                        <?php endif; ?>
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
            <?php endwhile; else: ?>
                <p>No supplies found.</p>
            <?php endif; ?>
        </div>
    </div>
    <div aria-label="Page navigation">
        <ul class="pagination justify-content-end" style="margin-right: 5%;">
            <?php for ($page = 1; $page <= $supplies_total_pages; $page++): ?>
                <li class="page-item <?= $page == $current_page ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page])) ?>"><?= $page ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </div>
<?php endif; ?>





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
   


    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.querySelector('select[name="type"]');
        const sizeSelect = document.querySelector('select[name="size"]');
        const genderSelect = document.querySelector('select[name="gender"]');

        function updateFilters() {
            const isSupplies = typeSelect.value === 'School Supplies';
            sizeSelect.disabled = isSupplies;
            genderSelect.disabled = isSupplies;
            
            if (isSupplies) {
                sizeSelect.value = '';
                genderSelect.value = '';
            }
        }

        // Initial check
        updateFilters();

        // Add event listener for type changes
        typeSelect.addEventListener('change', updateFilters);
    });
    </script>

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