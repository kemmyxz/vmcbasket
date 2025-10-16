<?php
// Turn off error output for AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ini_set('display_errors', 0);
    header('Content-Type: application/json');
}

session_start();
require('admin/inc/config.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo json_encode(['success' => false, 'error' => 'User not logged in']);
        exit;
    } else {
        header('Location: login.php');
        exit;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    if (!isset($_SESSION['temp_order'])) {
      throw new Exception('No order data found');
    }

    $temp_order = $_SESSION['temp_order'];
    $total_price = $temp_order['price'] * $temp_order['quantity'];

    // Fetch user information
    $stmt = $conn->prepare("SELECT student_fname, student_mname, student_lname, 
                              student_no, email, phone_number 
                       FROM users 
                       WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
      throw new Exception('User information not found');
    }

    // Create customer name
    $customer_name = $user['student_fname'] . ' ' .
      ($user['student_mname'] ? $user['student_mname'] . ' ' : '') .
      $user['student_lname'];

    // Begin transaction
    $conn->begin_transaction();

    // Generate a unique receipt number
    do {
      $receipt_no = 'ORD-' . time() . '-' . $_SESSION['user_id'];
      
      // Check if receipt_no already exists
      $check = $conn->prepare("SELECT receipt_id FROM order_receipt WHERE receipt_id = ?");
      $check->bind_param("s", $receipt_no);
      $check->execute();
      $exists = $check->get_result()->num_rows > 0;
      $check->close();
    } while ($exists);

    // Insert into order_receipt first
    $stmt = $conn->prepare("INSERT INTO order_receipt (receipt_id, order_status) VALUES (?, 'Pending')");
    $stmt->bind_param("s", $receipt_no);

    if (!$stmt->execute()) {
      throw new Exception('Failed to create order receipt');
    }

    // Insert into orders table
    $stmt = $conn->prepare("
            INSERT INTO orders (
                receipt_no,
                product_name,
                quantity,
                customer_name,
                school_id,
                email,
                phone,
                price,
                order_date,
                status,
                user_id,
                product_id,
                size,
                total_price,
                image,
                payment_method
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?,
                CURRENT_DATE,
                'Pending',
                ?, ?, ?, ?,
                ?, ?
            )
        ");

   
    $size = 'N/A';
    if ($product_type === 'Uniform') {
        if (empty($temp_order['size']) || $temp_order['size'] === 'N/A') {
            echo json_encode(['success' => false, 'error' => 'Please select a size for uniform products.']);
            exit;
        }
        $size = $temp_order['size'];
    } else {
        $size = 'N/A';
    }

    $payment_method = $_POST['payment_method'] ?? 'Cash (Pay at the Counter)';


    $stmt->bind_param(
        "ssissssdiiidss",
        $receipt_no,
        $temp_order['product_name'],
        $temp_order['quantity'],
        $customer_name,
        $user['student_no'],    // school_id
        $user['email'],         // email
        $user['phone_number'],  // phone
        $temp_order['price'],
        $_SESSION['user_id'],
        $temp_order['product_id'],
        $size,                  // <-- this inserts the size!
        $total_price,
        $temp_order['image'],
        $payment_method
    );
    if (!$stmt->execute()) {
      throw new Exception('Failed to create order: ' . $stmt->error);
    }

    $conn->commit();

    // Store receipt number and full order details for order completion page
    $_SESSION['last_receipt_no'] = $receipt_no;
    $_SESSION['checkout_products'] = [[
        'product_id'    => $temp_order['product_id'],
        'product_name'  => $temp_order['product_name'],
        'size'          => $size,
        'quantity'      => $temp_order['quantity'],
        'price'         => $temp_order['price'],
        'image'         => $temp_order['image'],
        'payment_method'=> $payment_method,
        'total_price'   => $total_price,
        'customer_name' => $customer_name,
        'order_date'    => date('Y-m-d'),
        'receipt_no'    => $receipt_no
    ]];

    echo json_encode([
        'success' => true,
        'receipt_no' => $receipt_no
    ]);

  } catch (Exception $e) {
    if ($conn && $conn->connect_error === false) {
      $conn->rollback();
    }
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
  }
  exit;
}

// Check if temp_order is set
if (!isset($_SESSION['temp_order'])) {
  header('Location: product_details.php');
  exit;
}

$temp_order = $_SESSION['temp_order'];
$product_id = $temp_order['product_id'];

// Fetch product type
$stmt = $conn->prepare("SELECT type FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product_type = $stmt->get_result()->fetch_assoc()['type'];

// Calculate total
$total = floatval($temp_order['price']) * intval($temp_order['quantity']);
$totalItems = intval($temp_order['quantity']); // Add this line

// Fetch user information
$stmt = $conn->prepare("SELECT student_fname, student_mname, student_lname, 
                              student_no, email, phone_number 
                       FROM users 
                       WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

if (!$user_data) {
  echo json_encode(['success' => false, 'error' => 'User data not found']);
  exit;
}

// Create customer name from user data
$customer_name = $user_data['student_fname'] . ' ' .
  ($user_data['student_mname'] ? $user_data['student_mname'] . ' ' : '') .
  $user_data['student_lname'];




?>




<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VMC Basket- Order Details</title>
  <?php include 'links.php'; ?>

  <style>
    .header-order {
      background-color: #003153;
      color: white;
      padding: 0.5rem 1rem;
      font-size: 0.9rem;
      text-align: center;
    }

    .brand {
      display: flex;
      align-items: center;
      padding: 1rem;
      background-color: #ffffff;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .brand img {
      height: 50px;
    }

    .section {
      background: linear-gradient(180deg, #F7FBFE 1.92%, #E4EFF9 100%);
      border: 1px solid black;
      padding: 1rem;
      margin-bottom: 1rem;
      border-radius: 8px;
    }

    .table-section {
      background: #F7FAFE;
      border: 1px solid black;
      padding: 1rem;
      margin-bottom: 1rem;
      border-radius: 8px;
    }

    .product-img {
      height: 60px;
      width: auto;
    }

    .btn-primary {
      background-color: #2d8dd6;
      border: none;
    }

    .btn-primary:hover {
      background-color: #2275b4;
    }

    .btn-outline-danger {
      border-radius: 8px;
    }

    .summary-table th,
    .summary-table td {
      background: #F7FAFE;
      vertical-align: middle;
      border-bottom: 1px solid black;
    }

    .total {
      font-weight: bold;
      color: #2d8dd6;
    }

    .order-line {
      border: 1px solid #000000;
      opacity: 0.3;
      margin-top: 5px;
      margin-bottom: 5px;
    }

    .btn-order {
      background-color: #0d6efd;
      color: white;
      border-radius: 8px;
    }

    .btn-order:hover {
      background-color: #0056b3;
      color: white;
    }

    .btn-cancel {
      border-radius: 8px;
    }

    .order-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 1.5rem;
    }

    .btn-circle {
      border-radius: 50%;
      border: 1px solid black;
      width: 50px;
      height: 50px;
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: black;
      color: white;
      transition: 0.3s ease;
    }

    .online-note {
      background-color: #ffffff;
      border-left: 4px solid #2d8dd6;
      padding: 1rem;
      margin-top: 1rem;
      display: none;
      border-radius: 4px;
      margin-bottom: 30px;
      width: 100%;
    }

    .payment-icon {
      height: 50px;
      width: 50px;
      border-radius: 50%;
    }

    .radio-bordered {
      border: 1px solid black !important;
      box-shadow: 0 0 0 2px #e4eff9;
      border-radius: 50%;
      width: 1.2em;
      height: 1.2em;
    }

    .radio-bordered:checked {
      border-color: #0d6efd !important;
      box-shadow: 0 0 0 2px #b6d4fe;
    }

    .qr-img {
      max-width: 500px;
      max-height: 500px;
      object-fit: cover;

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

    @media (max-width: 991.98px) {

      .highlight-blue {
        font-size: 1.5rem;
      }


    }

    @media (max-width: 767.98px) {
      .highlight-blue {
        font-size: 1.2rem;
      }

      .summary-table th,
      .summary-table td {
        font-size: 0.75rem;
      }

      .section,
      .table-section {
        font-size: 0.8rem;
      }

      .payment-method {
        font-size: 0.8rem;
      }

      .payment-icon {
        width: 40px;
        height: 40px;
      }

    }

    @media (max-width: 575.98px) {
      .highlight-blue {
        font-size: 1.2rem;
      }

      .summary-table th,
      .summary-table td {
        font-size: 0.65rem;
      }

      .section,
      .table-section {
        font-size: 0.65rem;
      }

      .payment-method {
        display: none;
        margin-right: 10px;
      }

      .payment-icon {
        width: 35px;
        height: 35px;
      }

      .qr-img {
        max-width: 300px;
        max-height: 300px;
      }

      .btn-outline-secondary {
        font-size: 0.8rem;
        padding: 0.25rem 0.5rem;
      }

    }
  </style>

</head>

<body>

  <div class="header-order">
    All products are available for pick-up only at Villagers Montessori College
  </div>

  <div class="brand d-flex justify-content-center border-bottom shadow-sm">
    <img src="admin/images/vmc_basket_logo.png" alt="Logo">
  </div>

  <div class="container p-5">

    <!-- Return Button -->
    <div class="order-header">
      <a href="purchase_history.php" class="btn btn-circle">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left"
          viewBox="0 0 16 16">
          <path fill-rule="evenodd"
            d="M15 8a.5.5 0 0 1-.5.5H2.707l4.147 4.146a.5.5 0 0 1-.708.708l-5-5a.5.5 0 0 1 0-.708l5-5a.5.5 0 1 1 .708.708L2.707 7.5H14.5A.5.5 0 0 1 15 8z" />
        </svg>
      </a>
      <h2>
        <span class="highlight-blue">Order Details</span>
      </h2>
    </div>
    <!-- Order Details -->
    <div class="table-section card">
      <div class="table-responsive">
        <table class="table summary-table">
          <thead class="table-headerbg">
            <tr>
              <th>Product Details</th>
              <th class="text-center">Unit Price</th>
              <th class="text-center">Quantity</th>
              <th class="text-end">Item Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <div class="d-flex align-items-center">
                  <img src="./admin/<?= htmlspecialchars($temp_order['image']) ?>" class="product-img me-2"
                    alt="Product" />
                  <div>
                    <strong><?= htmlspecialchars($temp_order['product_name']) ?></strong><br />
                    <?php if ($product_type === 'Uniform' && isset($temp_order['size'])): ?>
                        <span class="text-muted" style="font-size: 0.85rem;">Size: <?= htmlspecialchars($temp_order['size']) ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
              <td class="text-center">₱ <?= number_format($temp_order['price'], 2) ?></td>
              <td class="text-center"><?= $temp_order['quantity'] ?></td>
              <td class="text-end">₱ <?= number_format($total, 2) ?></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="text-end pe-2 text-muted">
        Order Total (<span id="total-items"><?php echo $totalItems; ?></span> items):
        <span class="order-title ms-2" id="order-total">₱ <?php echo number_format($total, 2); ?></span>
      </div>
    </div>

    <!-- Payment Method -->
    <div class="card p-4 section">
      <h4 class="title-text fw-bold mb-3">Payment Method</h4>
      <div class="d-flex flex-column justify-content-start align-items-start mb-2">
        <div class="form-check custom-radio mb-2 d-flex align-items-center">
          <input class="form-check-input me-2 radio-bordered" type="radio" name="paymentMethod" id="online"
            value="Send Online Receipt" <?php echo isset($orderData['payment_method']) && $orderData['payment_method'] === 'Send Online Receipt' ? 'checked' : ''; ?>
            onclick="toggleNote(true); updatePaymentMethod(this.value)">
          <label class="form-check-label d-flex align-items-center" for="online">
            <img src="./admin/images/Gcash-icon.png" alt="Receipt Icon" class="payment-icon">
            <span class="ms-2">Send Online Receipt</span>
          </label>
        </div>

        <!-- ONLINE NOTE WITH UPLOAD SECTION -->
        <div id="onlineNote" class="online-note">
          <div class="d-flex flex-wrap justify-content-center text-center align-items-center gap-2">
            <!-- Left Side: INSTRUCTIONS -->
            <div class="p-3 flex-fill d-flex flex-column align-items-center">
              <img src="./admin/images/GCash-Instruction.png" alt="GCash Instruction"
                class="img-fluid qr-img mb-2 gcash-img-same-size">
            </div>

            <!-- Right Side: GCASH QR CODE -->
            <div class="review-form flex-fill p-3 d-flex flex-column align-items-center">
              <h5 class="fw-bold mb-3 content-title">VMC Official GCash Account:</h5>
              <img src="./admin/images/GCashAcc.jpg" alt="GCash QR Code"
                class="img-fluid qr-img mb-2 gcash-img-same-size">
            </div>
          </div>
          <hr>

          <!-- Upload Section -->
          <div class="upload-section p-3">
            <h5 class="content-title fw-bold">Upload Here</h5>
            <p class="text-muted mb-3">Select and upload (1) image</p>

            <label for="gcashReceiptInput" class="upload-box border rounded p-4 text-center position-relative d-block"
              id="dropArea">
              <input type="file" id="gcashReceiptInput" class="d-none" accept=".jpg,.jpeg,.png">

              <!-- Upload Prompt -->
              <div id="uploadPrompt">
                <i class="bi bi-upload fs-1 mb-2"></i>
                <p class="mb-1 fw-medium">Choose a file or drag & drop it here.</p>
                <small class="text-muted">JPG, JPEG, PNG formats</small><br>
                <span class="btn btn-outline-secondary mt-2">Browse File</span>
              </div>

              <!-- Preview container -->
              <div id="previewContainer" class="mt-3"></div>
            </label>
          </div>
        </div>
        <div class="form-check custom-radio d-flex align-items-center mt-1">
          <input class="form-check-input me-2 radio-bordered" type="radio" name="paymentMethod" id="cash"
            value="Cash (Pay at the Counter)" <?php echo !isset($orderData['payment_method']) || $orderData['payment_method'] === 'Cash (Pay at the Counter)' ? 'checked' : ''; ?>
            onclick="toggleNote(false); updatePaymentMethod(this.value)">
          <label class="form-check-label d-flex align-items-center" for="cash">
            <img src="./admin/images/Cash.png" alt="Cash Icon" class="payment-icon">
            <span class="ms-2">Cash (Pay at the Counter)</span>
          </label>
        </div>
      </div>


      <hr class="order-line">
      <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <div>
          <img src="./admin/images/Cash.png" alt="Payment Icon" class="payment-icon" id="paymentMethodIcon">
          <span id="paymentMethodText" class="payment-method">Cash (Pay at the Counter)</span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <div class="text-muted me-5 ms-2">Total Payment:</div>
          <h3 class="fw-bold">₱<?php echo number_format($total, 2); ?></h3>
        </div>
      </div>

      <hr class="order-line">

      <div class="d-flex justify-content-end mt-2">
          <button class="btn btn-outline-danger me-2 btn-cancel" onclick="window.history.back()">Cancel</button>
          <button class="custom-navy-btn" type="button" id="proceedOrderBtn">Proceed to Order</button>
      </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Payment method toggle function
        window.toggleNote = function(show) {
            const onlineNote = document.getElementById('onlineNote');
            const paymentMethodIcon = document.getElementById('paymentMethodIcon');
            const paymentMethodText = document.getElementById('paymentMethodText');
            if (!onlineNote || !paymentMethodIcon || !paymentMethodText) return;
            onlineNote.style.display = show ? 'block' : 'none';
            paymentMethodIcon.src = show ? './admin/images/Gcash-icon.png' : './admin/images/Cash.png';
            paymentMethodText.textContent = show ? 'Send Online Receipt' : 'Cash (Pay at the Counter)';
        };
    
        // Initial payment method setup
        if (document.getElementById('online').checked) {
            toggleNote(true);
        }
    
        // Proceed to order button logic
        const proceedButton = document.getElementById('proceedOrderBtn');
        if (proceedButton) {
            proceedButton.addEventListener('click', async function(e) {
                e.preventDefault();
                try {
                    const onlinePaymentSelected = document.getElementById('online').checked;
                    const hasUploadedReceipt = document.querySelector('#previewContainer img') !== null;
    
                    if (onlinePaymentSelected && !hasUploadedReceipt) {
                        alert('Please upload your GCash e-receipt before proceeding with the order.');
                        return;
                    }
    
                    // Submit order (POST to self)
                    const orderResponse = await fetch(window.location.href, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            payment_method: onlinePaymentSelected ? 'Send Online Receipt' : 'Cash (Pay at the Counter)'
                        })
                    });
                    const orderResult = await orderResponse.json();
                    if (!orderResult.success) throw new Error(orderResult.error || 'Failed to process order');
    
                    // Upload receipt if needed
                    if (onlinePaymentSelected && hasUploadedReceipt) {
                        const receiptFile = document.querySelector('#gcashReceiptInput').files[0];
                        if (receiptFile) {
                            const uploadFormData = new FormData();
                            uploadFormData.append('receipt_image', receiptFile);
                            uploadFormData.append('receipt_no', orderResult.receipt_no);
    
                            const uploadResponse = await fetch('upload_receipt.php', {
                                method: 'POST',
                                body: uploadFormData
                            });
                            const uploadResult = await uploadResponse.json();
                            if (!uploadResult.success) throw new Error(uploadResult.error || 'Failed to upload receipt');
                        }
                    }
    
                    // Disable button to prevent double submission
                    this.disabled = true;
                    window.location.replace('order_complete.php');
                } catch (error) {
                    alert('Error processing order: ' + error.message);
                    console.error('Order processing error:', error);
                }
            });
        }
    });

</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
      crossorigin="anonymous"></script>
</body>

</html>