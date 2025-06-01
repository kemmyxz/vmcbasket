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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"><link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        /* General Card Styling */
        .card {
        background-color: #E8EDEF;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        border: none;
        }

        .card hr{
            border: 1px solid #00527F;
        }

        /* Status Section */
        .card .d-flex.align-items-center img:first-child {
        margin-right: 8px;
        }

        .card strong {
        font-weight: 600;
        font-size: 15px;
        }

        /* Product Image and Info */
        .card img {
        border-radius: 8px;
        }

        .card .d-flex.align-items-center.mb-2 img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        }

        /* Text Info */
        .card p {
        font-size: 14px;
        font-weight: 500;
        }

        .card small {
        font-size: 13px;
        color: #666;
        }

        /* Buttons */
        .card button {
        font-size: 13px;
        padding: 8px 24px;
        font-weight: 500;
        }

        /* Totals */
        .card .text-end {
        font-size: 14px;
        font-weight: 600;
        color: #222;
        }

        /* Border Styling for Special Statuses */
        .border-danger {
        border-left: 5px solid #dc3545 !important;
        }

        .border-info {
        border-left: 5px solid #0dcaf0 !important;
        }

        .tab-button {
            border: none;
            background-color: #E8EDEF;
            font-weight: 400;
            padding: 20px 30px;
            color: #000000;
            text-align: center;
        }
        .tab-button.active {
            border: 2px solid #3D87F5;
            color: #00527F;
            background-color: #A9CEEA;
        }
        .tab-button:hover {
            background-color: #D1E7F5;
            color: #00527F;
            cursor: pointer;
        }

        /* Modal Styling */
        .rate-review-modal {
        border-radius: 12px;
        overflow: hidden;
        }

        /* Left side panel */
        .product-info {
            width: 40%;
            background-color: #D5E9F3;
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

        .title-part{
           font-family: 'Ubuntu', sans-serif;
           color: #00527F;
        }

        .content-title{
            color: #00527F;
            font-weight: 500;
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

        .gcash-modal-content {
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .gcash-logo {
            height: 40px;
            width: 40px;
            border-radius: 50%;
        }

        .qr-img {
            max-width: 300px;
            border-radius: 8px;
        }

        .instruction-box {
            width: 60%;
            background-color: #D5E9F3;
        }

        .instruction-list li {
            margin-bottom: 0.75rem;
            font-size: 15px;
            line-height: 1.5;
        }
        
        .upload-box {
            border-style: dashed;
            border-color: #ccc;
            border-width: 2px;
            border-radius: 10px;
            background-color: #f9f9f9;
            cursor: pointer;
            transition: background 0.3s;
        }

        .upload-box:hover {
            background-color: #f0f8ff;
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

        .pagination .page-link {
        color: #00527F;
        background-color: #fff;
        border-color: #D5E9F3;
        }

        .pagination .page-item.active .page-link {
            background-color: #00527F;
            border-color: #00527F;
            color: #fff;
        }

        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            background-color: #fff;
            border-color: #dee2e6;
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
                <input type="text" class="form-control" placeholder="">
                <button><img src="admin/images/search-icon.png" alt="Search"></button>
            </div>
        </div>
    </div>

    <!-- My Profile Side-bar-->
    <div class="d-flex">
         <div class="sidebar ms-5 ">
            <!-- USER PROFILE PIC AND NAME-->
            <h5 style="font-size: 16px;">
            <!-- Display the Profile Image -->
                <img src= "admin/uploads/<?= $profilePic ?>" class="rounded-circle mb-3" width="120" id="profileImage" name="profileImage" alt="profile">

                <br>    
                <?php echo htmlspecialchars($fullName); ?>
               
                
            </h5>


             <!-- SIDE-BAR-->
            <div class="mt-4 mb-4">
                <button class="btn w-100 text-start btn-active d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#accountMenu" aria-expanded="true">
                    <img src="admin/images/profile_pic.png" alt="profile" width="20" class="me-2">
                    My profile
                </button>
                <div id="accountMenu" class="collapse show mb-3 mt-1">
                    <a href="forgot_pass.php" class="d-block text-decoration-none ps-3 text-muted mb-3"><img src="admin/images/locked.png" alt="profile" width="20" class="me-2">Change Password</a>
                </div>
                <a href="#" class="d-block text-decoration-none mb-3 mt-3"><img src="admin/images/bill.png" alt="profile" width="20" class="me-2">My Purchase</a>
                <a href="logout.php" class="d-block text-decoration-none"><img src="admin/images/logout.png" alt="profile" width="20" class="me-2">Log out</a>
            </div>
        </div>

     <!-- Purchase History -->
     <div class="purchase-history container mt-3 mb-5">

    <!-- Tabs -->
    <div class="d-flex mb-3">
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
                    <img src="./admin/images/wallet.png" alt="Box" width="25" class="me-2">
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
                                <button class="btn btn-outline-primary btn-sm me-2" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#gcashUploadModal"
                                        data-receipt="<?= $order['receipt_no'] ?>"
                                        data-products='<?= json_encode($order['items']) ?>'>
                                    Upload E-Receipt
                                </button>
                                <button class="btn btn-outline-danger btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#cancelOrderModal" 
                                        data-receipt="<?= $order['receipt_no'] ?>"
                                        data-products='<?= json_encode($order['items']) ?>'>
                                    Cancel Order
                                </button>
                            <?php else: ?>
                                <span class="text-muted">
                                    <i class="bi bi-clock"></i> Waiting to confirm payment...
                                </span>
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
                            <button class="btn btn-outline-primary btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#rateReviewModal"
                                    data-receipt="<?= $order['receipt_no'] ?>"
                                    data-products='<?= json_encode($order['items']) ?>'>
                                Rate & Review
                            </button>
                        <?php else: ?>
                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                Already Reviewed
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

    <!-- Pagination -->
    <div aria-label="Page navigation example" class="mt-4">
        <ul class="pagination d-flex justify-content-end">
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
    </div>

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
    <div class="modal-header">
        <h4 class="modal-title title-part" id="returnRequestLabel">
            <img src="./admin/images/request-for-return.png" alt="Return Icon"  class="me-2" style="width: 40px; height: 40px;">
            Request for Return/Refund
        </h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
      <div class="modal-body d-flex p-0">
        
        <!-- Left Side: Product Info -->
        <div class="product-info p-4 text-center">
          <img src="./Images/41's Aniv Shirt (Front).png" alt="VMC Shirt" class="img-fluid mb-3" />
          <h5 class="mb-1">VMC 41st Anniversary Shirt</h5>
          <p class="text-muted mb-0">Size: L</p>
        </div>

        <!-- Right Side: Rating Form -->
        <div class="review-form flex-grow-1 p-4">

        <h5 class="content-title">Reason for Return</h5>
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
            <label class="form-label content-title fs-5 mt-3">Add photos</label><br/>

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

          <div class="d-flex justify-content-between mt-4">
            <button class="btn btn-outline-danger me-2" data-bs-dismiss="modal">Cancel</button>
            <button class="btn btn-primary">Send Request</button>
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
      <div class="modal-header">
        <h4 class="modal-title title-part" id="rateReviewLabel">
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
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary px-4" id="submitAllReviews">Submit All Reviews</button>
      </div>
    </div>
  </div>
</div>

<!-- Template for individual product review (hidden) -->
<template id="productReviewTemplate">
  <div class="product-review-item border-bottom mb-3">
    <form class="review-form" data-product-id="" data-order-id="">
      <div class="d-flex p-0">
        <!-- Left Side: Product Info -->
        <div class="product-info p-4 text-center" style="width: 40%; background-color: #D5E9F3;">
          <img src="" alt="Product Image" class="img-fluid mb-3 product-image" />
          <h5 class="mb-1 product-name"></h5>
          <p class="text-muted mb-0 product-size"></p>
          <div class="form-check mt-4 d-flex align-items-center justify-content-center">
            <input class="form-check-input me-2" type="checkbox" name="is_anonymous">
            <label class="form-check-label text-muted ms-1">
              Review Anonymously
            </label>
          </div>
        </div>

        <!-- Right Side: Rating Form -->
        <div class="review-form-content flex-grow-1 p-4">
          <!-- Star Rating -->
          <div class="mb-3">
            <label class="form-label content-title">Rate Product</label><br/>
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
            <label class="form-label content-title">Add photos</label><br/>
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
            <label class="form-label content-title">Write your review</label>
            <textarea class="form-control" name="review_text" rows="3"></textarea>
          </div>
        </div>
      </div>
    </form>
  </div>
</template>

<!-- CANCELLED MODAL -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content cancel-order-modal">
      <div class="modal-header">
          <h4 class="modal-title title-part" id="cancelOrderLabel">
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
            <h5 class="content-title mt-2 mb-1">Reason for Cancel</h5>

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



<!------------------------ FOOTER ----------------------------------------------------------->

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
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>