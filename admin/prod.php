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
    <link rel="icon" href="admin/images/vmc_basket_logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kulim+Park:ital,wght@0,200;0,300;0,400;0,600;0,700;1,200;1,300;1,400;1,600;1,700&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="styles.css">
    <style>
        #qrcode {
            min-width: 128px;
            min-height: 128px;
            margin: 0 auto;
        }
        #qrcode img {
            margin: 0 auto;
        }
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
        <nav class="navbar navbar-light bg-light d-md-none shadow-sm">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <img src="images/vmc_basket_logo.png" alt="VMC Logo" class="vmc-logo img-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </nav>

        <!-- Sidebar -->
        <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse">
        <div class="text-center py-3 d-none d-md-block">
            <img src="images/vmc_basket_logo.png" alt="VMC Logo" class="vmc-logo img-fluid">
        </div>

        <ul class="nav flex-column px-2 mb-3">
            <button class="btn btn-light btn-sm d-lg-none d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
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
                <a href="inquiries.php" class="nav-link">
                    <i class="bi bi-chat-dots me-2"></i> Messages
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
            <li class="nav-item justify-content-end mt-lg-5">
                <a href="logout.php" class="nav-link text-danger fw-semibold">
                    <i class="bi bi-box-arrow-right me-2"></i> Log Out
                </a>
            </li>
        </ul>
    </nav>

            <!-- Content Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 content">
                <div class="d-flex justify-content-end mb-5">
                    <div class="search-container">
                        <input type="text" class="form-control" placeholder="">
                        <button><img src="./images/search-icon.png" alt="Search"></button>
                    </div>
                </div>
                <div class="mt-2 d-flex flex-row align-items-center">
                    <img src="./images/Admin Nav/products-nav.png" alt="VMC Products" class="img-fluid" style="max-width: 40px; margin-right: 10px;">
                    <h2 class="mb-0">Products</h2>
                </div>

                <div class="d-flex justify-content-end mb-3"> 
                    <!-- BUTTON FOR ADD NEW PRODUCTS FORM -->
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <img src="./images/add.png" alt="Add" style="max-width: 20px; margin-right: 5px;">
                     New Products
                    </button>

                    <!-- ADD NEW PRODUCTS FORM -->
                    <div class="modal fade" id="addProductModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <!-- Modal Header -->
                                <div class="modal-header">
                                    <h5 class="modal-title" class="d-flex align-items-center">
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
                                            <input type="text" class="form-control" name="product_name" required>
                                        </div>
                                        
                                        <div class="col-12">
                                            <label class="form-label">Delivery Receipt Number</label>
                                            <input type="text" class="form-control" name="dr_number" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Price</label>
                                            <input type="number" class="form-control" name="price" required>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Sizes</label>
                                            <div class="row" id="sizesContainer">
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="sizes[]" value="XS" id="sizeXS">
                                                        <label class="form-check-label" for="sizeXS">XS</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="sizes[]" value="Small" id="sizeS">
                                                        <label class="form-check-label" for="sizeS">Small</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="sizes[]" value="Medium" id="sizeM">
                                                        <label class="form-check-label" for="sizeM">Medium</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="sizes[]" value="Large" id="sizeL">
                                                        <label class="form-check-label" for="sizeL">Large</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="sizes[]" value="XL" id="sizeXL">
                                                        <label class="form-check-label" for="sizeXL">XL</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="sizes[]" value="2XL" id="size2XL">
                                                        <label class="form-check-label" for="size2XL">2XL</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label class="form-label">Gender</label>
                                            <div class="row" id="gendersContainer">
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="genders[]" value="Male" id="genderMale">
                                                        <label class="form-check-label" for="genderMale">Male</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="genders[]" value="Female" id="genderFemale">
                                                        <label class="form-check-label" for="genderFemale">Female</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="genders[]" value="Unisex" id="genderUnisex">
                                                        <label class="form-check-label" for="genderUnisex">Unisex</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label">Stocks for each variant</label>
                                            <div class="row" id="stocksContainer">
                                                <!-- Stock inputs will be dynamically added here -->
                                            </div>
                                        </div>
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
                                    <button type="submit" class="btn btn-primary">Add Product</button>
                                </div>
                                   </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                
               <!-- PRODUCTS TABLE -->
<div class="table-container table-responsive-lg">
    <table class="table table-bordered">
        <thead class="image-table-header">
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Type</th>
                <th>Date Modified</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody class="image-table-body">
            <?php if ($result->num_rows > 0): ?>
                <?php $count = $offset + 1; while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $count++; ?></td>
                        <td class="text-start">
                            <img src="<?= $row['image'] ?: 'default.png'; ?>" class="product-image" alt="Product Image" name="product_image" style="width: auto; height: 200px; justify-content: center;"><br>
                            <strong><?= $row['product_name']; ?></strong><br>
                            <small>D.R. No: <?= $row['dr_number']; ?></small>
                        </td>
                        <td class="text-start">₱<?= number_format($row['price'], 2); ?></td>
                        <td class="text-start">
                            <?= $row['size_variants'] ?: ' '; ?><br>
                            <strong>Total: <?= $row['total_stock']; ?> pcs</strong>
                        </td>
                        <td class="text-start"><?= $row['type']; ?></td>
                        <td class="text-start"><?= $row['date_modified']; ?></td>
                        <td>
                        <button type="button" class="bi bi-file-text btn btn-outline-dark rounded-100" 
                            data-bs-toggle="modal" 
                            data-bs-target="#productDetailsModal"
                            data-id="<?= $row['id']; ?>"
                            data-variant_id="<?= $row['variant_id']; ?>"
                            data-product_name="<?= $row['product_name']; ?>"
                            data-drnumber="<?= $row['dr_number']; ?>"
                            data-price="<?= $row['price']; ?>"
                            data-total_stock="<?= $row['total_stock']; ?>"
                            data-variants="<?= htmlspecialchars($row['size_variants']); ?>"
                            data-type="<?= $row['type']; ?>"
                            data-image="<?= $row['image']; ?>">
                            Details
                        </button>

                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">No products found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
                
                <nav aria-label="Page navigation" class="d-flex justify-content-end mt-3">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= ($page == 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= max(1, $page - 1); ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page == $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= min($total_pages, $page + 1); ?>">Next</a>
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
                <h5 class="modal-title" class="d-flex align-items-center">
                    <img src="./images/detail.png" alt="add user icon" style="margin-right: 5px; height:50px;">Product Details
                </h5> 
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <form>
                    <div class="p-3">
                        <h6 class="title-text mb-3">Product Details</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="productName" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type</label>
                                <input type="text" class="form-control" id="productTypes" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Delivery Receipt Number</label>
                                <input type="text" class="form-control" id="drNumber" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Price</label>
                                <input type="text" class="form-control" id="productPrice" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Stock Information</label>
                                <div class="form-control" style="height: auto; min-height: 100px;" id="productVariants"></div>
                            </div>
                        </div>
                    </div>
                    <hr>

                    <!-- Images Section -->
                    <div class="p-3">
                        <h6 class="title-text mb-3">Product Image</h6>
                        <div class="text-center" id="productImages"></div>
                    </div>
                    <hr>

                    <!-- QR Code Section -->
                    <div class="p-3 bg-light">
                        <h6 class="title-text mb-3">QR Code</h6>
                        <div class="text-center">
                            <div id="qrcode" class="d-inline-block bg-white p-3 rounded"></div>
                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-primary" id="downloadQRBtn">
                                    <i class="bi bi-download"></i> Download QR Code
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer d-flex justify-content-between buttons">
                <button type="button" class="btn btn-outline-danger" id="deleteProductBtn">
                    <i class="bi bi-trash"></i> Delete
                </button>
                
                <div class="d-flex gap-2" style="width: 300px;">
                    <button type="button" class="btn btn-primary w-100">
                        <i class="bi bi-plus-lg"></i> Add Stocks
                    </button>
                    <button type="button" class="btn btn-outline-secondary w-100" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

    
// Set product details into the modal when it is opened
const productDetailsModal = document.getElementById('productDetailsModal');
const deleteButton = document.getElementById('deleteProductBtn');

productDetailsModal.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    
    // Get data from button attributes
    const productId = button.getAttribute('data-id');
    const productName = button.getAttribute('data-product_name');
    const productType = button.getAttribute('data-type');
    const drNumber = button.getAttribute('data-drnumber');
    const productPrice = button.getAttribute('data-price');
    const totalStock = button.getAttribute('data-total_stock');
    const variants = button.getAttribute('data-variants');
    const productImage = button.getAttribute('data-image');
    const variantId = button.getAttribute('data-variant_id');

    // Set values in the modal form
    document.getElementById('productName').value = productName;
    document.getElementById('productTypes').value = productType === '1' ? 'Uniform' : 'Supplies';
    document.getElementById('drNumber').value = drNumber;
    document.getElementById('productPrice').value = '₱' + parseFloat(productPrice).toFixed(2);
    
    // Update delete button with product ID
    document.getElementById('deleteProductBtn').setAttribute('data-id', productId);

    // First, fetch the variant IDs
    fetch('get_variants.php?product_id=' + productId)
        .then(response => response.json())
        .then(variantData => {
            // Display variants information with radio buttons
            const variantsContainer = document.getElementById('productVariants');
            variantsContainer.innerHTML = '';
            
            if (variants) {
                // Parse the variants string into an array of variants
                const variantArray = variants.split('<br>');
                
                // Create variant selection form with the familiar structure
                variantsContainer.innerHTML = `
                    <div class="mb-3">
                        <label class="form-label">Select Variant for QR Code:</label>
                        <div class="variant-options">
                            ${variantArray.map((variant, index) => {
                                // Extract size and gender from variant text
                                const match = variant.match(/^(.+) \((.+)\):/);
                                let variantHtml = '';
                                
                                if (match) {
                                    const size = match[1].trim();
                                    const gender = match[2].trim();
                                    
                                    // Find the matching variant ID from our fetched data
                                    const matchingVariant = variantData.find(v => 
                                        v.size === size && v.gender === gender
                                    );
                                    
                                    // Use the correct variant ID if found, otherwise fall back to the default
                                    const actualVariantId = matchingVariant ? matchingVariant.id : variantId;
                                    
                                    variantHtml = `
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" 
                                                name="variantSelect" 
                                                id="variant${index}" 
                                                value="${variant.trim()}" 
                                                data-variant-id="${actualVariantId}" 
                                                ${index === 0 ? 'checked' : ''}>
                                            <label class="form-check-label" for="variant${index}">
                                                ${variant}
                                            </label>
                                        </div>
                                    `;
                                }
                                return variantHtml;
                            }).join('')}
                        </div>
                    </div>
                    <div><strong>Total Stock: ${totalStock} pcs</strong></div>
                `;
            } else {
                variantsContainer.innerHTML = 'No variants available';
                // Create hidden input for non-variant products
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'variantSelect';
                hiddenInput.checked = true;
                hiddenInput.dataset.variantId = variantId;
                variantsContainer.appendChild(hiddenInput);
            }
            
            // Add event listeners to radio buttons
            document.querySelectorAll('input[name="variantSelect"]').forEach(radio => {
                radio.addEventListener('change', updateQRCode);
            });
            
            // Initial QR code generation
            updateQRCode();
        })
        .catch(error => {
            console.error('Error fetching variant data:', error);
            // Fall back to the original behavior if the fetch fails
            if (variants) {
                variantsContainer.innerHTML = `
                    <div class="mb-3">
                        <label class="form-label">Select Variant for QR Code:</label>
                        <div class="variant-options">
                            ${variants.split('<br>').map((variant, index) => `
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" 
                                        name="variantSelect" 
                                        id="variant${index}" 
                                        value="${variant.trim()}"
                                        data-variant-id="${variantId}"
                                        ${index === 0 ? 'checked' : ''}>
                                    <label class="form-check-label" for="variant${index}">
                                        ${variant}
                                    </label>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div><strong>Total Stock: ${totalStock} pcs</strong></div>
                `;
            }
            
            // Add event listeners to radio buttons
            document.querySelectorAll('input[name="variantSelect"]').forEach(radio => {
                radio.addEventListener('change', updateQRCode);
            });
            
            // Initial QR code generation
            updateQRCode();
        });

    // Function to update QR code based on selected variant
    function updateQRCode() {
        const selectedVariant = document.querySelector('input[name="variantSelect"]:checked');
        const qrcode = document.getElementById('qrcode');
        qrcode.innerHTML = '';

        // Use the variant ID from the selected radio button
        const variantIdToEncode = selectedVariant ? selectedVariant.dataset.variantId : variantId;
        
        // Generate QR code with the variant ID
        new QRCode(qrcode, {
            text: variantIdToEncode,
            width: 150,
            height: 150,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    }

    // Update download button functionality
    document.getElementById('downloadQRBtn').onclick = function() {
        const selectedVariant = document.querySelector('input[name="variantSelect"]:checked');
        const canvas = qrcode.querySelector('canvas');
        if (canvas && selectedVariant) {
            const link = document.createElement('a');
            const variantName = selectedVariant.value.replace(/[^a-zA-Z0-9]/g, '_');
            link.download = `qr-${productName}-${variantName}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
    };

    // Images Section
    const productImagesContainer = document.getElementById('productImages');
    productImagesContainer.innerHTML = '';

    if (productImage) {
        const img = document.createElement('img');
        img.src = productImage;
        img.alt = productName;
        img.className = 'img-fluid';
        img.style.maxHeight = '150px'; // Set maximum height
        img.style.width = 'auto';      // Maintain aspect ratio
        productImagesContainer.appendChild(img);
    } else {
        productImagesContainer.innerHTML = '<p class="text-muted">No image available</p>';
    }
});

    // Add Stock button functionality
    document.querySelector('#productDetailsModal .btn-primary').addEventListener('click', function() {
        const productId = deleteButton.getAttribute('data-id');
        // Implement your add stock functionality here
        alert('Add stock functionality to be implemented');
    });



document.addEventListener('DOMContentLoaded', function() {
    // Add click event listener to delete button
    document.getElementById('deleteProductBtn').addEventListener('click', function() {
        const productId = this.getAttribute('data-id');
        
        if (!productId) {
            alert('Product ID not found');
            return;
        }
        
        if (confirm('Are you sure you want to delete this product?')) {
            fetch('delete_product.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('productDetailsModal'));
                    modal.hide();
                    
                    // Show success message
                    alert(data.message);
                    
                    // Reload the page to refresh the product list
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to delete product');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the product');
            });
        }
    });
});

    document.addEventListener('DOMContentLoaded', function() {
        const sizesContainer = document.getElementById('sizesContainer');
        const gendersContainer = document.getElementById('gendersContainer');
        const stocksContainer = document.getElementById('stocksContainer');

        function updateStockInputs() {
            const selectedSizes = [...document.querySelectorAll('input[name="sizes[]"]:checked')].map(input => input.value);
            const selectedGenders = [...document.querySelectorAll('input[name="genders[]"]:checked')].map(input => input.value);
            
            stocksContainer.innerHTML = ''; // Clear existing stock inputs
            
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

        // Add event listeners to size and gender checkboxes
        document.querySelectorAll('input[name="sizes[]"], input[name="genders[]"]').forEach(checkbox => {
            checkbox.addEventListener('change', updateStockInputs);
        });

        const typeSelect = document.getElementById('productType');
        const sizesSection = document.querySelector('.col-12.mb-3:has(#sizesContainer)');
        const gendersSection = document.querySelector('.col-12.mb-3:has(#gendersContainer)');
        const stocksSection = document.querySelector('.col-12:has(#stocksContainer)');

        typeSelect.addEventListener('change', function() {
            const isSupplies = this.value === '2';
            
            // Hide/show size and gender sections
            sizesSection.style.display = isSupplies ? 'none' : 'block';
            gendersSection.style.display = isSupplies ? 'none' : 'block';
            
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
                // Reset stock container and trigger update
                stocksContainer.innerHTML = '';
                updateStockInputs();
            }
        });
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('productType');
    const sizesSection = document.querySelector('.col-12.mb-3:has(label:contains("Sizes"))');
    const gendersSection = document.querySelector('.col-12.mb-3:has(label:contains("Gender"))');
    const stocksContainer = document.getElementById('stocksContainer');

    typeSelect.addEventListener('change', function() {
        const isSupplies = this.value === '2';
        
        // Hide/show size and gender sections
        if (sizesSection) sizesSection.style.display = isSupplies ? 'none' : 'block';
        if (gendersSection) gendersSection.style.display = isSupplies ? 'none' : 'block';
        
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
            // Clear stocks container for uniform selection
            stocksContainer.innerHTML = '';
        }
    });

    // Add event listeners to size and gender checkboxes for uniforms
    document.querySelectorAll('input[name="sizes[]"], input[name="genders[]"]').forEach(checkbox => {
        checkbox.addEventListener('change', updateStockInputs);
    });

    function updateStockInputs() {
        if (typeSelect.value === '1') { // Only update for uniforms
            const selectedSizes = [...document.querySelectorAll('input[name="sizes[]"]:checked')].map(input => input.value);
            const selectedGenders = [...document.querySelectorAll('input[name="genders[]"]:checked')].map(input => input.value);
            
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
});

// Add this validation function to your existing JavaScript code
function validateStock() {
    const typeSelect = document.getElementById('productType');
    const isSupplies = typeSelect.value === '2';
    let hasValidStock = false;

    if (isSupplies) {
        // For supplies - check the single stock input
        const stockInput = document.querySelector('input[name="stocks[total]"]');
        hasValidStock = stockInput && parseInt(stockInput.value) > 0;
    } else {
        // For uniforms - check all variant stock inputs
        const stockInputs = document.querySelectorAll('#stocksContainer input[type="number"]');
        stockInputs.forEach(input => {
            if (parseInt(input.value) > 0) {
                hasValidStock = true;
            }
        });
    }

    if (!hasValidStock) {
        alert('Stock quantity must be greater than 0');
        return false;
    }
    return true;
}

// Add this to your form submit handler
document.querySelector('form').addEventListener('submit', function(e) {
    if (!validateStock()) {
        e.preventDefault();
    }
});

// Update your existing stock input creation code to add min attribute
function updateStockInputs() {
    if (typeSelect.value === '1') { // Only update for uniforms
        const selectedSizes = [...document.querySelectorAll('input[name="sizes[]"]:checked')].map(input => input.value);
        const selectedGenders = [...document.querySelectorAll('input[name="genders[]"]:checked')].map(input => input.value);
        
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

// Update the supplies stock input creation
typeSelect.addEventListener('change', function() {
    const isSupplies = this.value === '2';
    
    // ... existing code ...
    
    if (isSupplies) {
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
    }
    
    // ... rest of your existing code ...
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>