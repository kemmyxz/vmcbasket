<?php
require 'inc/config.php'; // Database connection

// Fetch the product details if product ID is passed
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];  // Get product ID passed via the URL

    // Fetch product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if product exists
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Product not found!";
        exit;
    }

    // Fetch images if applicable
    $product_images = json_decode($product['images'], true); // Assuming images are stored as a JSON array
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $dr_number = $_POST['dr_number'];
    $price = $_POST['price'];
    $type = $_POST['type'];
    $max_quantity = $_POST['max_quantity'];
    $date_modified = date("Y-m-d");
    $tags = isset($_POST['tags']) ? json_encode($_POST['tags']) : null;

    // Handle Image Upload
    $image_path = "";
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_tmp = $_FILES["product_image"]["tmp_name"];
        $file_extension = strtolower(pathinfo($_FILES["product_image"]["name"], PATHINFO_EXTENSION));
        $file_name = time() . "_" . uniqid() . "." . $file_extension;
        $image_path = $target_dir . $file_name;

        // Validate file type
       // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_extension, $allowed_types)) {
            header("Location: prod.php?error=filetype");
            exit;
        }

        if (!move_uploaded_file($file_tmp, $image_path)) {
            header("Location: prod.php?error=uploadfail");
            exit;
        }
    }

    // Start transaction
    $conn->begin_transaction();

    // Clean and store tags as plain text
    if (isset($_POST['tags']) && is_array($_POST['tags'])) {
        // Clean tags and join with commas
        $cleanTags = array_map(function ($tag) {
            // Remove any special characters and trim
            return trim(preg_replace('/[^a-zA-Z0-9\s]/', ' ', $tag));
        }, $_POST['tags']);

        // Convert array to comma-separated string
        $tags = implode(', ', array_filter($cleanTags));
    } else {
        $tags = '';
    }

    try {
        // Update your INSERT statement
        $stmt = $conn->prepare("INSERT INTO products (product_name, dr_number, price, type, date_modified, image, tags, max_quantity) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssdssssi",
            $product_name,
            $dr_number,
            $price,
            $type,
            $date_modified,
            $image_path,
            $tags,  // Now storing as plain text
            $max_quantity
        );
        $stmt->execute();

        $product_id = $conn->insert_id;

        if ($type === '2') { // Supplies
            // Insert single stock entry for supplies
            $total_stock = $_POST['stocks']['total'];
            $stmt = $conn->prepare("INSERT INTO product_variants (product_id, size, gender, stock) VALUES (?, '', '', ?)");
            $stmt->bind_param("ii", $product_id, $total_stock);
            $stmt->execute();
        } else { // Uniform
            // Insert stock for each size-gender combination
            $stocks = $_POST['stocks'];
            $stmt = $conn->prepare("INSERT INTO product_variants (product_id, size, gender, stock) VALUES (?, ?, ?, ?)");

            foreach ($stocks as $size => $genderStocks) {
                foreach ($genderStocks as $gender => $stock) {
                    if ($stock > 0) { // Only insert if stock is greater than 0
                        $stmt->bind_param("issi", $product_id, $size, $gender, $stock);
                        $stmt->execute();
                    }
                }
            }
        }

        $conn->commit();
        header("Location: prod.php?added=success");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}



// Search and pagination logic
$search = isset($_GET['search']) ? $_GET['search'] : '';
$limit = 50;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT p.*, pv.id as variant_id,
        GROUP_CONCAT(
            CONCAT(pv.size, ' (', pv.gender, '): ', pv.stock, ' pcs') 
            ORDER BY pv.size, pv.gender
            SEPARATOR '<br>'
        ) as size_variants,
        SUM(pv.stock) as total_stock,
        CASE 
            WHEN p.type = 'Uniform' THEN 'Uniform'
            WHEN p.type = 'Supplies' THEN 'Supplies'
            ELSE 'Unknown'
        END as type_name,
        CASE
            WHEN p.type = 'Uniform' AND EXISTS (
                SELECT 1 FROM product_variants pv2 
                WHERE pv2.product_id = p.id AND pv2.stock <= 5
            ) THEN 'Out of Stock'
            WHEN p.type = 'Supplies' AND (
                SELECT SUM(pv2.stock) 
                FROM product_variants pv2 
                WHERE pv2.product_id = p.id
            ) <= 5 THEN 'Out of Stock'
            ELSE 'In Stock'
        END as stock_status
        FROM products p
        LEFT JOIN product_variants pv ON p.id = pv.product_id
        WHERE p.product_name LIKE ?
        GROUP BY p.id
        ORDER BY p.id DESC, p.date_modified DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$search_term = "%$search%";
$stmt->bind_param("sii", $search_term, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$total_records_query = "SELECT COUNT(*) AS total FROM products WHERE product_name LIKE '%$search%'";
$total_records_result = $conn->query($total_records_query);
$total_records = $total_records_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);


$total_products_sql = "SELECT COUNT(*) as total FROM products";
$total_products_result = $conn->query($total_products_sql);
$total_products = $total_products_result->fetch_assoc()['total'];
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket - Admin/Products</title>
    <link rel="icon" href="images/vmc_basket_logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Kulim+Park:ital,wght@0,200;0,300;0,400;0,600;0,700;1,200;1,300;1,400;1,600;1,700&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .variant-options {
            max-height: 200px;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        }

        .form-check {
            margin-bottom: 8px;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Toggle Button -->
            <!-- Top Navbar (visible only on small devices) -->
            <nav class="navbar navbar-light bg-light d-md-none">
                <div class="container-fluid d-flex justify-content-between align-items-center">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu"
                        aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <img src="images/vmc_basket_logo.png" alt="VMC Logo" class="vmc-logo img-fluid">
                </div>
            </nav>

            <!-- Sidebar -->
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse">
                
                <div class="text-center py-3 d-none d-md-block">
                    <img src="images/vmc_basket_logo.png" alt="VMC Logo" class="vmc-logo img-fluid">
                </div>

                <ul class="nav flex-column px-2 mb-3 mt-4 mt-md-0">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link">
                            <i class="bi bi-house-door me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="orders.php" class="nav-link">
                            <i class="bi bi-bag-check me-2"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="prod.php" class="nav-link active">
                            <i class="bi bi-box-seam me-2"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="cus.php" class="nav-link">
                            <i class="bi bi-people me-2"></i> Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="chat.php" class="nav-link">
                            <i class="bi bi-chat-dots me-2"></i> Chat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="ratings.php" class="nav-link">
                            <i class="bi bi-list-stars me-2"></i> Ratings & Reviews
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="accounting.php" class="nav-link">
                            <i class="bi bi-receipt me-2"></i> Receipt Form
                        </a>
                    </li>
                    <!-- Logout for small screens (visible only on xs/sm) -->
                    <li class="nav-item d-block d-md-none">
                        <a href="logout.php" class="nav-link text-danger fw-semibold">
                            <i class="bi bi-box-arrow-right me-2"></i> Log Out
                        </a>
                    </li>
                </ul>
                <!-- Logout at the bottom for md/lg screens -->
                <div class="position-absolute w-100 d-none d-md-block" style="bottom: 30px; left: 0;">
                    <ul class="nav flex-column px-2">
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link text-danger fw-semibold">
                                <i class="bi bi-box-arrow-right me-2"></i> Log Out
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!--Alert Message-->
            <?php
                if (isset($_GET['added']) && $_GET['added'] == 'success') {
                    echo '
                    <div class="alert alert-success alert-dismissible fade show position-fixed m-3 p-3 d-flex align-items-center"
                        role="alert"
                        id="successAlert"
                        style="width:350px; top: 0; right: 0; z-index: 1055;">
                        <i class="bi bi-check-circle-fill me-2" style="font-size: 1.3rem;"></i>
                        Product added successfully!
                        <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
                } elseif (isset($_GET['error'])) {
                    $message = '';
                    switch ($_GET['error']) {
                        case 'filetype':
                            $message = 'Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.';
                            break;
                        case 'uploadfail':
                            $message = 'Failed to upload image.';
                            break;
                        default:
                            $message = 'An unknown error occurred.';
                    }

                    echo '
                    <div class="alert alert-danger alert-dismissible show position-fixed m-3 p-3 d-flex align-items-center" 
                        role="alert" 
                        id="statusAlert"
                        style="width:400px; top: 0; right: 0; z-index: 1055;">
                        <i class="bi bi-x-circle-fill me-2" style="font-size: 1.3rem;"></i>
                        ' . htmlspecialchars($message) . '
                    </div>';
                }
            ?>
            <div id="alertContainer" class="position-fixed top-0 end-0 mt-3 me-3" style="z-index: 1055; width: 90%; max-width: 600px;"></div>

            <!-- Delete Confirmation Modal -->
            <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h4 class="modal-title" id="deleteConfirmLabel">Confirm Delete</h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the selected product(s)?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
                </div>
            </div>
            </div>

            <!-- Title Page and Search -->
            <main class="col-md-9 ms-sm-auto col-lg-10 content p-3">

                <div class="d-flex justify-content-end mb-5">
                    <div class="search-container">
                        <input type="text" class="form-control" id="productSearch" placeholder="Search products...">
                        <button type="button"><i class="bi bi-search"></i></button>
                    </div>
                </div>

                <div class="mt-2 d-flex flex-row align-items-center">
                    <h2 class="mb-0">Product</h2>
                </div>

                <!--Total Products, Add Products Button, and Modal-->
                <div class="d-flex justify-content-between align-items-center mb-2 mt-3">
                    <div>
                        <strong>Total Products: <?= number_format($total_products); ?></strong>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <!-- Bulk Delete Button (hidden by default) -->
                        <div id="bulkDeleteContainer" style="display:none;">
                            <button id="bulkDeleteBtn" class="btn btn-danger">
                                <i class="bi bi-trash"></i> Delete Selected
                            </button>
                        </div>
                        <!-- BUTTON FOR ADD NEW PRODUCTS FORM -->
                        <button type="button" class="admin-btn" data-bs-toggle="modal"
                            data-bs-target="#addProductModal">
                            Add <i class="bi bi-plus-circle ms-1"></i>
                        </button>
                    </div>
                </div>



                <!-- ADD NEW PRODUCTS FORM -->
                <div class="modal fade" id="addProductModal" data-bs-backdrop="static" data-bs-keyboard="false"
                    tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <!-- Modal Header -->
                            <div class="modal-header">
                                <h5 class="d-flex align-items-center">
                                    <img src="./images/add-product.png" alt="add user icon"
                                        style="margin-right: 5px; height:50px;">Add New Product
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>

                            <!-- Modal Body -->
                            <div class="modal-body">
                                <!-- Product Details Section -->
                                <h5 class="title-text mb-3">Product Details</h5>
                                <form action="" method="POST" enctype="multipart/form-data">

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Type</label>
                                            <select class="form-select" name="type" id="productType" required>
                                                <option value="">Choose Type</option>
                                                <option value="1">Uniform</option>
                                                <option value="2">Supplies</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Product Name</label>
                                            <input type="text" class="form-control" name="product_name"
                                                placeholder="Enter product name" required>
                                        </div>

                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Delivery Receipt Number</label>
                                            <input type="text" class="form-control" name="dr_number"
                                                placeholder="Ex. DR1234567" required>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Price</label>
                                            <input type="number" class="form-control" name="price"
                                                placeholder="Enter price" required>
                                        </div>

                                        <!-- Uniform Tags Badges Section -->
                                        <div class="col-12 mb-3 mt-2" id="uniformTagsSection" style="display:none;">
                                            <label class="form-label">Year-level Tags</label>
                                            <div class="d-flex flex-wrap gap-2">
                                                <?php
                                                $uniformTags = [
                                                    ['id' => 'tagPreschool', 'value' => 'Pre-school', 'badge' => 'preschool_badge', 'label' => 'Pre-school', 'badgeId' => 'badgePreschool'],
                                                    ['id' => 'tagKindergarten', 'value' => 'Kindergarten', 'badge' => 'kinder_badge', 'label' => 'Kindergarten', 'badgeId' => 'badgeKindergarten'],
                                                    ['id' => 'tagElementary', 'value' => 'Elementary', 'badge' => 'elementary_badge', 'label' => 'Elementary', 'badgeId' => 'badgeElem'],
                                                    ['id' => 'tagJuniorHigh', 'value' => 'Junior High School', 'badge' => 'jhs_badge', 'label' => 'Junior High School', 'badgeId' => 'badgeJHS'],
                                                    ['id' => 'tagSeniorHigh', 'value' => 'Senior High School', 'badge' => 'shs_badge', 'label' => 'Senior High School', 'badgeId' => 'badgeSHS'],
                                                    ['id' => 'tagTourism', 'value' => 'BS Tourism Management', 'badge' => 'bstm_badge', 'label' => 'BS Tourism Management', 'badgeId' => 'badgeTM'],
                                                    ['id' => 'tagBSIS', 'value' => 'BS Information System', 'badge' => 'bsis_badge', 'label' => 'BS Information System', 'badgeId' => 'badgeBSIS'],
                                                    ['id' => 'tagBSBA', 'value' => 'BS Business Administration', 'badge' => 'bsba_badge', 'label' => 'BS Business Administration', 'badgeId' => 'badgeBSBA'],
                                                    ['id' => 'tagBHRM', 'value' => 'BS Hotel and Restaurant Management', 'badge' => 'bhrm_badge', 'label' => 'BS Hotel and Restaurant Management', 'badgeId' => 'badgeBHRM'],
                                                    ['id' => 'tagSecondary', 'value' => 'BS Secondary Education', 'badge' => 'secondary_badge', 'label' => 'BS Secondary Education', 'badgeId' => 'badgeSecondary'],
                                                    ['id' => 'tagEduc', 'value' => 'BS Elementary Education', 'badge' => 'educ_badge', 'label' => 'BS Elementary Education', 'badgeId' => 'badgeEduc'],
                                                    ['id' => 'tagCrim', 'value' => 'Criminology', 'badge' => 'crim_badge', 'label' => 'Criminology', 'badgeId' => 'badgeCriminology'],
                                                    ['id' => 'tagMerch', 'value' => 'School-Merchandise', 'badge' => 'merch_badge', 'label' => 'School-Merchandise', 'badgeId' => 'badgeMerch'],
                                                ];
                                                foreach ($uniformTags as $tag) {
                                                    ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input styled-checkbox uniform-tag-checkbox"
                                                            type="checkbox" id="<?= $tag['id'] ?>" name="tags[]"
                                                            value="<?= $tag['value'] ?>">
                                                        <label class="form-check-label" for="<?= $tag['id'] ?>">
                                                            <span class="badge <?= $tag['badge'] ?> me-2"
                                                                id="<?= $tag['badgeId'] ?>"><?= $tag['label'] ?></span>
                                                        </label>
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        </div>

                                        <!-- Supplies Tags Badges Section (Only one can be selected) -->
                                        <div class="col-12 mb-3 mt-2" id="suppliesTagsSection" style="display:none;">
                                            <label class="form-label">School Supplies Tags</label>
                                            <div class="d-flex flex-wrap gap-2">
                                                <?php
                                                $suppliesTags = [
                                                    ['id' => 'tagWriting', 'value' => 'Writing Tools', 'badge' => 'writing_badge', 'label' => 'Writing Tools', 'badgeId' => 'badgeWriting'],
                                                    ['id' => 'tagPaper', 'value' => 'Paper Products', 'badge' => 'paper_badge', 'label' => 'Paper Products', 'badgeId' => 'badgePaper'],
                                                    ['id' => 'tagArt', 'value' => 'Art Supplies', 'badge' => 'art_badge', 'label' => 'Art Supplies', 'badgeId' => 'badgeArt'],
                                                    ['id' => 'tagMerch', 'value' => 'School-Merchandise', 'badge' => 'merch_badge', 'label' => 'School-Merchandise', 'badgeId' => 'badgeMerch']
                                                ];
                                                foreach ($suppliesTags as $tag) {
                                                    ?>
                                                    <div class="form-check">
                                                        <input
                                                            class="form-check-input styled-checkbox supplies-tag-checkbox"
                                                            type="checkbox" id="<?= $tag['id'] ?>" name="tags[]"
                                                            value="<?= $tag['value'] ?>">
                                                        <label class="form-check-label" for="<?= $tag['id'] ?>">
                                                            <span class="badge <?= $tag['badge'] ?> me-2"
                                                                id="<?= $tag['badgeId'] ?>"><?= $tag['label'] ?></span>
                                                        </label>
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                        </div>

                                        <!-- Sizes Selection -->
                                        <div class="col-12 mb-3 mt-2">
                                            <label class="form-label">Sizes</label>
                                            <div class="row" id="sizesContainer">
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input styled-checkbox" type="checkbox"
                                                            name="sizes[]" value="XS" id="sizeXS">
                                                        <label class="form-check-label" for="sizeXS">XS</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input styled-checkbox" type="checkbox"
                                                            name="sizes[]" value="Small" id="sizeS">
                                                        <label class="form-check-label" for="sizeS">Small</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input styled-checkbox" type="checkbox"
                                                            name="sizes[]" value="Medium" id="sizeM">
                                                        <label class="form-check-label" for="sizeM">Medium</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input styled-checkbox" type="checkbox"
                                                            name="sizes[]" value="Large" id="sizeL">
                                                        <label class="form-check-label" for="sizeL">Large</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input styled-checkbox" type="checkbox"
                                                            name="sizes[]" value="XL" id="sizeXL">
                                                        <label class="form-check-label" for="sizeXL">XL</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input styled-checkbox" type="checkbox"
                                                            name="sizes[]" value="2XL" id="size2XL">
                                                        <label class="form-check-label" for="size2XL">2XL</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Gender Selection -->
                                        <div class="col-12 mb-3 mt-2">
                                            <label class="form-label">Gender</label>
                                            <div class="row" id="gendersContainer">
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input styled-checkbox" type="radio"
                                                            name="genders[]" value="Male" id="genderMale">
                                                        <label class="form-check-label" for="genderMale">Male</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input styled-checkbox" type="radio"
                                                            name="genders[]" value="Female" id="genderFemale">
                                                        <label class="form-check-label" for="genderFemale">Female</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input styled-checkbox" type="radio"
                                                            name="genders[]" value="Unisex" id="genderUnisex">
                                                        <label class="form-check-label" for="genderUnisex">Unisex</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Stocks -->
                                        <div class="col-12 mt-2">
                                            <label class="form-label" id="stocksLabel">Stocks for each Sizes and
                                                Gender</label>
                                            <div class="row" id="stocksContainer">
                                                <!-- Stock inputs will be dynamically added here -->
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mt-2">
                                        <label class="form-label">Maximum Quantity to be Sold</label>
                                        <input type="number" class="form-control" name="max_quantity"
                                            placeholder="Ex. 5 pcs" required>
                                    </div>
                                    <!-- Image Upload Section -->
                                    <hr>
                                    <h5 class="title-text mt-3 mb-3">Add Images</h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <label class="form-label">Picture</label>
                                            <input type="file" class="form-control" name="product_image" required>

                                        </div>
                                    </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-danger"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn custom-navy-btn">Add Product</button>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- PRODUCTS TABLE -->
                <div class="table-responsive">
                    <table class="table table-container">
                        <thead class="thead">
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAllProducts" title="Select All"
                                        class="custom-checkbox">
                                </th>
                                <th>#</th>
                                <th>Image</th>
                                <th>Order Details</th>
                                <th>Price</th>
                                <th>Stock</th>

                                <th class="align-middle text-center">
                                    <div class="dropdown">
                                        <button class="btn p-0 m-0 align-baseline table-dropdown dropdown-toggle"
                                            type="button" id="tagsDropdown" data-bs-toggle="dropdown"
                                            aria-expanded="false" style="text-decoration:none;">
                                            Types
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="tagsDropdown">
                                            <li><a class="dropdown-item filter-type" href="#" data-type="all">All</a>
                                            </li>
                                            <li><a class="dropdown-item filter-type" href="#"
                                                    data-type="Uniform">Uniform</a></li>
                                            <li><a class="dropdown-item filter-type" href="#"
                                                    data-type="Supplies">Supplies</a></li>
                                        </ul>
                                    </div>
                                </th>
                                <th class="align-middle text-center">Restock History</th>
                                <th class="align-middle text-center">
                                    <div class="dropdown">
                                        <button class="btn p-0 m-0 align-baseline table-dropdown dropdown-toggle"
                                            type="button" id="stocksStatusDropdown" data-bs-toggle="dropdown"
                                            aria-expanded="false" style="text-decoration:none;">
                                            Status
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="stocksStatusDropdown">
                                            <li><a class="dropdown-item filter-status" href="#"
                                                    data-status="all">All</a></li>
                                            <li><a class="dropdown-item filter-status" href="#"
                                                    data-status="in-stock">In Stock</a></li>
                                            <li><a class="dropdown-item filter-status" href="#"
                                                    data-status="out-of-stock">Out of Stock</a></li>
                                        </ul>
                                    </div>
                                </th>
                                <th class="align-middle text-start">Action</th>
                            </tr>
                        </thead>
                        <!--table-body-->
                        <tbody class="image-table-body text-center align-middle">
                            <?php if ($result->num_rows > 0): ?>
                                <?php $count = $offset + 1;
                                while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="custom-checkbox product-checkbox"
                                                value="<?= $row['id']; ?>">
                                        </td>
                                        <td><?= $count++; ?></td>
                                        <td>
                                            <img src="<?= $row['image'] ?: 'default.png'; ?>" class="product-img"
                                                alt="Product Image" name="product_image">
                                        </td>
                                        <td>
                                            <strong><?= $row['product_name']; ?></strong><br>
                                            <small>D.R. No: <?= $row['dr_number']; ?></small>
                                        </td>
                                        <td>₱<?= number_format($row['price'], 2); ?></td>

                                        <td>
                                            <?php
                                            // Only show size variants if there is more than one variant (i.e., Uniforms with sizes/genders)
                                            if (!empty($row['size_variants']) && strpos($row['size_variants'], '<br>') !== false) {
                                                echo $row['size_variants'] . '<br>';
                                                echo '<strong>Total: ' . $row['total_stock'] . ' pcs</strong>';
                                            } else {
                                                // For supplies or products with no size/gender variants
                                                echo '<strong>Total: ' . $row['total_stock'] . ' pcs</strong>';
                                            }
                                            ?>
                                        </td>


                                        <td>
                                            <?php if ($row['type_name'] === 'Uniform'): ?>
                                                <span class="badge uniform_badge"><?= htmlspecialchars($row['type_name']); ?></span>
                                            <?php else: ?>
                                                <span class="badge supplies_badge"><?= htmlspecialchars($row['type_name']); ?></span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php
                                            // Get the latest restock history for this product
                                            $restock_sql = "SELECT updated_by, restock_date 
                                                            FROM restock_history 
                                                            WHERE product_id = ? 
                                                            ORDER BY restock_date DESC 
                                                            LIMIT 1";
                                            $restock_stmt = $conn->prepare($restock_sql);
                                            $restock_stmt->bind_param("i", $row['id']);
                                            $restock_stmt->execute();
                                            $restock_result = $restock_stmt->get_result();

                                            if ($restock = $restock_result->fetch_assoc()) {
                                                echo "Restocked by: " . htmlspecialchars($restock['updated_by']) . "<br>";
                                                echo "<small class='text-muted'>" . date('M d, Y', strtotime($restock['restock_date'])) . "</small>";
                                            } else {
                                                echo "<span class='text-muted'>No restock history</span>";
                                            }
                                            ?>
                                        </td>

                                        <td>
                                            <?php if ($row['stock_status'] === 'In Stock'): ?>
                                                <span class="status active">In Stock</span>
                                            <?php else: ?>
                                                <span class="status disabled">Out of Stock</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-link p-0 m-0" type="button"
                                                    id="actionDropdown<?= $row['id']; ?>" data-bs-toggle="dropdown"
                                                    aria-expanded="false" style="font-size: 1.5rem; color: #333;">
                                                    <i class="bi bi-three-dots-vertical fs-5"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="actionDropdown<?= $row['id']; ?>">
                                                    <li>
                                                        <!--View Button -->
                                                        <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#productDetailsModal" data-id="<?= $row['id']; ?>"
                                                            data-variant_id="<?= $row['variant_id']; ?>"
                                                            data-product_name="<?= htmlspecialchars($row['product_name']); ?>"
                                                            data-drnumber="<?= htmlspecialchars($row['dr_number']); ?>"
                                                            data-price="<?= $row['price']; ?>"
                                                            data-total_stock="<?= $row['total_stock']; ?>"
                                                            data-variants="<?= htmlspecialchars($row['size_variants']); ?>"
                                                            data-type="<?= $row['type']; ?>"
                                                            data-image="<?= htmlspecialchars($row['image']); ?>">
                                                            <i class="bi bi-file-text me-2"></i>View Details
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <!--Restock Button -->
                                                        <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                                            data-bs-target="#addStocksModal">
                                                            <i class="bi bi-plus-lg me-2"></i> Restock
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <!--Delete Button -->
                                                        <button type="button" class="dropdown-item text-danger"
                                                            id="deleteProductBtn<?= $row['id']; ?>">
                                                            <i class="bi bi-trash me-2"></i> Delete
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10">No products found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <nav aria-label="Page navigation" class="d-flex justify-content-end mt-3">
                    <ul class="pagination justify-content-center custom-pagination">
                        <li class="page-item <?= ($page == 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= max(1, $page - 1); ?>"><span
                                    aria-hidden="true">&lt;</span></a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page == $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= min($total_pages, $page + 1); ?>"><span
                                    aria-hidden="true">&gt;</span></a>
                        </li>
                    </ul>
                </nav>
            </main>
        </div>
    </div>

    <!-- PRODUCT DETAILS MODAL-->
    <div class="modal fade" id="productDetailsModal" tabindex="-1" aria-labelledby="productDetailsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center">
                        <img src="./images/detail.png" alt="detail icon" style="margin-right: 5px; height:50px;">Product
                        Details
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="p-3">
                        <h6 class="title-text mb-3">Product Details</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label mb-0">Product Name</label>
                                <div class="form-control-plaintext fw-semibold" id="productNameView"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-0">Type</label>
                                <div class="form-control-plaintext" id="productTypesView"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-0">Delivery Receipt Number</label>
                                <div class="form-control-plaintext" id="drNumberView"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-0">Price</label>
                                <div class="form-control-plaintext" id="productPriceView"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label mb-0">Stock Information</label>
                                <div class="border rounded p-2" style="min-height: 60px;" id="productVariants"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-0">Tags</label>
                                <div class="form-control-plaintext" id="productTagsView">
                                    <span class="text-muted">No tags available (placeholder)</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label mb-0">Maximum Quantity to be Sold</label>
                                <div class="form-control-plaintext" id="productMaxQtyView">
                                    <span class="text-muted">Not set (placeholder)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <!-- Images Section -->
                    <div class="p-3">
                        <h6 class="title-text mb-3">Product Image</h6>
                        <div class="text-center" id="productImages"></div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn custom-navy-btn" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!--RESTOCK MODAL -->
    <div class="modal fade" id="addStocksModal" tabindex="-1" aria-labelledby="addStocksModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title fw-bold d-flex align-items-center" id="addStocksModalLabel">
                        <img src="https://cdn-icons-png.flaticon.com/512/1170/1170576.png" alt="Stock Icon" class="me-2"
                            width="30">
                        Add Stocks
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>



                <!-- Modal Body -->

                <div class="modal-body">
                    <form id="restockForm" method="POST" action="process_restock.php">
                        <input type="hidden" name="product_id" id="restockProductId">
                        <input type="hidden" name="product_type" id="restockProductType">

                        <!-- Notice -->
                        <div class="alert alert-info text-center small rounded-pill py-2 mt-2">
                            To maintain accurate stock records, kindly provide the exact quantity of stock
                            linked to the Delivery Receipt Number you enter.
                        </div>

                        <!-- Delivery Receipt Number -->
                        <div class="mb-3">
                            <label class="form-label">Delivery Receipt Number</label>
                            <input type="text" class="form-control" name="dr_number" required
                                placeholder="Please enter the Delivery Receipt Number here">
                        </div>

                        <!-- Dynamic Stock Inputs for Variants -->
                        <div id="variantStockInputs" class="mb-3">
                            <!-- Will be populated dynamically -->
                        </div>

                        <!-- Stock Update by -->
                        <div class="mb-3">
                            <label class="form-label">Stock Update by:</label>
                            <input type="text" class="form-control" name="updated_by" required
                                placeholder="Please enter your name here">
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="restockForm" class="btn custom-navy-btn">
                        <i class="bi bi-plus-circle me-1"></i> Add Stocks
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Stock input logic for Uniforms and Supplies
        document.addEventListener('DOMContentLoaded', function () {
            const typeSelect = document.getElementById('productType');
            const sizesSection = document.getElementById('sizesContainer') ? document.getElementById('sizesContainer').closest('.col-12.mb-3.mt-2') : null;
            const gendersSection = document.getElementById('gendersContainer') ? document.getElementById('gendersContainer').closest('.col-12.mb-3.mt-2') : null;
            const stocksContainer = document.getElementById('stocksContainer');

            function updateStockInputs() {
                if (typeSelect.value === '1') { // Uniform
                    const selectedSizes = Array.from(document.querySelectorAll('input[name="sizes[]"]:checked')).map(input => input.value);
                    const selectedGenders = Array.from(document.querySelectorAll('input[name="genders[]"]:checked')).map(input => input.value);

                    stocksContainer.innerHTML = '';

                    if (selectedSizes.length && selectedGenders.length) {
                        selectedSizes.forEach(size => {
                            selectedGenders.forEach(gender => {
                                const div = document.createElement('div');
                                div.className = 'col-md-4 mb-3';
                                div.innerHTML = `
                            <label class="form-label">Stock for ${size} - ${gender}</label>
                            <input type="number" 
                                   class="form-control" 
                                   name="stocks[${size}][${gender}]" 
                                   min="1"
                                   placeholder="Enter stock quantity" 
                                   required>
                        `;
                                stocksContainer.appendChild(div);
                            });
                        });
                    }
                } else {
                    // Hide stocks container for supplies
                    stocksContainer.innerHTML = '';
                }
            }

            // Add event listeners to size and gender checkboxes for uniforms
            document.querySelectorAll('input[name="sizes[]"], input[name="genders[]"]').forEach(checkbox => {
                checkbox.addEventListener('change', updateStockInputs);
            });

            typeSelect.addEventListener('change', function () {
                const isSupplies = this.value === '2';

                // Hide/show size and gender sections
                if (sizesSection) sizesSection.style.display = isSupplies ? 'none' : '';
                if (gendersSection) gendersSection.style.display = isSupplies ? 'none' : '';

                if (isSupplies) {
                    // Clear all checkboxes
                    document.querySelectorAll('input[name="sizes[]"], input[name="genders[]"]').forEach(checkbox => {
                        checkbox.checked = false;
                    });

                    // Show single stock input for supplies
                    stocksContainer.innerHTML = `
                <div class="col-12">
                    <label class="form-label">Stock Quantity</label>
                    <input type="number" 
                           class="form-control" 
                           name="stocks[total]" 
                           min="1"
                           placeholder="Enter total stock quantity" 
                           required>
                </div>`;
                } else {
                    stocksContainer.innerHTML = '';
                    updateStockInputs();
                }
            });

            // Initial call to set up the correct stock inputs on page load
            if (typeSelect.value === '2') {
                stocksContainer.innerHTML = `
            <div class="col-12">
                <label class="form-label">Stock Quantity</label>
                <input type="number" 
                       class="form-control" 
                       name="stocks[total]" 
                       min="1"
                       placeholder="Enter total stock quantity" 
                       required>
            </div>`;
                if (sizesSection) sizesSection.style.display = 'none';
                if (gendersSection) gendersSection.style.display = 'none';
            } else {
                updateStockInputs();
            }

            // Stock validation on form submit
            document.querySelector('form').addEventListener('submit', function (e) {
                let hasValidStock = false;
                if (typeSelect.value === '2') {
                    const stockInput = document.querySelector('input[name="stocks[total]"]');
                    hasValidStock = stockInput && parseInt(stockInput.value) > 0;
                } else {
                    const stockInputs = document.querySelectorAll('#stocksContainer input[type="number"]');
                    stockInputs.forEach(input => {
                        if (parseInt(input.value) > 0) {
                            hasValidStock = true;
                        }
                    });
                }
                if (!hasValidStock) {
                    alert('Stock quantity must be greater than 0');
                    e.preventDefault();
                }
            });
        });



        //For Tags Section
        document.addEventListener('DOMContentLoaded', function () {
            const typeSelect = document.getElementById('productType');
            const uniformTagsSection = document.getElementById('uniformTagsSection');
            const suppliesTagsSection = document.getElementById('suppliesTagsSection');

            function updateTagsSection() {
                if (typeSelect.value === '1') { // Uniform
                    uniformTagsSection.style.display = '';
                    suppliesTagsSection.style.display = 'none';
                } else if (typeSelect.value === '2') { // Supplies
                    uniformTagsSection.style.display = 'none';
                    suppliesTagsSection.style.display = '';
                } else {
                    uniformTagsSection.style.display = 'none';
                    suppliesTagsSection.style.display = 'none';
                }
            }

            typeSelect.addEventListener('change', updateTagsSection);
            updateTagsSection(); // Initial call on page load
        });

        //Hide the Stocks Label for Uniforms when it's supplies
        document.addEventListener('DOMContentLoaded', function () {
            const typeSelect = document.getElementById('productType');
            const stocksLabel = document.getElementById('stocksLabel');

            function updateStocksLabel() {
                if (typeSelect.value === '2') { // Supplies
                    stocksLabel.style.display = 'none';
                } else {
                    stocksLabel.style.display = '';
                }
            }

            typeSelect.addEventListener('change', updateStocksLabel);
            updateStocksLabel(); // Initial call on page load
        });

        // For checkbox
        document.addEventListener('DOMContentLoaded', function () {
            // Allow multiple checkboxes for uniform tags (no restriction)
            // Only one checkbox for supplies tags
            document.querySelectorAll('.supplies-tag-checkbox').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                if (this.checked) {
                document.querySelectorAll('.supplies-tag-checkbox').forEach(function (box) {
                    if (box !== checkbox) box.checked = false;
                });
                }
            });
            });
        });

        // Product Details Modal Handler
        document.addEventListener('DOMContentLoaded', function () {
            const productDetailsModal = document.getElementById('productDetailsModal');

            productDetailsModal.addEventListener('show.bs.modal', function (event) {
                // Get the button that triggered the modal
                const button = event.relatedTarget;

                // Extract data from button attributes
                const data = {
                    id: button.getAttribute('data-id'),
                    productName: button.getAttribute('data-product_name'),
                    type: button.getAttribute('data-type'),
                    drNumber: button.getAttribute('data-drnumber'),
                    price: parseFloat(button.getAttribute('data-price')),
                    totalStock: button.getAttribute('data-total_stock'),
                    variants: button.getAttribute('data-variants'),
                    image: button.getAttribute('data-image')
                };

                // Fetch additional product details from server
                fetch(`get_product_details.php?id=${data.id}`)
                    .then(response => response.json())
                    .then(productData => {
                        // Update modal content
                        document.getElementById('productNameView').textContent = data.productName;
                        document.getElementById('productTypesView').textContent = data.type === '1' ? 'Uniform' : 'Supplies';
                        document.getElementById('drNumberView').textContent = data.drNumber;
                        document.getElementById('productPriceView').textContent = '₱' + data.price.toFixed(2);

                        // Stock/variant information
                        const variantsContainer = document.getElementById('productVariants');
                        if (data.variants && data.variants.trim() !== '') {
                            variantsContainer.innerHTML = `${data.variants}<br><strong>Total: ${data.totalStock} pcs</strong>`;
                        } else {
                            variantsContainer.innerHTML = `<strong>Total: ${data.totalStock} pcs</strong>`;
                        }

                        // Display tags
                        const tagsContainer = document.getElementById('productTagsView');
                        if (productData.tags && productData.tags.length > 0) {
                            const tagsHTML = productData.tags.map(tag => {
                                let badgeClass = '';
                                // Assign badge classes based on tag type
                                if (data.type === '1') { // Uniform
                                    badgeClass = getBadgeClassForUniform(tag);
                                } else { // Supplies
                                    badgeClass = getBadgeClassForSupplies(tag);
                                }
                                return `<span class="badge ${badgeClass} me-1">${tag}</span>`;
                            }).join('');
                            tagsContainer.innerHTML = tagsHTML;
                        } else {
                            tagsContainer.innerHTML = '<span class="text-muted">No tags available</span>';
                        }

                        // Display max quantity
                        const maxQtyContainer = document.getElementById('productMaxQtyView');
                        if (productData.max_quantity) {
                            maxQtyContainer.textContent = `${productData.max_quantity} pcs`;
                        } else {
                            maxQtyContainer.innerHTML = '<span class="text-muted">Not set</span>';
                        }

                        // Display product image
                        const productImagesContainer = document.getElementById('productImages');
                        if (data.image) {
                            productImagesContainer.innerHTML = `
                                        <img src="${data.image}" 
                                             alt="${data.productName}" 
                                             class="img-fluid" 
                                             style="max-height: 150px; width: auto;">`;
                        } else {
                            productImagesContainer.innerHTML = '<p class="text-muted">No image available</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching product details:', error);
                        alert('Error loading product details');
                    });
            });
        });

        // Helper function for uniform badge classes
        function getBadgeClassForUniform(tag) {
            const badgeMap = {
                'Pre-school': 'preschool_badge',
                'Kindergarten': 'kinder_badge',
                'Elementary': 'elementary_badge',
                'Junior High School': 'jhs_badge',
                'Senior High School': 'shs_badge',
                'BS Tourism Management': 'bstm_badge',
                'BS Information System': 'bsis_badge',
                'BS Hotel and Restaurant Management': 'bhrm_badge',
                'BS Secondary Education': 'secondary_badge',
                'BS Elementary Education': 'educ_badge',
                'Criminology': 'crim_badge'
            };
            return badgeMap[tag] || 'badge-secondary';
        }

        // Helper function for supplies badge classes
        function getBadgeClassForSupplies(tag) {
            const badgeMap = {
                'Writing Tools': 'writing_badge',
                'Paper Products': 'paper_badge',
                'Art Supplies': 'art_badge'
            };
            return badgeMap[tag] || 'badge-secondary';
        }

        //bulk delete and single delete
          document.addEventListener("DOMContentLoaded", () => {
        const selectAllCheckbox = document.getElementById('selectAllProducts');
        const productCheckboxes = document.querySelectorAll('.product-checkbox');
        const bulkDeleteContainer = document.getElementById('bulkDeleteContainer');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        const alertContainer = document.getElementById('alertContainer');
        let deleteData = null;
    
        // Select All checkbox functionality
        selectAllCheckbox.addEventListener('change', function() {
            productCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkDeleteButton();
        });
    
        // Individual checkbox functionality
        productCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(productCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(productCheckboxes).some(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
                updateBulkDeleteButton();
            });
        });
    
        // Update bulk delete button visibility
        function updateBulkDeleteButton() {
            const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
            bulkDeleteContainer.style.display = checkedBoxes.length > 0 ? 'block' : 'none';
        }
    
        // Handle Bulk Delete
        bulkDeleteBtn.addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('.product-checkbox:checked'))
                .map(cb => cb.value);
    
            if (selectedIds.length === 0) return;
    
            deleteData = { type: 'bulk', ids: selectedIds };
            deleteModal.show();
        });
    
        // Handle Single Delete
        document.querySelectorAll('[id^="deleteProductBtn"]').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.id.replace('deleteProductBtn', '');
                deleteData = { type: 'single', id: productId };
                deleteModal.show();
            });
        });
    
        // Confirm Delete Action
        confirmDeleteBtn.addEventListener('click', () => {
            deleteModal.hide();
            if (!deleteData) return;

            const url = 'delete_product.php';
            const bodyData = deleteData.type === 'bulk' ? { ids: deleteData.ids } : { id: deleteData.id };

            fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(bodyData)
            })
            .then(res => res.json())
            .then(data => {
            if (data.success) {
                showAlert(
                `<i class="bi bi-check-circle-fill me-2" style="font-size: 1.3rem;"></i>` +
                (deleteData.type === 'bulk'
                    ? 'Selected products deleted successfully!'
                    : 'Product deleted successfully!')
                );
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('Error deleting product(s): ' + (data.message || 'Unknown error'), 'danger');
            }
            })
            .catch(err => {
            console.error('Error:', err);
            showAlert('An error occurred while deleting product(s)', 'danger');
            });
        });
        // Alert function
        function showAlert(message, type = 'success') {
            const wrapper = document.createElement('div');
            wrapper.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show shadow" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
            alertContainer.append(wrapper);
            setTimeout(() => {
                const alert = bootstrap.Alert.getOrCreateInstance(wrapper.querySelector('.alert'));
                alert.close();
            }, 3000);
        }
    });
        // Add Product Form Validation with Bootstrap Alerts
        document.addEventListener('DOMContentLoaded', function () {
            const addProductForm = document.querySelector('#addProductModal form');
            const modalBody = addProductForm.closest('.modal-content').querySelector('.modal-body');

            // Helper to show Bootstrap alert in modal
            function showFormAlert(message, type = 'danger') {
            // Remove previous alert
            const prevAlert = modalBody.querySelector('.form-validation-alert');
            if (prevAlert) prevAlert.remove();

            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} form-validation-alert alert-dismissible fade show`;
            alertDiv.role = 'alert';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            modalBody.insertBefore(alertDiv, modalBody.firstChild);

            // Auto-hide after 4 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                alertDiv.classList.remove('show');
                alertDiv.classList.add('hide');
                setTimeout(() => alertDiv.remove(), 500);
                }
            }, 4000);
            }

            addProductForm.addEventListener('submit', function (e) {
            e.preventDefault();

            // Remove previous alert
            const prevAlert = modalBody.querySelector('.form-validation-alert');
            if (prevAlert) prevAlert.remove();

            // Basic validation
            const requiredFields = ['product_name', 'dr_number', 'price', 'type', 'max_quantity'];
            let isValid = true;

            requiredFields.forEach(field => {
                const input = this.querySelector(`[name="${field}"]`);
                if (!input.value.trim()) {
                isValid = false;
                input.classList.add('is-invalid');
                } else {
                input.classList.remove('is-invalid');
                }
            });

            // Validate tags
            const type = this.querySelector('#productType').value;
            const uniformTags = document.querySelectorAll('.uniform-tag-checkbox:checked');
            const suppliesTags = document.querySelectorAll('.supplies-tag-checkbox:checked');

            if (type === '1' && uniformTags.length === 0) {
                isValid = false;
                showFormAlert('Please select at least one uniform tag.');
                return;
            }

            if (type === '2' && suppliesTags.length === 0) {
                isValid = false;
                showFormAlert('Please select at least one supplies tag.');
                return;
            }

            // Validate stocks
            if (type === '1') {
                const selectedSizes = Array.from(document.querySelectorAll('input[name="sizes[]"]:checked')).map(input => input.value);
                const selectedGenders = Array.from(document.querySelectorAll('input[name="genders[]"]:checked')).map(input => input.value);

                if (selectedSizes.length === 0 || selectedGenders.length === 0) {
                isValid = false;
                showFormAlert('Please select at least one size and gender for uniforms.');
                return;
                }

                // Check if at least one stock quantity is entered
                const stockInputs = document.querySelectorAll('#stocksContainer input[type="number"]');
                let hasStock = false;
                stockInputs.forEach(input => {
                if (parseInt(input.value) > 0) {
                    hasStock = true;
                }
                });

                if (!hasStock) {
                isValid = false;
                showFormAlert('Please enter stock quantity for at least one size-gender combination.');
                return;
                }
            } else {
                const totalStock = document.querySelector('input[name="stocks[total]"]');
                if (!totalStock || parseInt(totalStock.value) <= 0) {
                isValid = false;
                showFormAlert('Please enter a valid stock quantity.');
                return;
                }
            }

            // Submit form if valid
            if (isValid) {
                this.submit();
            }
            });
        });

        //  restock modal event listener code 

        document.addEventListener('DOMContentLoaded', function () {
            const restockModal = document.getElementById('addStocksModal');
            const restockForm = document.getElementById('restockForm');

            restockModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const tr = button.closest('tr');
            const productId = tr.querySelector('.product-checkbox').value;
            const productType = tr.querySelector('.badge').textContent.trim();
            const variantsText = tr.querySelector('td:nth-child(6)').innerHTML;

            // Reset form
            restockForm.reset();

            document.getElementById('restockProductId').value = productId;
            document.getElementById('restockProductType').value = productType;

            const variantStockInputs = document.getElementById('variantStockInputs');
            variantStockInputs.innerHTML = ''; // Clear existing inputs

            if (productType === 'Uniform') {
                const variants = variantsText.split('<br>');
                variants.forEach(variant => {
                if (!variant.includes('Total:')) {
                    // Updated regex pattern to handle spaces and capture groups properly
                    const match = variant.match(/([^(]+)\s*\(([^)]+)\):\s*(\d+)\s*pcs/);
                    if (match) {
                    const size = match[1].trim();
                    const gender = match[2].trim();
                    const currentStock = match[3];

                    const div = document.createElement('div');
                    div.className = 'mb-3';
                    div.innerHTML = `
                            <label class="form-label">Add Stock for ${size} (${gender})</label>
                            <div class="input-group">
                            <input type="number" 
                                   class="form-control stock-input" 
                                   name="variant_stock[${size}][${gender}]" 
                                   min="0"
                                   
                                    placeholder="Enter quantity to add"
                                           required>
                                    <span class="input-group-text">Current: ${currentStock} pcs</span>
                                        `;
                                variantStockInputs.appendChild(div);

                                // Add event listener to ensure valid number input
                                const input = div.querySelector('input');
                                input.addEventListener('input', function () {
                                    if (this.value < 0) this.value = 0;
                                });
                            }
                        }
                    });
                } else {
                    // For supplies - single stock input
                    const match = variantsText.match(/Total:\s*(\d+)\s*pcs/);
                    const currentStock = match ? match[1] : '0';
                    variantStockInputs.innerHTML = `
                                <label class="form-label">Stock to Add</label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control stock-input" 
                                           name="add_stock" 
                                           min="0"
                                           
                                           placeholder="Enter quantity to add"
                                           required>
                                    <span class="input-group-text">Current: ${currentStock} pcs</span>
                                </div>
                            `;

                    const input = variantStockInputs.querySelector('input');
                    input.addEventListener('input', function () {
                        if (this.value < 0) this.value = 0;
    
                    });
                }
            });

            // Form validation before submit
            restockForm.addEventListener('submit', function (e) {
                e.preventDefault();

                // Helper to show validation message in modal
                function showRestockValidationMessage(message) {
                    let alertDiv = restockModal.querySelector('.restock-validation-message');
                    if (!alertDiv) {
                        alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger restock-validation-message';
                        alertDiv.style.marginBottom = '10px';
                        // Insert at the top of modal body
                        const modalBody = restockModal.querySelector('.modal-body');
                        modalBody.insertBefore(alertDiv, modalBody.firstChild);
                    }
                    alertDiv.textContent = message;
                }

                // Remove previous validation message
                const prevAlert = restockModal.querySelector('.restock-validation-message');
                if (prevAlert) prevAlert.remove();

                // Validate DR number
                const drNumber = this.querySelector('input[name="dr_number"]').value.trim();
                if (!drNumber) {
                    showRestockValidationMessage('Please enter a Delivery Receipt Number');
                    return;
                }

                // Validate stock inputs
                const stockInputs = this.querySelectorAll('.stock-input');
                let totalStock = 0;
                stockInputs.forEach(input => {
                    const value = parseInt(input.value) || 0;
                    totalStock += value;
                });

                if (totalStock === 0) {
                    showRestockValidationMessage('Please enter a stock quantity greater than 0 to one of the product variants');
                    return;
                }

                // Validate updated_by field
                const updatedBy = this.querySelector('input[name="updated_by"]').value.trim();
                if (!updatedBy) {
                    showRestockValidationMessage('Please enter your name in the "Stock Update by" field');
                    return;
                }

                // If all validations pass, submit the form
                this.submit();
            });
        });

        //product status filter
        document.addEventListener('DOMContentLoaded', function () {
            const typeFilters = document.querySelectorAll('.filter-type');
            const statusFilters = document.querySelectorAll('.filter-status');

            let currentTypeFilter = 'all';
            let currentStatusFilter = 'all';

            function filterTable() {
                const rows = document.querySelectorAll('tbody tr');

                rows.forEach(row => {
                    let showRow = true;

                    // Type filtering
                    if (currentTypeFilter !== 'all') {
                        const typeCell = row.querySelector('.badge').textContent.trim();
                        if (typeCell !== currentTypeFilter) {
                            showRow = false;
                        }
                    }

                    // Status filtering
                    if (currentStatusFilter !== 'all' && showRow) {
                        const stockCell = row.querySelector('.status');
                        const stockStatus = stockCell.textContent.trim();

                        if (currentStatusFilter === 'in-stock' && stockStatus !== 'In Stock') {
                            showRow = false;
                        }
                        if (currentStatusFilter === 'out-of-stock' && stockStatus !== 'Out of Stock') {
                            showRow = false;
                        }
                    }

                    row.style.display = showRow ? '' : 'none';
                });
            }

            // Status filter click handlers
            statusFilters.forEach(filter => {
                filter.addEventListener('click', function (e) {
                    e.preventDefault();
                    currentStatusFilter = this.dataset.status;

                    // Update dropdown button text
                    document.getElementById('stocksStatusDropdown').textContent =
                        currentStatusFilter === 'all' ? 'Status' :
                            (currentStatusFilter === 'in-stock' ? 'In Stock' : 'Out of Stock');

                    filterTable();
                });
            });

            // Type filter click handlers (keep existing code)
            typeFilters.forEach(filter => {
                filter.addEventListener('click', function (e) {
                    e.preventDefault();
                    currentTypeFilter = this.dataset.type;
                    document.getElementById('tagsDropdown').textContent =
                        currentTypeFilter === 'all' ? 'Types' : currentTypeFilter;
                    filterTable();
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('productSearch');
            const tableBody = document.querySelector('.image-table-body');
            let typingTimer;
            const doneTypingInterval = 300; // Delay in milliseconds

            // Function to perform the search
            function searchProducts(searchTerm) {
                fetch(`search_products.php?search=${encodeURIComponent(searchTerm)}`)
                    .then(response => response.text())
                    .then(html => {
                        tableBody.innerHTML = html;
                        // Reinitialize any event listeners for the new content
                        initializeProductEventListeners();
                    })
                    .catch(error => console.error('Error:', error));
            }

            // Initialize event listeners for dynamic content
            function initializeProductEventListeners() {
                // Reinitialize delete buttons
                document.querySelectorAll('[id^="deleteProductBtn"]').forEach(button => {
                    button.addEventListener('click', function () {
                        const productId = this.id.replace('deleteProductBtn', '');
                        // Your existing delete logic
                    });
                });

                // Reinitialize checkboxes
                const checkboxes = document.querySelectorAll('.product-checkbox');
                checkboxes.forEach(cb => {
                    cb.addEventListener('change', function () {
                        // Your existing checkbox logic
                    });
                });
            }

            // Input event listener with debouncing
            searchInput.addEventListener('input', function () {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    const searchTerm = this.value.trim();
                    searchProducts(searchTerm);
                }, doneTypingInterval);
            });

            // Clear button functionality
            const searchButton = searchInput.nextElementSibling;
            searchButton.addEventListener('click', function () {
                searchInput.value = '';
                searchProducts('');
            });
        });

        //alert for successful product addition
            document.addEventListener("DOMContentLoaded", () => {
                const alertBox = document.getElementById("successAlert");
                if (alertBox) {
                    // Auto-hide after 3 seconds (optional)
                    setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alertBox);
                    bsAlert.close();
                    }, 5000);

                    // Remove ?added=success from URL without reloading
                    const url = new URL(window.location);
                    url.searchParams.delete("added");
                    window.history.replaceState({}, document.title, url);
                }
        });

        //alert for error in image upload
        document.addEventListener("DOMContentLoaded", () => {
            const alertBox = document.getElementById("statusAlert");
            if (alertBox) {
                // Auto-hide after 3 seconds (optional)
                setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alertBox);
                bsAlert.close();
                }, 5000);

                // Remove ?added=success or ?error=... from URL
                const url = new URL(window.location);
                url.searchParams.delete("added");
                url.searchParams.delete("error");
                window.history.replaceState({}, document.title, url);
            }
            });
    </script>

</body>

</html>