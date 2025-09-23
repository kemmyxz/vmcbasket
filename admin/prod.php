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
    $date_modified = date("Y-m-d");
    
    // Handle Image Upload
    $image_path = "";
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $file_tmp = $_FILES["product_image"]["tmp_name"];
        $file_name = time() . "_" . basename($_FILES["product_image"]["name"]);
        $image_path = $target_dir . $file_name;
    
        if (!move_uploaded_file($file_tmp, $image_path)) {
            echo "<script>alert('Failed to upload image');</script>";
            $image_path = "";
        }
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert into products table
        $stmt = $conn->prepare("INSERT INTO products (product_name, dr_number, price, type, date_modified, image) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdsss", $product_name, $dr_number, $price, $type, $date_modified, $image_path);
        $stmt->execute();
        
        $product_id = $conn->insert_id;

        if ($type === '2') { // Supplies
            // Insert single stock entry for supplies
            $total_stock = $_POST['stocks']['total'];
            $stmt = $conn->prepare("INSERT INTO product_variants (product_id, stock) VALUES (?, ?)");
            $stmt->bind_param("ii", $product_id, $total_stock);
            $stmt->execute();
        } else { // Uniform
            // Insert stock for each size-gender combination
            $stocks = $_POST['stocks'];
            $stmt = $conn->prepare("INSERT INTO product_variants (product_id, size, gender, stock) VALUES (?, ?, ?, ?)");
            
            foreach ($stocks as $size => $genderStocks) {
                foreach ($genderStocks as $gender => $stock) {
                    $stmt->bind_param("issi", $product_id, $size, $gender, $stock);
                    $stmt->execute();
                }
            }
        }

        $conn->commit();
        echo "<script> window.location.href='prod.php';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}

// Search and pagination logic
$search = isset($_GET['search']) ? $_GET['search'] : '';
$limit = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT p.*, pv.id as variant_id,
        GROUP_CONCAT(
            CONCAT(pv.size, ' (', pv.gender, '): ', pv.stock, ' pcs') 
            ORDER BY pv.size, pv.gender
            SEPARATOR '<br>'
        ) as size_variants,
        SUM(pv.stock) as total_stock,
        CASE 
            WHEN p.type = '1' THEN 'Uniform'
            WHEN p.type = '2' THEN 'Supplies'
            ELSE 'Unknown'
        END as type_name
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
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket - Admin/Products</title>
    <?php include 'links.php'; ?>
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
<div class="container-fluid" >
        <div class="row">
        <!-- Sidebar Toggle Button -->
        <!-- Top Navbar (visible only on small devices) -->
            <nav class="navbar navbar-light bg-light d-md-none">
                <div class="container-fluid d-flex justify-content-between align-items-center">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
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

        <!-- Title Page and Search -->
        <main class="col-md-9 ms-sm-auto col-lg-10 content p-5">
            <div class="d-flex justify-content-end mb-5">
                <div class="search-container">
                    <input type="text" class="form-control" placeholder="Search...">
                    <button><i class="bi bi-search"></i></button>
                </div>
            </div>
            <div class="mt-2 d-flex flex-row align-items-center">
                <h2 class="mb-0">Product</h2>
            </div>

            <!--Total Products, Add Products Button, and Modal-->
            <div class="d-flex justify-content-between align-items-center mb-2 mt-3">
                <div>
                <strong>Total Products: 100</strong>
                </div>
                <div class="d-flex align-items-center mb-2">
                <!-- BUTTON FOR ADD NEW PRODUCTS FORM -->
                <button type="button" class="admin-btn" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    Add <i class="bi bi-plus-circle ms-1"></i>
                </button>
                </div>
            </div>

            <!-- Bulk Delete Button (hidden by default) -->
            <div id="bulkDeleteContainer" style="display:none; margin-bottom: 16px;">
                <button id="bulkDeleteBtn" class="btn btn-danger">
                <i class="bi bi-trash"></i> Delete Selected
                </button>
            </div>

            <!-- ADD NEW PRODUCTS FORM -->
            <div class="modal fade" id="addProductModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h5  class="d-flex align-items-center">
                        <img src="./images/add-product.png" alt="add user icon" style="margin-right: 5px; height:50px;">Add New Product
                        </h5>                                    
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                            <option value="">Choose Type</option >
                            <option value="1">Uniform</option>
                            <option value="2">Supplies</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Product Name</label>
                            <input type="text" class="form-control" name="product_name" placeholder="Enter product name" required>
                        </div>
                        
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Delivery Receipt Number</label>
                            <input type="text" class="form-control" name="dr_number" placeholder="Ex. DR1234567" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Price</label>
                            <input type="number" class="form-control" name="price" placeholder="Enter price" required>
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
                                    ['id' => 'tagBHRM', 'value' => 'BS Hotel and Restaurant Management', 'badge' => 'bhrm_badge', 'label' => 'BS Hotel and Restaurant Management', 'badgeId' => 'badgeBHRM'],
                                    ['id' => 'tagSecondary', 'value' => 'BS Secondary Education', 'badge' => 'secondary_badge', 'label' => 'BS Secondary Education', 'badgeId' => 'badgeSecondary'],
                                    ['id' => 'tagEduc', 'value' => 'BS Elementary Education', 'badge' => 'educ_badge', 'label' => 'BS Elementary Education', 'badgeId' => 'badgeEduc'],
                                    ['id' => 'tagCrim', 'value' => 'Criminology', 'badge' => 'crim_badge', 'label' => 'Criminology', 'badgeId' => 'badgeCriminology'],
                                ];
                                foreach ($uniformTags as $tag) {
                                    ?>
                                    <div class="form-check">
                                        <input class="form-check-input styled-checkbox uniform-tag-checkbox" type="checkbox" id="<?= $tag['id'] ?>" name="tags[]" value="<?= $tag['value'] ?>">
                                        <label class="form-check-label" for="<?= $tag['id'] ?>">
                                            <span class="badge <?= $tag['badge'] ?> me-2" id="<?= $tag['badgeId'] ?>"><?= $tag['label'] ?></span>
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
                                ];
                                foreach ($suppliesTags as $tag) {
                                    ?>
                                    <div class="form-check">
                                        <input class="form-check-input styled-checkbox supplies-tag-checkbox" type="checkbox" id="<?= $tag['id'] ?>" name="tags[]" value="<?= $tag['value'] ?>">
                                        <label class="form-check-label" for="<?= $tag['id'] ?>">
                                            <span class="badge <?= $tag['badge'] ?> me-2" id="<?= $tag['badgeId'] ?>"><?= $tag['label'] ?></span>
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
                                <input class="form-check-input styled-checkbox" type="checkbox" name="sizes[]" value="XS" id="sizeXS">
                                <label class="form-check-label" for="sizeXS">XS</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                <input class="form-check-input styled-checkbox" type="checkbox" name="sizes[]" value="Small" id="sizeS">
                                <label class="form-check-label" for="sizeS">Small</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                <input class="form-check-input styled-checkbox" type="checkbox" name="sizes[]" value="Medium" id="sizeM">
                                <label class="form-check-label" for="sizeM">Medium</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                <input class="form-check-input styled-checkbox" type="checkbox" name="sizes[]" value="Large" id="sizeL">
                                <label class="form-check-label" for="sizeL">Large</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                <input class="form-check-input styled-checkbox" type="checkbox" name="sizes[]" value="XL" id="sizeXL">
                                <label class="form-check-label" for="sizeXL">XL</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                <input class="form-check-input styled-checkbox" type="checkbox" name="sizes[]" value="2XL" id="size2XL">
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
                                    <input class="form-check-input styled-checkbox" type="checkbox" name="genders[]" value="Male" id="genderMale">
                                    <label class="form-check-label" for="genderMale">Male</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                    <input class="form-check-input styled-checkbox" type="checkbox" name="genders[]" value="Female" id="genderFemale">
                                    <label class="form-check-label" for="genderFemale">Female</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                    <input class="form-check-input styled-checkbox" type="checkbox" name="genders[]" value="Unisex" id="genderUnisex">
                                    <label class="form-check-label" for="genderUnisex">Unisex</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stocks -->
                        <div class="col-12 mt-2">
                            <label class="form-label" id="stocksLabel">Stocks for each Sizes and Gender</label>
                            <div class="row" id="stocksContainer">
                            <!-- Stock inputs will be dynamically added here -->
                            </div>
                        </div>
                    </div>

                        <div class="col-md-12 mt-2">
                            <label class="form-label">Maximum Quantity to be Sold</label>
                            <input type="number" class="form-control" name="max_quantity" placeholder="Ex. 5 pcs" required>
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
                        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
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
                <input type="checkbox" id="selectAllProducts" title="Select All" class="custom-checkbox">
            </th>
            <th>#</th>
            <th>Image</th>
            <th>Product Details</th>
            <th>Price</th>
            <th>Stock</th>
            <th class="align-middle text-center">
                <div class="dropdown">
                <button class="btn p-0 m-0 align-baseline table-dropdown dropdown-toggle" type="button" id="tagsDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration:none;">
                    Type
                </button>
                <ul class="dropdown-menu" aria-labelledby="tagsDropdown">
                    <li><a class="dropdown-item" href="#">All</a></li>
                    <li><a class="dropdown-item" href="#">Uniform</a></li>
                    <li><a class="dropdown-item" href="#">Supplies</a></li>
                    <!-- Add more tag options as needed -->
                </ul>
                </div>
            </th>
            <th class="align-middle text-center">Restock History </th>
            <th class="align-middle text-center">
                <div class="dropdown">
                <button class="btn p-0 m-0 align-baseline table-dropdown dropdown-toggle" type="button" id="stocksStatusDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration:none;">
                    Status
                </button>
                <ul class="dropdown-menu" aria-labelledby="stocksStatusDropdown">
                    <li><a class="dropdown-item" href="#">All</a></li>
                    <li><a class="dropdown-item" href="#">In Stock</a></li>
                    <li><a class="dropdown-item" href="#">Out of Stock</a></li>
                </ul>
                </div>
            </th>
            <th class="align-middle text-start">Action</th>
            </tr>
            </thead>
            <!--table-body-->
            <tbody class="image-table-body text-center align-middle">
            <?php if ($result->num_rows > 0): ?>
            <?php $count = $offset + 1; while ($row = $result->fetch_assoc()): ?>
                <tr>
                <td>
                    <input type="checkbox" class="custom-checkbox product-checkbox" value="<?= $row['id']; ?>">
                </td>
                <td><?= $count++; ?></td>
                <td>
                    <img src="<?= $row['image'] ?: 'default.png'; ?>" class="product-img" alt="Product Image" name="product_image" style="width: 100px; height: 100px; object-fit: cover;">
                </td>
                <td>
                <strong><?= $row['product_name']; ?></strong><br>
                <small>D.R. No: <?= $row['dr_number']; ?></small>
                </td>
                <td>₱<?= number_format($row['price'], 2); ?></td>
                <td>
                <?php if (!empty($row['size_variants'])): ?>
                    <?= $row['size_variants']; ?><br>
                    <strong>Total: <?= $row['total_stock']; ?> pcs</strong>
                <?php else: ?>
                    <strong>Total: <?= $row['total_stock']; ?> pcs</strong>
                <?php endif; ?>
                </td>
                <td>tags</td>
                <td><?= $row['type_name']; ?><br><?= $row['date_modified']; ?></td>
                <td>
                <!-- Status: In Stock/Out of Stock -->
                <?php if ($row['total_stock'] > 0): ?>
                <span class="status active">In Stock</span>
                <?php else: ?>
                <span class="status inactive">Out of Stock</span>
                <?php endif; ?>
                </td>
                <td>
                <div class="dropdown">
                    <button class="btn btn-link p-0 m-0" type="button" id="actionDropdown<?= $row['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 1.5rem; color: #333;">
                    <i class="bi bi-three-dots-vertical fs-5"></i>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="actionDropdown<?= $row['id']; ?>">
                    <li>
                    <!--View Button -->
                        <button 
                        type="button" 
                        class="dropdown-item"
                        data-bs-toggle="modal"
                        data-bs-target="#productDetailsModal"
                        data-id="<?= $row['id']; ?>"
                        data-variant_id="<?= $row['variant_id']; ?>"
                        data-product_name="<?= htmlspecialchars($row['product_name']); ?>"
                        data-drnumber="<?= htmlspecialchars($row['dr_number']); ?>"
                        data-price="<?= $row['price']; ?>"
                        data-total_stock="<?= $row['total_stock']; ?>"
                        data-variants="<?= htmlspecialchars($row['size_variants']); ?>"
                        data-type="<?= $row['type']; ?>"
                        data-image="<?= htmlspecialchars($row['image']); ?>"
                        >
                        <i class="bi bi-file-text me-2"></i>View Details
                        </button>
                    </li>
                    <li>
                    <!--Restock Button -->
                        <button 
                        type="button" 
                        class="dropdown-item"
                        data-bs-toggle="modal" 
                        data-bs-target="#addStocksModal"
                        >
                        <i class="bi bi-plus-lg me-2"></i> Restock
                        </button>
                    </li>
                    <li>
                        <!--Delete Button -->
                        <button 
                        type="button" 
                        class="dropdown-item text-danger"
                        id="deleteProductBtn<?= $row['id']; ?>"
                        >
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
                        <a class="page-link" href="?page=<?= max(1, $page - 1); ?>"><span aria-hidden="true">&lt;</span></a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page == $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?= min($total_pages, $page + 1); ?>"><span aria-hidden="true">&gt;</span></a>
                    </li>
                </ul>
            </nav>
        </main>
    </div>
</div>

<!-- PRODUCT DETAILS MODAL-->
<div class="modal fade" id="productDetailsModal" tabindex="-1" aria-labelledby="productDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    <img src="./images/detail.png" alt="detail icon" style="margin-right: 5px; height:50px;">Product Details
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
          <img src="https://cdn-icons-png.flaticon.com/512/1170/1170576.png" 
               alt="Stock Icon" class="me-2" width="30"> 
          Add Stocks
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <!-- Modal Body -->
    <div class="modal-body">
        <form>
            <!-- Notice -->
            <div class="alert alert-info text-center small rounded-pill py-2 mt-2">
                To maintain accurate stock records, kindly provide the exact quantity of stock 
                linked to the Delivery Receipt Number you enter.
            </div>
            <!-- Delivery Receipt Number -->
            <div class="mb-3">
                <label class="form-label">Delivery Receipt Number</label>
                <input type="text" class="form-control" placeholder="Please enter the Delivery Receipt Number here">
            </div>

            <!-- Stock -->
            <div class="mb-3">
                <label class="form-label">Stock</label>
                <input type="number" class="form-control" placeholder="Please enter the stock quantity here">
            </div>

            <!-- Stock Update by -->
            <div class="mb-3">
                <label class="form-label">Stock Update by:</label>
                <input type="text" class="form-control" placeholder="Please enter your name here">
            </div>
        </form>
    </div>

    <!-- Modal Footer -->
    <div class="modal-footer border-0">
        <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="custom-navy-btn">
        <i class="bi bi-plus-circle me-1"></i> Add Stocks
        </button>
    </div>
</div>


<script>
// Stock input logic for Uniforms and Supplies
document.addEventListener('DOMContentLoaded', function() {
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
        }
    }

    // Add event listeners to size and gender checkboxes for uniforms
    document.querySelectorAll('input[name="sizes[]"], input[name="genders[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', updateStockInputs);
    });

    typeSelect.addEventListener('change', function() {
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
    document.querySelector('form').addEventListener('submit', function(e) {
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

/* --- The rest of your scripts remain unchanged --- */

//For Tags Section
document.addEventListener('DOMContentLoaded', function() {
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
document.addEventListener('DOMContentLoaded', function() {
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
document.addEventListener('DOMContentLoaded', function() {
    // Only one checkbox for uniform tags
    document.querySelectorAll('.uniform-tag-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                document.querySelectorAll('.uniform-tag-checkbox').forEach(function(box) {
                    if (box !== checkbox) box.checked = false;
                });
            }
        });
    });
    // Only one checkbox for supplies tags
    document.querySelectorAll('.supplies-tag-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                document.querySelectorAll('.supplies-tag-checkbox').forEach(function(box) {
                    if (box !== checkbox) box.checked = false;
                });
            }
        });
    });
});

//Showing Product Details to Modal
document.addEventListener('DOMContentLoaded', function() {
    // Listen for modal show event
    var productDetailsModal = document.getElementById('productDetailsModal');
    productDetailsModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;

        // Get data from button attributes
        var productId = button.getAttribute('data-id');
        var productName = button.getAttribute('data-product_name');
        var productType = button.getAttribute('data-type');
        var drNumber = button.getAttribute('data-drnumber');
        var productPrice = button.getAttribute('data-price');
        var totalStock = button.getAttribute('data-total_stock');
        var variants = button.getAttribute('data-variants');
        var productImage = button.getAttribute('data-image');
        var variantId = button.getAttribute('data-variant_id');

        // Set values in the modal
        document.getElementById('productNameView').textContent = productName;
        document.getElementById('productTypesView').textContent = productType === '1' ? 'Uniform' : 'Supplies';
        document.getElementById('drNumberView').textContent = drNumber;
        document.getElementById('productPriceView').textContent = '₱' + parseFloat(productPrice).toFixed(2);

        // Stock/variant info
        var variantsContainer = document.getElementById('productVariants');
        if (variants) {
            variantsContainer.innerHTML = variants + '<br><strong>Total: ' + totalStock + ' pcs</strong>';
        } else {
            variantsContainer.innerHTML = '<strong>Total: ' + totalStock + ' pcs</strong>';
        }

        // Image
        var productImagesContainer = document.getElementById('productImages');
        productImagesContainer.innerHTML = '';
        if (productImage) {
            var img = document.createElement('img');
            img.src = productImage;
            img.alt = productName;
            img.className = 'img-fluid';
            img.style.maxHeight = '150px';
            img.style.width = 'auto';
            productImagesContainer.appendChild(img);
        } else {
            productImagesContainer.innerHTML = '<p class="text-muted">No image available</p>';
        }
    });
});
</script>

<script>
//Multiple delete functionality
    document.addEventListener('DOMContentLoaded', function() {
        const selectAll = document.getElementById('selectAllProducts');
        const checkboxes = document.querySelectorAll('.product-checkbox');
        const bulkDeleteContainer = document.getElementById('bulkDeleteContainer');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

        // Select/Deselect all checkboxes
        selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        toggleBulkDelete();
        });

        // If any checkbox is changed, update selectAll and bulk delete button
        checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            selectAll.checked = Array.from(checkboxes).every(cb => cb.checked);
            toggleBulkDelete();
        });
        });

        function toggleBulkDelete() {
        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
        bulkDeleteContainer.style.display = anyChecked ? 'block' : 'none';
        }

        // Example: Bulk delete action (replace with your AJAX or form submit)
        bulkDeleteBtn.addEventListener('click', function() {
        const selectedIds = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        if (selectedIds.length === 0) return;
        if (confirm('Are you sure you want to delete the selected products?')) {
            // TODO: Send selectedIds to server for deletion (AJAX or form)
            alert('Selected IDs: ' + selectedIds.join(', '));
        }
        });
    });
</script>
</body>
</html>