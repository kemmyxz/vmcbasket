<?php
session_start();
require 'admin/inc/config.php';

if (!isset($_SESSION['student_no'])) {
    header("Location: login.php"); // or an appropriate redirect
    exit();
}
// Get user info
$student_no = $_SESSION['student_no'];
$sql = "SELECT * FROM users WHERE student_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_no);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Profile image fallback
$profilePic = $user['photo'];
$fullName = $user['student_fname'] . ' ' . $user['student_mname'] . ' ' . $user['student_lname'];

// Pagination settings
$items_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get total number of orders
$total_sql = "SELECT COUNT(DISTINCT o.receipt_no) as total FROM orders o WHERE o.user_id = ?";
$stmt = $conn->prepare($total_sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$total_result = $stmt->get_result();
$total_orders = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_orders / $items_per_page);

// Modify your existing orders query to include LIMIT and OFFSET
$orders_sql = "
    SELECT o.*, p.image as product_image, p.product_name, r.order_status, r.receipt_id,
           CASE WHEN rv.id IS NOT NULL THEN 1 ELSE 0 END as has_review,
           CASE WHEN ori.id IS NOT NULL THEN 1 ELSE 0 END as has_receipt
    FROM orders o
    LEFT JOIN products p ON o.product_id = p.id
    LEFT JOIN order_receipt r ON o.receipt_no = r.receipt_id
    LEFT JOIN product_reviews rv ON o.id = rv.order_id
    LEFT JOIN order_receipts_images ori ON r.receipt_id = ori.receipt_id
    WHERE o.user_id = ? AND o.receipt_no IS NOT NULL 
    GROUP BY o.receipt_no
    ORDER BY o.order_date DESC, o.receipt_no DESC
    LIMIT ? OFFSET ?";

$stmt = $conn->prepare($orders_sql);
$stmt->bind_param("iii", $_SESSION['user_id'], $items_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();

// Update the grouped orders array construction
$grouped_orders = [];
while ($row = $result->fetch_assoc()) {
    $receipt_no = $row['receipt_no'];
    if (!isset($grouped_orders[$receipt_no])) {
        $grouped_orders[$receipt_no] = [
            'receipt_no' => $receipt_no,
            'status' => $row['order_status'] ?? 'Pending', // Get status from order_receipt
            'order_date' => $row['order_date'],
            'payment_method' => $row['payment_method'],
            'has_receipt' => $row['has_receipt'],
            'items' => [],
            'total' => 0
        ];
    }
    $grouped_orders[$receipt_no]['items'][] = $row;
    $grouped_orders[$receipt_no]['total'] += ($row['price'] * $row['quantity']);
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket-My Purchase</title>
    <?php include 'links.php';?>
    <style>
        /* Navy button for "Buy Again" */
        .custom-navy-btn {
            background-color: #0d1b52; /* dark navy */
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 8px 20px;
            font-weight: 500;
            margin-left: 8px;
            transition: 0.2s;
        }
        .custom-navy-btn:hover {
            background-color: #142674;
        }
        /* Modal Styling */
        .rate-review-modal {
        border-radius: 12px;
        overflow: hidden;
        }

        /* Left side panel */
        .product-info {
        width: 40%;
        background-color: #d5e9f3;
        }

        /* Star rating */
        .star-rating i {
        font-size: 1.5rem;
        color: #000;
        cursor: pointer;
        }

        /* Upload photo button */
        .photo-upload-btn {
        width: 120px;
        height: 90px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        }
        #photoPreview .btn {
        font-weight: bold;
        background-color: rgba(255, 255, 255, 0.85);
        border: none;
        color: #333;
        z-index: 10;
        }

        #photoArea img {
        border-radius: 6px;
        }

        .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid #e9ecef;
        }

        #previewContainer img {
        display: block;
        margin: 0 auto;
        border: 1px solid #ddd;
        padding: 5px;
        background-color: #fff;
        }

        .upload-box.bg-light {
        background-color: #f8f9fa;
        }
        .margin-top {
            margin-top: 80px;
        }
          /* Tablet styles */
    @media (max-width: 991.98px) {
        .tab-card{
            font-size: 0.75rem;
        }
         /* Cards */
        .card {
            padding: 10px;
        }
        .card .d-flex.align-items-center.mb-2 img {
            width: 50px;
            height: 50px;
        }
        .card strong {
            font-size: 14px;
        }
        .card p, .card small {
            font-size: 13px;
        }

        /* Buttons */
        .card button {
            font-size: 12px;
            padding: 6px 18px;
        }

        /* Product review modal */
        .product-info {
            width: 45%; /* shrink left panel */
        }
        .review-form-content {
            padding: 1rem;
            font-size: 0.95rem;
        }
        .star-rating i {
            font-size: 1.25rem;
        }
        .product-info{
            font-size: 0.95rem;
        }
        .review-form{
            font-size: 0.95rem;
        }
    }
    @media (max-width: 575.98px) {
        .margin-top {
            margin-top: 20px;
        }
        .text-title{
            font-size: 0.9rem;
        }
        .product-reviews-container{
            font-size: 0.85rem;
        }
        .product-image{
            width: 150px;
            height: auto;
        }
        
        .highlight-blue {
            font-size: 1rem;
        }

        /* Cards become vertical */
        .card {
            padding: 8px;
            border-radius: 8px;
        }
        .card .d-flex.align-items-center.mb-2 {
            flex-direction: column;
            text-align: center;
        }
        .card .d-flex.align-items-center.mb-2 img {
            width: 100px;
            height: 100px;
            margin-bottom: 8px;
        }
        .card strong {
            font-size: 13px;
        }
        .card p, .card small {
            font-size: 12px;
        }

        /* Buttons stack */
        .card button {
            display: block;
            width: 100%;
            margin: 6px 0;
            font-size: 13px;
            padding: 10px;
        }
        .tab-card {
            flex-direction: column;
        }
        .tab-button {
            width: 50%;
            margin-bottom: 5px;
        }
        /* Product review modal stacked layout */
        .product-info {
            width: 100%;
            padding: 1rem;
            font-size: 0.9rem;
        }
        .review-form-content {
            width: 100%;
            padding: 1rem;
            font-size: 0.9rem;
        }
        .star-rating i {
            font-size: 1.2rem;
        }
        .photo-upload-btn {
            width: 100px;
            height: 80px;
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
        .pagination .page-link {
            padding: 4px 8px;
            font-size: 0.85rem;
        }
        .form-check-label{
            font-size: 0.85rem;
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

    <!-- My Profile Side-bar-->
    <div class="d-flex margin-top">
     <!-- Purchase History -->
     <div class="purchase-history container mb-5 p-5">
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <h2 class="mt-4 mb-5">
                <span class="highlight-blue">My Purchase History</span>
            </h2>
             <!-- Desktop: show p and pagination on the right -->
            <div class="d-flex flex-column justify-content-center align-items-end align-items-sm-end ms-auto text-center">
                <p class="mb-2">8 out of 100 items shows</p>
                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination custom-pagination justify-content-center">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php for($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

    <!-- Tabs -->
     <div class="d-flex tab-card mb-3"> 
        <button class="tab-button active w-100">All</button> 
        <button class="tab-button w-100">To Pay</button> 
        <button class="tab-button w-100">To Pick Up</button> 
        <button class="tab-button w-100">Completed</button> 
        <button class="tab-button w-100">Cancelled</button> 
        <button class="tab-button w-100">Return Refund</button> 
    </div>

    <!-- Search -->
    <div class="purchase-search-bar d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div class="search-input-group input-group mb-2 mb-md-0 w-100">
            <span class="input-group-text bg-light border-1">
                <img src="./admin/images/search-icon.png" alt="Search" width="20">
            </span>
            <input type="text" class="form-control border-1" placeholder="Search product name...">
        </div>
    </div>

    <!-- Purchase History Cards -->
    <?php foreach($grouped_orders as $order): ?>
        <div class="card p-3 mb-4 purchase-card" data-status="<?= $order['status'] ?>">
            <!-- Order Header -->
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="./admin/images/ready-to-pickup.png" alt="Box" width="25" class="me-2">
                    <div>
                        <strong>Order #<?= $order['receipt_no'] ?></strong><br>
                        <small class="text-muted">Ordered on <?= date('M d, Y', strtotime($order['order_date'])) ?></small>
                    </div>
                </div>
                <span class="status-badge <?= strtolower($order['status']) ?>"><?= $order['status'] ?></span>
            </div>
            <hr>
            
            <!-- Products in this order -->
            <?php foreach($order['items'] as $item): ?>
                <div class="d-flex align-items-center mb-3">
                    <img src="admin/<?= $item['product_image'] ?>" width="60" height="60" 
                         class="me-3 rounded" alt="<?= $item['product_name'] ?>">
                    <div class="flex-grow-1">
                        <h6 class="mb-0"><?= $item['product_name'] ?></h6>
                        <small>Size: <?= $item['size'] ?> | Qty: <?= $item['quantity'] ?></small>
                    </div>
                    <div class="text-end">
                        <strong>₱<?= number_format($item['price'], 2) ?></strong>
                    </div>
                </div>
            <?php endforeach; ?>

            <hr>
            <!-- Order Footer -->
            <div class="d-flex justify-content-between align-items-center">
        <div>
            <?php if($order['status'] == 'Pending'): ?>
                <?php if($order['payment_method'] == 'Send Online Receipt'): ?>
                    <?php if(!$order['has_receipt']): ?>
                        <button class="btn btn-outline-danger btn-sm" 
                                data-bs-toggle="modal" 
                                data-bs-target="#cancelOrderModal" 
                                data-receipt="<?= $order['receipt_no'] ?>"
                                data-products='<?= json_encode($order['items']) ?>'>
                            Cancel Order
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            <?php elseif($order['status'] == 'ToPickUp'): ?>
                <button class="btn btn-success btn-sm pickup-btn"
                        data-receipt="<?= $order['receipt_no'] ?>">
                    <i class="bi bi-check2-circle"></i> Confirm Pick-up
                </button>
            <?php endif; ?>
            
<?php if($order['status'] == 'Complete'): ?>
    <?php if(!$item['has_review']): ?>
        <button class="btn custom-outline-black"
                data-bs-toggle="modal"
                data-bs-target="#rateReviewModal"
                data-receipt="<?= $order['receipt_no'] ?>"
                data-products='<?= json_encode($order['items']) ?>'>
            Rate
        </button>

        <button class="btn custom-navy-btn">
            Buy Again
        </button>

        <button class="btn custom-blue-btn"
                data-bs-toggle="modal"
                data-bs-target="#returnRequestModal"
                data-receipt="<?= $order['receipt_no'] ?>"
                data-products='<?= json_encode($order['items']) ?>'>
            Request for Refund
        </button>
    <?php else: ?>
        <button class="btn custom-outline-black" disabled>
            Already Reviewed
        </button>
         <button class="btn custom-navy-btn">
            Buy Again
        </button>

        <button class="btn custom-blue-btn"
                data-bs-toggle="modal"
                data-bs-target="#returnRequestModal"
                data-receipt="<?= $order['receipt_no'] ?>"
                data-products='<?= json_encode($order['items']) ?>'>
            Request for Refund
        </button>
    <?php endif; ?>
<?php endif; ?>

        </div>
        <div class="text-end">
            <small class="text-muted">Total Amount</small><br>
            <strong class="fs-5">₱<?= number_format($order['total'], 2) ?></strong>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
</div>


<!------------------------ MODALS ----------------------------------------------------------->
<!-- SEND E-RECEIPT MODAL-->
<div class="modal fade" id="gcashUploadModal" tabindex="-1" aria-labelledby="gcashUploadLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content gcash-modal-content">
      <div class="modal-header">
        <h4 class="modal-title d-flex align-items-center" id="gcashUploadLabel">
          <img src="./admin/images/Gcash-icon.png" alt="Receipt Icon" class="me-2 gcash-logo">
          Upload E-Receipt from GCash
        </h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body d-flex p-0">
        
        <!-- Left Side: INSTRUCTIONS -->
        <div class="instruction-box p-4">
        <ol class="ps-3 instruction-list">
            <h5 class="fw-bold content-title mb-3">Steps to Confirm Your Payment:</h5>
              <li>Scan the QR code or manually enter the phone number for the <strong>VMC Official GCash Account</strong>.</li>
              <li>Complete your payment through GCash.</li>
              <li>Take a screenshot of the E-receipt from your GCash transaction.</li>
              <li>
                Upload the screenshot directly here to confirm your payment.
                <br><small class="text-muted">• Ensure the screenshot clearly displays all important details of your payment (e.g., <em>transaction date, amount paid, reference number</em>).</small>
              </li>
              <li>This uploaded screenshot will serve as <strong>proof</strong> that your payment has been completed successfully through the online method.</li>
            </ol>
          </div>

        <!-- Right Side: GCASH QR CODE -->
            <div class="review-form flex-grow-1 p-4">
                <h5 class="fw-bold mb-3 content-title">VMC Official GCash Account:</h5>
                <img src="./admin/images/GCashAcc.jpg" alt="GCash QR Code" class="img-fluid qr-img mb-2">
            </div>
        </div>
        <hr>

        <!-- Upload Section -->
        <div class="upload-section p-4">
            <h5 class="content-title fw-bold">Upload Here</h5>
            <p class="text-muted mb-3">Select and upload (1) image</p>

            <label for="gcashReceiptInput" class="upload-box border rounded p-4 text-center position-relative d-block" id="dropArea">
                <input type="file" id="gcashReceiptInput" class="d-none" accept=".jpg,.jpeg,.png">

                <!-- This part will be hidden when image is uploaded -->
                <div id="uploadPrompt">
                <i class="bi bi-upload fs-1 mb-2"></i>
                <p class="mb-1 fw-medium">Choose a file or drag & drop it here.</p>
                <small class="text-muted">JPG, JPEG, PNG formats, up to 50MB</small><br>
                <span class="btn btn-outline-secondary mt-2">Browse File</span>
                </div>

                <!-- Preview container -->
                <div id="previewContainer" class="mt-3"></div>
            </label>
        </div>


        <hr>
        <!-- Footer -->
            <div class="modal-footer d-flex justify-content-end align-items-center">
                <button class="btn btn-outline-danger w-25" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary w-25">Send E-Receipt</button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- RETURN REFUND MODAL -->
<div class="modal fade" id="returnRequestModal" tabindex="-1" aria-labelledby="returnRequestLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content return-request-modal">
    <div class="modal-header shadow-sm">
        <h4 class="modal-title title-text fw-bold" id="returnRequestLabel">
            <img src="./admin/images/request-for-return.png" alt="Return Icon"  class="me-2" style="width: 40px; height: 40px;">
            Request for Return/Refund
        </h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
      <div class="modal-body d-flex p-0">
        
        <!-- Left Side: Product Info -->
        <div class="product-info p-4 text-start">
          <img src="./Images/41's Aniv Shirt (Front).png" alt="VMC Shirt" class="img-fluid mb-3" />
          <h5 class="mb-1">VMC 41st Anniversary Shirt</h5>
          <p class="text-muted mb-0">Size: L</p>
        </div>

        <!-- Right Side: Rating Form -->
        <div class="review-form flex-grow-1 p-4">

        <h5 class="title-text fw-semibold">Reason for Return</h5>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="returnReason1">
            <label class="form-check-label" for="returnReason1">Defective or Damage Product</label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="returnReason2">
            <label class="form-check-label" for="returnReason2">Size/Fit Issue</label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="returnReason3">
            <label class="form-check-label" for="returnReason3">Missing Parts or Accessories</label>
          </div>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="returnReasonOther">
            <label class="form-check-label" for="returnReasonOther">Others</label>
          </div>

          <textarea class="form-control mt-2" placeholder="Please state the reason."></textarea>

          <!-- Photo Upload -->
          <div class="mb-3 photo-upload-section">
            <label class="form-label title-text fw-semibold fs-5 mt-3">Add photos</label><br/>

            <!-- Flex container for previews + button -->
            <div class="d-flex align-items-start flex-wrap gap-2 photo-area">
                <div class="d-flex flex-wrap photo-preview"></div>

                <!-- Upload Button -->
                <button type="button" class="btn btn-light border photo-upload-btn">
                <i class="bi bi-image me-2"></i>Photo
                </button>
            </div>

            <input type="file" class="photo-upload-input d-none" multiple accept="image/png, image/jpeg">
            </div>

          <div class="d-flex justify-content-end mt-4">
            <button class="btn btn-danger me-2" data-bs-dismiss="modal">Cancel</button>
            <button class="custom-navy-btn">Send Request</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Rate & Review Modal -->
<div class="modal fade" id="rateReviewModal" tabindex="-1" aria-labelledby="rateReviewLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content rate-review-modal">
      <div class="modal-header shadow-sm">
        <h4 class="modal-title text-title fw-bold" id="rateReviewLabel">
          <img src="./admin/images/rate-and-review.png" alt="Review Icon" class="me-2" style="width: 40px; height: 40px;">
          Rate & Review Products
        </h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <!-- Product Reviews Container -->
      <div class="modal-body p-0">
        <div class="product-reviews-container">
          <!-- Products will be dynamically inserted here -->
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="custom-navy-btn" id="submitAllReviews">Submit Reviews</button>
      </div>
    </div>
  </div>
</div>

<!-- Template for individual product review (hidden) -->
<template id="productReviewTemplate">
  <div class="product-review-item border-bomb-3">
    <form class="review-form" data-product-id="" data-order-id="">
      <div class="d-flex p-0">
        <!-- Left Side: Product Info -->
        <div class="product-info p-4 text-center">
          <img src="" alt="Product Image" class="img-fluid mb-3 product-image" />
          <h5 class="mb-1 product-name"></h5>
          <p class="text-muted mb-0 product-size"></p>
          <div class="d-flex justify-content-center">
        <div class="form-check mt-4 d-flex align-items-center">
            <input class="form-check-input me-2" type="checkbox" name="is_anonymous" id="is_anonymous">
            <label class="form-check-label text-muted" for="is_anonymous">
            Review Anonymously
            </label>
        </div>
        </div>
    </div>

        <!-- Right Side: Rating Form -->
        <div class="review-form-content flex-grow-1 p-4">
          <!-- Star Rating -->
          <div class="mb-3">
            <label class="form-label text-title fw-semibold">Rate Product</label><br/>
            <div class="star-rating">
              <i class="bi bi-star" data-rating="1"></i>
              <i class="bi bi-star" data-rating="2"></i>
              <i class="bi bi-star" data-rating="3"></i>
              <i class="bi bi-star" data-rating="4"></i>
              <i class="bi bi-star" data-rating="5"></i>
            </div>
            <input type="hidden" name="rating" class="selected-rating" value="0">
          </div>

          <!-- Photo Upload -->
          <div class="mb-3 photo-upload-section">
            <label class="form-label text-title fw-semibold">Add photos</label><br/>
            <div class="d-flex align-items-start flex-wrap gap-2 photo-area">
              <div class="d-flex flex-wrap photo-preview"></div>
              <button type="button" class="btn btn-light border photo-upload-btn">
                <i class="bi bi-image me-2"></i>Photo
              </button>
            </div>
            <input type="file" name="review_images[]" class="photo-upload-input d-none" 
                   multiple accept="image/png, image/jpeg" data-max-files="3">
            <small class="text-muted d-block mt-1">You can upload up to 3 images</small>
          </div>

          <!-- Review Text -->
          <div class="mb-3">
            <label class="form-label text-title fw-semibold">Write your review</label>
            <textarea class="form-control" name="review_text" rows="3"></textarea>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<!-- CANCEL MODAL -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content cancel-order-modal">
      <div class="modal-header shadow-sm">
          <h4 class="modal-title text-title fw-bold" id="cancelOrderLabel">
              <img src="./admin/images/cancel-order.png" alt="Cancel Icon" class="me-2" style="width: 40px; height: 40px;">
              Cancel Order
          </h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body d-flex p-0">
        <!-- Left Side: Product Info -->
        <div class="product-info p-4 text-center"></div>

        <!-- Right: Cancel Form -->
        <div class="review-form flex-grow-1 p-4">
            <p>Are you sure you want to cancel your order? If yes, please state the reason.</p>
            <h5 class="mt-2 mb-1 text-title fw-semibold">Reason for Cancel</h5>

            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" id="cancelReason1">
              <label class="form-check-label" for="cancelReason1">Wrong Item</label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" id="cancelReason2">
              <label class="form-check-label" for="cancelReason2">Wrong Size</label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" id="cancelReason3">
              <label class="form-check-label" for="cancelReason3">Change of Mind</label>
            </div>
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" id="cancelReasonOther">
              <label class="form-check-label" for="cancelReasonOther">Others</label>
            </div>

            <textarea class="form-control mt-4" placeholder="Please state the reason."></textarea>

            <div class="d-flex justify-content-between mt-4">
              <button class="btn btn-danger w-100">Cancel Order</button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- Footer and chat-->
    <?php include 'footer.php'; ?>
    <?php include 'chat.php'; ?>



<!------------------------ JS SCRIPT ----------------------------------------------------------->
<script>
    // TAB FUNCTIONALITY
   document.addEventListener('DOMContentLoaded', function () {
    const tabButtons = document.querySelectorAll('.tab-button');
    const cards = document.querySelectorAll('.purchase-card');

    // Status mapping object
    const statusMap = {
        'All': 'All',
        'To Pay': 'Pending',
        'To Pick Up': 'ToPickUp',
        'Completed': 'Complete',
        'Cancelled': 'Cancelled',
        'Return Refund': 'Refunded'
    };

    tabButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Remove active class from all buttons
            tabButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');

            const buttonText = this.textContent.trim();
            const statusToMatch = statusMap[buttonText];

            cards.forEach(card => {
                const cardStatus = card.dataset.status;
                if (statusToMatch === 'All' || cardStatus === statusToMatch) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
});


// Unified Photo Upload Handler
class PhotoUploadHandler {
    constructor(section, maxFiles = 3) {
        this.fileInput = section.querySelector(".photo-upload-input");
        this.uploadBtn = section.querySelector(".photo-upload-btn");
        this.previewContainer = section.querySelector(".photo-preview");
        this.maxFiles = maxFiles;
        this.selectedFiles = [];
        this.allowedTypes = ["image/png", "image/jpeg"];
        
        this.init();
    }

    init() {
        this.uploadBtn.addEventListener("click", () => this.fileInput.click());
        this.fileInput.addEventListener("change", () => this.handleFileSelect());
    }

    handleFileSelect() {
        const newFiles = Array.from(this.fileInput.files);
        
        if (!this.validateFiles(newFiles)) return;
        
        this.addFiles(newFiles);
        this.fileInput.value = "";
        this.renderPreviews();
    }

    validateFiles(files) {
        for (let file of files) {
            if (!this.allowedTypes.includes(file.type)) {
                alert("Only PNG and JPEG files are allowed.");
                return false;
            }
            if (this.selectedFiles.length >= this.maxFiles) {
                alert(`You can upload a maximum of ${this.maxFiles} images.`);
                return false;
            }
        }
        return true;
    }

    addFiles(files) {
        files.forEach(file => {
            if (this.selectedFiles.length < this.maxFiles) {
                this.selectedFiles.push(file);
            }
        });
    }

    renderPreviews() {
        this.previewContainer.innerHTML = "";
        
        this.selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const preview = this.createPreviewElement(e.target.result, index);
                this.previewContainer.appendChild(preview);
            };
            reader.readAsDataURL(file);
        });

        this.uploadBtn.style.display = this.selectedFiles.length >= this.maxFiles ? "none" : "inline-block";
    }

    createPreviewElement(src, index) {
        const wrapper = document.createElement("div");
        wrapper.classList.add("position-relative");
        wrapper.style.width = "80px";
        wrapper.style.height = "80px";

        const img = document.createElement("img");
        img.src = src;
        img.classList.add("img-thumbnail");
        img.style.cssText = "object-fit: cover; width: 100%; height: 100%;";

        const closeBtn = document.createElement("button");
        closeBtn.innerHTML = "&times;";
        closeBtn.classList.add("btn", "btn-sm", "btn-light", "position-absolute");
        closeBtn.style.cssText = "top: 0; right: 0; padding: 0.25rem 0.4rem; line-height: 1;";
        closeBtn.onclick = () => {
            this.selectedFiles.splice(index, 1);
            this.renderPreviews();
        };

        wrapper.appendChild(img);
        wrapper.appendChild(closeBtn);
        return wrapper;
    }

    reset() {
        this.selectedFiles = [];
        this.fileInput.value = "";
        this.previewContainer.innerHTML = "";
        this.uploadBtn.style.display = "inline-block";
    }

    getFiles() {
        return this.selectedFiles;
    }
}

// Initialize photo upload handlers
document.addEventListener("DOMContentLoaded", function () {
    // Initialize for review modal
    const reviewPhotoUpload = new PhotoUploadHandler(
        document.querySelector("#rateReviewModal .photo-upload-section")
    );

    // Initialize for return/refund modal
    const returnPhotoUpload = new PhotoUploadHandler(
        document.querySelector("#returnRequestModal .photo-upload-section")
    );

    // Reset handlers when modals are closed
    ['rateReviewModal', 'returnRequestModal'].forEach(modalId => {
        const modal = document.getElementById(modalId);
        modal.addEventListener('hidden.bs.modal', function () {
            if (modalId === 'rateReviewModal') reviewPhotoUpload.reset();
            if (modalId === 'returnRequestModal') returnPhotoUpload.reset();
        });
    });
});

// UPLOAD G-CASH E-RECEIPT FUNCTIONALITY
document.addEventListener("DOMContentLoaded", function () {
  const fileInput = document.getElementById("gcashReceiptInput");
  const dropArea = document.getElementById("dropArea");
  const uploadPrompt = document.getElementById("uploadPrompt");
  const previewContainer = document.getElementById("previewContainer");
  const allowedExtensions = ["jpg", "jpeg", "png"];

  function resetUpload() {
    fileInput.value = "";
    previewContainer.innerHTML = "";
    uploadPrompt.style.display = "block";
  }

  function handleFile(file) {
    const fileExtension = file.name.split(".").pop().toLowerCase();

    if (!allowedExtensions.includes(fileExtension)) {
      alert("Invalid file type. Please upload a JPG, JPEG, or PNG image.");
      return;
    }

    if (file.size > 50 * 1024 * 1024) {
      alert("File is too large. Please upload an image up to 50MB.");
      return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
      uploadPrompt.style.display = "none";
      previewContainer.innerHTML = `
        <img src="${e.target.result}" class="img-fluid rounded mb-3" style="max-height: 250px;" alt="Uploaded Preview">
        <br>
        <button type="button" class="btn btn-danger btn-sm" id="removeImageBtn">Remove Image</button>
      `;
      document.getElementById("removeImageBtn").addEventListener("click", resetUpload);
    };
    reader.readAsDataURL(file);
  }

  // On input change
  fileInput.addEventListener("change", function () {
    if (fileInput.files.length > 0) {
      handleFile(fileInput.files[0]);
    }
  });

  // Drag & Drop Events
  ;["dragenter", "dragover", "dragleave", "drop"].forEach(eventName => {
    dropArea.addEventListener(eventName, e => e.preventDefault());
    dropArea.addEventListener(eventName, e => e.stopPropagation());
  });

  dropArea.addEventListener("dragover", () => dropArea.classList.add("bg-light"));
  dropArea.addEventListener("dragleave", () => dropArea.classList.remove("bg-light"));

  dropArea.addEventListener("drop", e => {
    dropArea.classList.remove("bg-light");
    const dt = e.dataTransfer;
    const files = dt.files;
    if (files.length > 0) {
      handleFile(files[0]);
    }
  });
});

// E-Receipt Upload Functionality
document.addEventListener('DOMContentLoaded', function() {
    const gcashModal = document.getElementById('gcashUploadModal');
    const fileInput = document.getElementById('gcashReceiptInput');
    const uploadPrompt = document.getElementById('uploadPrompt');
    const previewContainer = document.getElementById('previewContainer');
    
    let currentReceiptNo = null;

    // When upload button is clicked
    document.querySelectorAll('[data-bs-target="#gcashUploadModal"]').forEach(button => {
        button.addEventListener('click', function() {
            currentReceiptNo = this.getAttribute('data-receipt');
            resetUpload();
        });
    });

    // Send E-Receipt button click handler
    gcashModal.querySelector('.btn-primary').addEventListener('click', function() {
        if (!fileInput.files.length) {
            alert('Please select an e-receipt image to upload');
            return;
        }

        const formData = new FormData();
        formData.append('receipt_no', currentReceiptNo);
        formData.append('receipt_image', fileInput.files[0]);

        // Show loading state
        this.disabled = true;
        this.innerHTML = 'Uploading...';

        fetch('upload_receipt.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('E-Receipt uploaded successfully');
                location.reload(); // Refresh page to show updated status
            } else {
                alert('Error uploading e-receipt: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error uploading e-receipt');
        })
        .finally(() => {
            // Reset button state
            this.disabled = false;
            this.innerHTML = 'Send E-Receipt';
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(gcashModal);
            modal.hide();
        });
    });

    function resetUpload() {
        fileInput.value = '';
        previewContainer.innerHTML = '';
        uploadPrompt.style.display = 'block';
    }
});

// Cancel Order Functionality
document.addEventListener('DOMContentLoaded', function() {
    const cancelModal = document.getElementById('cancelOrderModal');
    let currentReceiptNo = null;

    // When cancel button is clicked, store the receipt number and update product details
    document.querySelectorAll('[data-bs-target="#cancelOrderModal"]').forEach(button => {
        button.addEventListener('click', function() {
            currentReceiptNo = this.getAttribute('data-receipt');
            const products = JSON.parse(this.getAttribute('data-products'));
            
            // Update product details in modal
            const productInfo = cancelModal.querySelector('.product-info');
            let productHTML = '';
            
            products.forEach(product => {
                productHTML += `
                    <div class="mb-4">
                        <img src="admin/${product.product_image}" alt="${product.product_name}" 
                             class="img-fluid mb-3" style="max-height: 150px;"/>
                        <h5 class="mb-1">${product.product_name}</h5>
                        <p class="text-muted mb-0">Size: ${product.size} | Qty: ${product.quantity}</p>
                        <p class="mb-0"><strong>₱${parseFloat(product.price).toFixed(2)}</strong></p>
                    </div>
                `;
            });
            
            productInfo.innerHTML = productHTML;
            
            // Reset form
            const otherReasonText = cancelModal.querySelector('textarea');
            const checkboxes = cancelModal.querySelectorAll('input[type="checkbox"]');
            otherReasonText.value = '';
            checkboxes.forEach(cb => cb.checked = false);
        });
    });

    // Handle cancel order submission
    cancelModal.querySelector('.btn-danger').addEventListener('click', function() {
        // Get selected reason
        const checkedBox = cancelModal.querySelector('input[type="checkbox"]:checked');
        if (!checkedBox) {
            alert('Please select a reason for cancellation');
            return;
        }

        const reason = checkedBox.nextElementSibling.textContent;
        const otherReason = cancelModal.querySelector('textarea').value;
        
        // Validate other reason if "Others" is selected
        if (checkedBox.id === 'cancelReasonOther' && !otherReason.trim()) {
            alert('Please provide a reason for cancellation');
            return;
        }

        // Send AJAX request
        fetch('cancel_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `receipt_no=${currentReceiptNo}&reason=${reason}&other_reason=${otherReason}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Order cancelled successfully');
                location.reload(); // Refresh page to show updated status
            } else {
                alert('Error cancelling order: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error cancelling order');
        });

        // Close modal
        const modal = bootstrap.Modal.getInstance(cancelModal);
        modal.hide();
    });

    // Make checkboxes mutually exclusive
    const checkboxes = cancelModal.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                checkboxes.forEach(cb => {
                    if (cb !== this) cb.checked = false;
                });
            }
        });
    });
});




// Rate & Review Functionality
document.addEventListener('DOMContentLoaded', function() {
    const rateReviewModal = document.getElementById('rateReviewModal');
    const productReviewsContainer = rateReviewModal.querySelector('.product-reviews-container');
    const template = document.getElementById('productReviewTemplate');

    // When "Rate & Review" button is clicked
    document.querySelectorAll('[data-bs-target="#rateReviewModal"]').forEach(button => {
        button.addEventListener('click', function() {
            const products = JSON.parse(this.getAttribute('data-products'));
            
            // Clear previous reviews
            productReviewsContainer.innerHTML = '';
            
            // Create review forms for each product
            products.forEach(product => {
                const reviewElement = createProductReviewElement(product);
                productReviewsContainer.appendChild(reviewElement);
            });

            // Initialize functionality for all new review forms
            initializeReviewForms();
        });
    });

    function createProductReviewElement(product) {
        const clone = template.content.cloneNode(true);
        const form = clone.querySelector('.review-form');
        
        // Set form data attributes
        form.dataset.productId = product.product_id;
        form.dataset.orderId = product.id;
        
        // Set product details
        form.querySelector('.product-image').src = `admin/${product.product_image}`;
        form.querySelector('.product-name').textContent = product.product_name;
        form.querySelector('.product-size').textContent = `Size: ${product.size}`;
        
        return clone;
    }

    function initializeReviewForms() {
        // Initialize star ratings
        document.querySelectorAll('.star-rating').forEach(ratingContainer => {
            const stars = ratingContainer.querySelectorAll('i');
            const ratingInput = ratingContainer.parentElement.querySelector('.selected-rating');

            stars.forEach(star => {
                star.addEventListener('click', () => {
                    ratingInput.value = star.dataset.rating;
                    updateStars(stars, ratingInput.value);
                });

                star.addEventListener('mouseover', () => {
                    updateStars(stars, star.dataset.rating);
                });

                star.addEventListener('mouseout', () => {
                    updateStars(stars, ratingInput.value);
                });
            });
        });

        // Initialize photo uploads
        document.querySelectorAll('.photo-upload-section').forEach(section => {
            const fileInput = section.querySelector('.photo-upload-input');
            const uploadBtn = section.querySelector('.photo-upload-btn');
            const previewContainer = section.querySelector('.photo-preview');

            uploadBtn.addEventListener('click', () => fileInput.click());

            fileInput.addEventListener('change', function() {
                handleFileSelect(this, previewContainer, uploadBtn);
            });
        });
    }

    function updateStars(stars, rating) {
        stars.forEach(star => {
            const starRating = star.dataset.rating;
            star.classList.toggle('bi-star-fill', starRating <= rating);
            star.classList.toggle('bi-star', starRating > rating);
            star.style.color = starRating <= rating ? '#FFC107' : '#000';
        });
    }

    function handleFileSelect(input, previewContainer, uploadBtn) {
        const files = Array.from(input.files);
        if (files.length > 3) {
            alert('Maximum 3 images allowed');
            input.value = '';
            return;
        }

        previewContainer.innerHTML = '';
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = e => {
                const preview = document.createElement('div');
                preview.className = 'position-relative';
                preview.innerHTML = `
                    <img src="${e.target.result}" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                    <button type="button" class="btn btn-sm btn-light position-absolute" style="top: 0; right: 0;">&times;</button>
                `;
                preview.querySelector('button').onclick = () => {
                    preview.remove();
                    if (previewContainer.children.length < 3) {
                        uploadBtn.style.display = 'block';
                    }
                };
                previewContainer.appendChild(preview);
            };
            reader.readAsDataURL(file);
        });

        uploadBtn.style.display = files.length >= 3 ? 'none' : 'block';
    }

    // Submit all reviews
    document.getElementById('submitAllReviews').addEventListener('click', async function() {
        const forms = productReviewsContainer.querySelectorAll('.review-form');
        let isValid = true;

        forms.forEach(form => {
            const rating = form.querySelector('.selected-rating').value;
            if (rating === '0') {
                alert('Please rate all products');
                isValid = false;
                return;
            }
        });

        if (!isValid) return;

        try {
            // Submit all reviews sequentially
            for (const form of forms) {
                const formData = new FormData();
                
                // Add basic review data
                formData.append('product_id', form.dataset.productId);
                formData.append('order_id', form.dataset.orderId);
                formData.append('rating', form.querySelector('.selected-rating').value);
                formData.append('review_text', form.querySelector('textarea[name="review_text"]').value);
                formData.append('is_anonymous', form.querySelector('input[name="is_anonymous"]').checked ? '1' : '0');

                // Add image files
                const previewContainer = form.querySelector('.photo-preview');
                const previews = previewContainer.querySelectorAll('img');
                
                const files = [];
                for (const preview of previews) {
                    try {
                        const response = await fetch(preview.src);
                        const blob = await response.blob();
                        const file = new File([blob], `review_image_${files.length}.jpg`, { type: 'image/jpeg' });
                        formData.append('review_images[]', file);
                    } catch (error) {
                        console.error('Error processing image:', error);
                    }
                }

                try {
                    const response = await fetch('submit_review.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    if (!result.success) {
                        throw new Error(result.message || 'Error submitting review');
                    }
                } catch (error) {
                    throw new Error(`Failed to submit review: ${error.message}`);
                }
            }

            // If we get here, all reviews were submitted successfully
            alert('All reviews submitted successfully');
            location.reload();
        } catch (error) {
            console.error('Error:', error);
            alert(error.message);
        }
    });
});

// Add this to your existing JavaScript code section
document.addEventListener('DOMContentLoaded', function() {
    // Handle Pick-up confirmation
    document.querySelectorAll('.pickup-btn').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Confirm that you have picked up this order?')) {
                const receiptNo = this.getAttribute('data-receipt');
                
                // Send AJAX request to update order status
                fetch('update_order_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `receipt_no=${receiptNo}&status=Complete`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Order status updated successfully');
                        location.reload(); // Refresh page to show updated status
                    } else {
                        alert('Error updating order status: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating order status');
                });
            }
        });
    });
});

// Add this to your existing JavaScript code
function calculateAverageRating(productId) {
    fetch(`get_product_rating.php?product_id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update rating display where needed
                const ratingDisplays = document.querySelectorAll(`.product-rating[data-product-id="${productId}"]`);
                ratingDisplays.forEach(display => {
                    updateRatingDisplay(display, data.rating);
                });
            }
        });
}

function updateRatingDisplay(element, rating) {
    const fullStars = Math.floor(rating);
    const hasHalfStar = (rating - fullStars) >= 0.5;
    let html = '';

    // Add full stars
    for (let i = 0; i < fullStars; i++) {
        html += '<i class="bi bi-star-fill text-warning"></i>';
    }

    // Add half star if applicable
    if (hasHalfStar) {
        html += '<i class="bi bi-star-half text-warning"></i>';
    }

    // Add empty stars
    const remainingStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
    for (let i = 0; i < remainingStars; i++) {
        html += '<i class="bi bi-star text-warning"></i>';
    }

    html += `<span class="ms-1">(${rating.toFixed(1)})</span>`;
    element.innerHTML = html;
}
//Collapse Search for small device Script
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