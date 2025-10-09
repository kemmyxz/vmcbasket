<?php
require('admin/inc/config.php');
session_start();

// Prevent browser from caching the page
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies


if (!isset($_SESSION['student_no'])) {
    header("location: login.php");
    exit();
}

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



// Set number of items per page
$items_per_page = 16;

// Get current page number from URL parameter
$current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

$search = $_GET['search'] ?? '';

$check_fav_sql = "SELECT favorite FROM favorites WHERE user_id = ? AND product_id = ?";
$check_fav_stmt = $conn->prepare($check_fav_sql);
$check_fav_stmt->bind_param("ii", $user['id'], $row['id']);
$check_fav_stmt->execute();
$is_favorite = $check_fav_stmt->get_result()->fetch_assoc();
$is_favorite = $is_favorite ? $is_favorite['favorite'] : 0;



$items_per_page = 8;
$current_page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total count of uniforms
$count_sql = "SELECT COUNT(DISTINCT p.id) as total FROM products p 
              LEFT JOIN product_variants pv ON p.id = pv.product_id 
              WHERE p.type='Uniform'";

// Add filters to count query if they exist
$count_params = [];
$count_types = "";

if (!empty($year_level)) {
    $count_sql .= " AND p.tags LIKE ?";
    $count_params[] = "%$year_level%";
    $count_types .= "s";
}

if (!empty($gender)) {
    $count_sql .= " AND pv.gender = ?";
    $count_params[] = $gender;
    $count_types .= "s";
}

if (!empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $count_sql .= " AND (p.product_name LIKE ? OR p.tags LIKE ?)";
    $count_params[] = $search;
    $count_params[] = $search;
    $count_types .= "ss";
}

$count_stmt = $conn->prepare($count_sql);
if (!empty($count_params)) {
    $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$uniforms_total = $count_result->fetch_assoc()['total'];
$uniforms_total_pages = ceil($uniforms_total / $items_per_page);
?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket- Uniforms</title>
    <?php include 'links.php'; ?>
    <style>
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

        .fav-button.active {
            background: #e8261a;
            color: #fff;
        }

        .fav-button.active i {
            color: #fff;
        }

        /* Sidebar filter styling */
        aside h6 {
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        aside ul li {
            margin-bottom: 6px;
            font-size: 0.9rem;
        }

        .basket-button {
            font-size: 0.9rem;
            padding: 0.5rem 0.8rem;
        }

        .margin-top {
            margin-top: 80px;
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
                padding: 0.15rem 0.15rem;
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

            .filter-title {
                font-size: 1rem;
            }

            .filter-text {
                font-size: 0.85rem;
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

            footer {
                font-size: 1rem;
            }
        }

        @media (max-width: 575.98px) {
            .margin-top {
                margin-top: 50px;
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

            .basket-button {
                font-size: 0.85rem;
                padding: 0.35rem 0.35rem;
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
                <input type="text" class="form-control search-box" name="search" placeholder="Search products here..."
                    value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" oninput="handleSearch(event)">
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
                <form action="" method="GET" class="search-form" id="searchForm">
                    <input type="text" class="form-control search-box" name="search"
                        placeholder="Search products here..."
                        value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" oninput="handleSearch(event)">
                </form>
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
                <img src="admin/uploads/<?php echo htmlspecialchars($profilePic); ?>">
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

    <!--Content -->
    <div class="container-fluid p-lg-5 p-md-3 margin-top">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
            aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Shop</li>
                <li class="breadcrumb-item active" aria-current="page">Uniforms</li>
            </ol>
        </nav>
        <div class="row">
            <!-- Sidebar Filter -->
            <!-- Filter Sidebar (collapsible on md and below) -->
            <aside class="col-lg-3 col-md-4 mt-4 mb-4">
                <!-- Toggle button for md and below -->
                <button class="btn btn-outline-secondary d-md-none mb-3" type="button" data-bs-toggle="collapse"
                    data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                    <i class="bi bi-filter me-2"></i>FILTER
                </button>
                <!-- Collapsible filter content -->
                <div class="collapse d-md-block" id="filterCollapse">
                    <h4 class="fw-semibold mb-3 d-none d-md-block">
                        <i class="bi bi-filter me-2"></i>FILTER
                    </h4>

                    <!-- By Year-Level -->
                    <div class="mb-4 mt-3">
                        <h5 class="fw-semibold filter-title" style="color: #26387D">BY YEAR-LEVEL:</h5>
                        <form id="filterForm" method="GET">
                            <ul class="list-unstyled filter-text">
                                <h6 class="fw-semibold filter-title">Basic Education</h6>
                                <?php
                                // Get current filters from URL
                                $selected_year = $_GET['year_level'] ?? '';
                                $selected_gender = $_GET['gender'] ?? '';

                                // Define year levels
                                $basic_education = [
                                    'Pre School' => 'Pre School',
                                    'Elementary' => 'Elementary',
                                    'Junior High School' => 'Junior High School',
                                    'Senior High School' => 'Senior High School'
                                ];

                                $college_courses = [
                                    'BS Tourism Management' => 'BS Tourism Management',
                                    'BS Information System' => 'BS Information System',
                                    'BS Hotel and Restaurant Management' => 'BS Hotel and Restaurant Management',
                                    'BS Secondary Education' => 'BS Secondary Education',
                                    'BS Elementary Education' => 'BS Elementary Education',
                                    'Criminology' => 'Criminology'
                                ];

                                // Output Basic Education radio buttons
                                foreach ($basic_education as $value => $label) {
                                    $checked = ($selected_year === $value) ? 'checked' : '';
                                    echo "<li>
                                        <input type='radio' name='year_level' class='form-check-input me-2' 
                                        value='" . htmlspecialchars($value) . "' $checked 
                                        onchange='this.form.submit()'>" . htmlspecialchars($label) . "
                                    </li>";
                                }
                                ?>

                                <h6 class="fw-semibold filter-title mt-2">College</h6>
                                <?php
                                // Output College radio buttons
                                foreach ($college_courses as $value => $label) {
                                    $checked = ($selected_year === $value) ? 'checked' : '';
                                    echo "<li>
                                        <input type='radio' name='year_level' class='form-check-input me-2' 
                                        value='" . htmlspecialchars($value) . "' $checked 
                                        onchange='this.form.submit()'>" . htmlspecialchars($label) . "
                                    </li>";
                                }
                                ?>
                            </ul>

                            <!-- By Gender -->
                            <div class="mb-4">
                                <h5 class="fw-semibold filter-title" style="color: #26387D">By Gender:</h5>
                                <ul class="list-unstyled filter-text">
                                    <?php
                                    $genders = ['Female' => 'Female', 'Male' => 'Male', 'Unisex' => 'Unisex'];
                                    foreach ($genders as $value => $label) {
                                        $checked = ($selected_gender === $value) ? 'checked' : '';
                                        $sql_value = ($value === 'Unisex') ? "AND (pv.gender = 'Male' OR pv.gender = 'Female')" : "AND pv.gender = '$value'";
                                        echo "<li>
                                            <input type='radio' name='gender' class='form-check-input me-2' 
                                            value='" . htmlspecialchars($value) . "' $checked 
                                            onchange='this.form.submit()'>" . htmlspecialchars($label) . "
                                        </li>";
                                    }
                                    ?>
                                </ul>
                            </div>

                            <!-- Add current page to form if it exists in URL -->
                            <?php if (isset($_GET['page'])): ?>
                                <input type="hidden" name="page" value="<?php echo htmlspecialchars($_GET['page']); ?>">
                            <?php endif; ?>
                    </div>

                    <!-- Clear Filters Button -->
                    <button type="button" id="clearFilters" class="btn btn-outline-secondary btn-sm mb-3"
                        onclick="window.location.href='shop_uniforms.php'">
                        Clear Filters
                    </button>
                    </form>
                </div>
            </aside>
            <!--Header -->
            <section class="col-lg-9 col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                    <h2 class="text-start w-md-auto">
                        <span class="highlight-pink">Uniforms</span>
                    </h2>
                    <!-- Desktop: show p and pagination on the right -->
                    <div class="d-none d-sm-flex flex-column justify-content-center align-items-end ms-auto">
                        <?php if ($uniforms_total > 0): ?>
                            <p class="mb-2"><?php echo min($items_per_page, $result->num_rows); ?> out of
                                <?php echo $uniforms_total; ?> items shows</p>
                            <!-- Pagination -->
                            <nav aria-label="Page navigation">
                                <ul class="pagination custom-pagination justify-content-center">
                                    <!-- Previous page -->
                                    <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link"
                                            href="?page=<?php echo $current_page - 1; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?><?php echo isset($_GET['year_level']) ? '&year_level=' . htmlspecialchars($_GET['year_level']) : ''; ?><?php echo isset($_GET['gender']) ? '&gender=' . htmlspecialchars($_GET['gender']) : ''; ?>"
                                            aria-label="Previous">
                                            <span aria-hidden="true">&lt;</span>
                                        </a>
                                    </li>

                                    <!-- Page numbers -->
                                    <?php
                                    $start_page = max(1, min($current_page - 2, $uniforms_total_pages - 4));
                                    $end_page = min($uniforms_total_pages, max(5, $current_page + 2));

                                    for ($i = $start_page; $i <= $end_page; $i++):
                                        ?>
                                        <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                                            <a class="page-link"
                                                href="?page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?><?php echo isset($_GET['year_level']) ? '&year_level=' . htmlspecialchars($_GET['year_level']) : ''; ?><?php echo isset($_GET['gender']) ? '&gender=' . htmlspecialchars($_GET['gender']) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Next page -->
                                    <li
                                        class="page-item <?php echo ($current_page >= $uniforms_total_pages) ? 'disabled' : ''; ?>">
                                        <a class="page-link"
                                            href="?page=<?php echo $current_page + 1; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?><?php echo isset($_GET['year_level']) ? '&year_level=' . htmlspecialchars($_GET['year_level']) : ''; ?><?php echo isset($_GET['gender']) ? '&gender=' . htmlspecialchars($_GET['gender']) : ''; ?>"
                                            aria-label="Next">
                                            <span aria-hidden="true">&gt;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>

                    <!-- Mobile pagination -->
                    <div class="d-block d-sm-none mb-4">
                        <?php if ($uniforms_total > 0): ?>
                            <p class="mb-2"><?php
                            $items_shown = $result->num_rows; 
                            echo $items_shown; ?> out of <?php echo $uniforms_total; ?> items shown</p>
                            <nav aria-label="Page navigation">
                                <ul class="pagination custom-pagination justify-content-center">
                                    <!-- Previous page -->
                                    <li class="page-item <?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link"
                                            href="?page=<?php echo $current_page - 1; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?><?php echo isset($_GET['year_level']) ? '&year_level=' . htmlspecialchars($_GET['year_level']) : ''; ?><?php echo isset($_GET['gender']) ? '&gender=' . htmlspecialchars($_GET['gender']) : ''; ?>"
                                            aria-label="Previous">
                                            <span aria-hidden="true">&lt;</span>
                                        </a>
                                    </li>

                                    <!-- Page numbers -->
                                    <?php
                                    $start_page = max(1, min($current_page - 2, $uniforms_total_pages - 4));
                                    $end_page = min($uniforms_total_pages, max(5, $current_page + 2));

                                    for ($i = $start_page; $i <= $end_page; $i++):
                                        ?>
                                        <li class="page-item <?php echo ($current_page == $i) ? 'active' : ''; ?>">
                                            <a class="page-link"
                                                href="?page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?><?php echo isset($_GET['year_level']) ? '&year_level=' . htmlspecialchars($_GET['year_level']) : ''; ?><?php echo isset($_GET['gender']) ? '&gender=' . htmlspecialchars($_GET['gender']) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Next page -->
                                    <li
                                        class="page-item <?php echo ($current_page >= $uniforms_total_pages) ? 'disabled' : ''; ?>">
                                        <a class="page-link"
                                            href="?page=<?php echo $current_page + 1; ?><?php echo isset($_GET['search']) ? '&search=' . htmlspecialchars($_GET['search']) : ''; ?><?php echo isset($_GET['year_level']) ? '&year_level=' . htmlspecialchars($_GET['year_level']) : ''; ?><?php echo isset($_GET['gender']) ? '&gender=' . htmlspecialchars($_GET['gender']) : ''; ?>"
                                            aria-label="Next">
                                            <span aria-hidden="true">&gt;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Mobile: show p and pagination below the heading -->
                <div class="d-block d-sm-none mb-4">
                    <p class="mb-2">8 out of 100 items shows</p>
                    <nav aria-label="Page navigation">
                        <ul class="pagination custom-pagination justify-content-center">
                            <li class="page-item">
                                <a class="page-link" href="#" aria-label="Previous">
                                    <span aria-hidden="true">&lt;</span>
                                </a>
                            </li>
                            <li class="page-item"><a class="page-link" href="#">1</a></li>
                            <li class="page-item active"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#">4</a></li>
                            <li class="page-item"><a class="page-link" href="#">5</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#" aria-label="Next">
                                    <span aria-hidden="true">&gt;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <!-- UNIFORMS SECTION -->
                <div class="row g-4 mb-5">
                    <?php
                    $year_level = isset($_GET['year_level']) ? $_GET['year_level'] : '';
                    $gender = isset($_GET['gender']) ? $_GET['gender'] : '';

                    $uniforms_sql = "SELECT COUNT(*) as total FROM products WHERE type='Uniform'";
                    $uniforms_result = $conn->query($uniforms_sql);
                    $uniforms_total = $uniforms_result->fetch_assoc()['total'];
                    $uniforms_total_pages = ceil($uniforms_total / $items_per_page);

                    $uniforms_sql = "SELECT p.*, 
                                    GROUP_CONCAT(DISTINCT pv.gender) as genders,
                                    GROUP_CONCAT(DISTINCT pv.size) as sizes,
                                    CASE WHEN f.favorite = 1 THEN true ELSE false END as is_favorited
                                    FROM products p 
                                    LEFT JOIN product_variants pv ON p.id = pv.product_id 
                                    LEFT JOIN favorites f ON p.id = f.product_id AND f.user_id = ?
                                    WHERE p.type='Uniform'";

                    $params = [$user['id']];
                    $types = "i";

                    // Add year level filter
                    if (!empty($year_level)) {
                        $uniforms_sql .= " AND p.tags LIKE ?";
                        $params[] = "%$year_level%";
                        $types .= "s";
                    }

                    // Add gender filter
                    if (!empty($gender)) {
                        if ($gender === 'Unisex') {
                            $uniforms_sql .= " AND (pv.gender IN ('Male', 'Female') OR pv.gender = 'Unisex')";
                        } else {
                            $uniforms_sql .= " AND (pv.gender = ? OR pv.gender = 'Unisex')";
                            $params[] = $gender;
                            $types .= "s";
                        }
                    }


                    if (!empty($_GET['search'])) {
                        $search = '%' . $_GET['search'] . '%';
                        $uniforms_sql .= " AND (p.product_name LIKE ? OR p.tags LIKE ?)";
                        $params[] = $search;
                        $params[] = $search;
                        $types .= "ss";
                    }



                    // Add GROUP BY, LIMIT and OFFSET after all WHERE conditions
                    $uniforms_sql .= " GROUP BY p.id LIMIT ? OFFSET ?";
                    $params[] = $items_per_page;
                    $params[] = $offset;
                    $types .= "ii";

                    // Prepare and execute the statement
                    $stmt = $conn->prepare($uniforms_sql);
                    $stmt->bind_param($types, ...$params);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                            ?>
                            <div class="col-md-4 col-lg-3 d-flex">
                                <div class="product-card d-flex flex-column w-100" style="height:100%;" onclick="location.href='product_details.php?id=<?= $row['id'] ?>'">
                                    <img src="admin/<?= htmlspecialchars($row['image']) ?>"
                                        alt="<?= htmlspecialchars($row['product_name']) ?>">
                                    <div class="product-info d-flex flex-column h-100">
                                        <h3><?= htmlspecialchars($row['product_name']) ?></h3>
                                        <?php if (!empty($row['genders'])): ?>
                                            <p class="mb-1">
                                                <?php
                                                $genders = explode(',', $row['genders']);
                                                $genders = array_filter($genders); // Remove empty values
                                                $genders = array_unique($genders); // Remove duplicates

                                                if (count($genders) > 0) {
                                                    // Sort genders with Unisex last if present
                                                    usort($genders, function ($a, $b) {
                                                        if ($a === 'Unisex')
                                                            return 1;
                                                        if ($b === 'Unisex')
                                                            return -1;
                                                        return strcmp($a, $b);
                                                    });

                                                    // Display genders with proper formatting
                                                    echo implode('  ', array_map('htmlspecialchars', $genders));
                                                }
                                                ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if (!empty($_GET['size'])): ?>
                                            <p class="stock-info <?= $row['stock_quantity'] == 0 ? 'out-of-stock' : '' ?>">
                                                Stock: <?= (int) $row['stock_quantity'] ?>
                                            </p>
                                        <?php endif; ?>


                                        <div class="badges" style="min-height:40px; display:flex; flex-wrap:wrap; align-items:flex-start;">
                                            <?php
                                            // Display tags if they exist
                                            $tagCount = 0;
                                            if (!empty($row['tags'])) {
                                                $tags = explode(',', $row['tags']); // Split tags string into array
                                                foreach ($tags as $tag) {
                                                    $tag = trim($tag); // Remove any whitespace
                                                    if (!empty($tag)) {
                                                        $tagClass = strtolower(str_replace(' ', '_', $tag)) . '_badge';
                                                        echo "<span class='badge {$tagClass} me-1 mb-1'>" . htmlspecialchars($tag) . "</span> ";
                                                        $tagCount++;
                                                    }
                                                }
                                            }

                                            // Display product type
                                            if (!empty($row['type'])) {
                                                echo '<span class="badge uniform_badge me-1 mb-1">' . htmlspecialchars($row['type']) . '</span>';
                                                $tagCount++;
                                            }

                                            /*Add empty badges to pad height if tagCount < 3 (adjust as needed)
                                            $minTags = 3;
                                            if ($tagCount < $minTags) {
                                                for ($i = $tagCount; $i < $minTags; $i++) {
                                                    echo "<span class='badge invisible me-1 mb-1'>&nbsp;</span> ";
                                                }
                                            }*/
                                            ?>
                                        </div>

                                        <!-- Spacer to push price to bottom if few tags -->
                                        <div style="flex-grow:1;"></div>
                                        
                                        <div class="price">
                                            ₱<?= number_format($row['price'], 2) ?>
                                        </div>
                                        <div class="rating">
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

                                            echo '<span class="ms-1">' . number_format($rating, 1) . '</span>';
                                            ?>
                                        </div>

                                        <!-- Icon Buttons (Heart & Cart) -->
                                        <div class="icon-buttons">
                                            <button class="fav-button <?= $row['is_favorited'] ? 'active' : '' ?>"
                                                onclick="event.stopPropagation(); toggleFavorite(this, <?= $row['id'] ?>)">
                                                <i class="bi <?= $row['is_favorited'] ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                                            </button>
                                            <button class="basket-button"
                                                onclick="event.stopPropagation(); addToBasket(<?= $row['id'] ?>, '<?= htmlspecialchars($row['product_name']) ?>', <?= $row['price'] ?>, '<?= htmlspecialchars($row['image']) ?>')">
                                                <i class="bi bi-basket me-lg-3 me-sm-0 me-md-0"></i>
                                                Add to Basket
                                            </button>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; else: ?>
                        <p>No uniforms found.</p>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>


    <!-- Footer and chat-->
    <?php include 'footer.php'; ?>
    <?php include 'chat.php'; ?>

    <!-- Collapse Search for small device Script -->
    <script>
        // Define searchTimeout at the top level
        let searchTimeout;

        // Define handleSearch function before it's used
        function handleSearch(event) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchValue = event.target.value.trim();

                // Get current URL and parameters
                const url = new URL(window.location.href);
                const params = new URLSearchParams(url.search);

                if (searchValue) {
                    params.set('search', searchValue);
                } else {
                    params.delete('search');
                }

                // Preserve other filter parameters
                if (params.toString()) {
                    window.location.href = `${url.pathname}?${params.toString()}`;
                } else {
                    window.location.href = url.pathname;
                }
            }, 500);
        }

        // Mobile search toggle functionality
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

                document.addEventListener('click', function (e) {
                    if (!searchBar.classList.contains('d-none') && !searchBar.contains(e.target) && e.target !== toggleBtn) {
                        searchBar.classList.add('d-none');
                    }
                });
            }

            // Initialize desktop search
            const desktopSearch = document.querySelector('.d-none.d-sm-block .search-box');
            if (desktopSearch) {
                desktopSearch.addEventListener('input', handleSearch);
            }
        });

        // Fixed addToBasket function
        function addToBasket(productId, productName, price, image) {
           window.location.href = `product_details.php?id=${productId}`;
        }

        // Bootstrap 5.3.3 alert for favorites with icons
        function showFavoriteAlert(message, type = 'success') {
            // Remove any existing alert
            const existingAlert = document.getElementById('favoriteAlert');
            if (existingAlert) existingAlert.remove();

            // Choose icon based on type
            let iconHtml = '';
            switch (type) {
            case 'success':
                iconHtml = '<i class="bi bi-heart-fill text-danger me-2"></i>';
                break;
            case 'warning':
                iconHtml = '<i class="bi bi-heart text-danger me-2"></i>';
                break;
            case 'danger':
                iconHtml = '<i class="bi bi-x-circle-fill text-danger me-2"></i>';
                break;
            default:
                iconHtml = '';
            }

            // Create alert element
            const alertDiv = document.createElement('div');
            alertDiv.id = 'favoriteAlert';
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 me-3 mt-3`;
            alertDiv.style.zIndex = '9999';
            alertDiv.style.minWidth = '250px';
            alertDiv.innerHTML = `
            ${iconHtml}${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            document.body.appendChild(alertDiv);

            // Auto-dismiss after 2 seconds
            setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alertDiv);
            bsAlert.close();
            }, 2000);
        }

        // Toggle favorite function
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
                showFavoriteAlert('Added to favorites!', 'success');
                } else {
                heartIcon.classList.remove('bi-heart-fill');
                heartIcon.classList.add('bi-heart');
                button.classList.remove('active');
                showFavoriteAlert('Removed from favorites.', 'warning');
                }
            } else {
                showFavoriteAlert(data.message || 'Failed to update favorite', 'danger');
            }
            })
            .catch(error => {
            console.error('Error:', error);
            showFavoriteAlert('An error occurred while updating favorite', 'danger');
            });
        }
    </script>
</body>

</html>