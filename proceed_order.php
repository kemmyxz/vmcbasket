<?php
require 'admin/inc/config.php';
session_start();

// Get products data
function getProducts() {
    global $conn;
    
    if (!isset($_SESSION['selected_items']) || empty($_SESSION['selected_items'])) {
        return ['error' => 'No items selected'];
    }

    if (!isset($_SESSION['user_id'])) {
        return ['error' => 'User not logged in'];
    }

    $products = [];
    $basketIds = array_map('intval', $_SESSION['selected_items']);
    $placeholders = str_repeat('?,', count($basketIds) - 1) . '?';
    
       
    $sql = "SELECT b.id, b.product_id, b.product_name, b.quantity, b.price, b.size, 
            b.image, p.product_name as original_name, p.type as product_type,
            u.student_fname, u.student_lname, u.student_mname, u.student_no,
            u.email, u.phone_number, u.year_level
            FROM basket b 
            JOIN products p ON b.product_id = p.id 
            JOIN users u ON b.user_id = u.id
            WHERE b.id IN ($placeholders) AND b.user_id = ?";
            
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return ['error' => $conn->error];
    }

    // Add user_id to the parameters
    $params = $basketIds;
    $params[] = $_SESSION['user_id'];
    $types = str_repeat('i', count($basketIds)) . 'i';
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        // Get the first row for user information
        $firstRow = $result->fetch_assoc();
        
        // Store user information in session
        $_SESSION['order_user_info'] = [
            'full_name' => $firstRow['student_fname'] . ' ' . 
                         $firstRow['student_mname'] . ' ' . 
                         $firstRow['student_lname'],
            'student_no' => $firstRow['student_no'],
            'email' => $firstRow['email'],
            'phone_number' => $firstRow['phone_number'],
            'year_level' => $firstRow['year_level']
        ];

        // Reset result pointer
        $result->data_seek(0);
        
        // Process products
        while ($row = $result->fetch_assoc()) {
            $row['total_price'] = $row['price'] * $row['quantity'];
            $products[] = $row;
        }
        
        // Store complete product details in session
        $_SESSION['checkout_products'] = $products;
    }

    return $products;
}

// Get the products before HTML output
$products = getProducts();
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

    .payment-icon{
      height: 50px; 
      width: 50px; 
      border-radius: 100%;
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

    .section, .table-section{
      font-size: 0.8rem;
    }
    .payment-method{
      font-size: 0.8rem;
    }
    .payment-icon{
      width:40px;
      height:40px;
    }
       
    }
    @media (max-width: 575.98px) {
      .highlight-blue {
          font-size: 1.2rem;
      }
      .summary-table th {
        font-size: 0.7rem;
      }
      .summary-table td {
          font-size: 0.6rem;
      }

      .section, .table-section{
        font-size: 0.65rem;
      }
      .payment-method{
        display: none;
        margin-right: 10px;
      }

      .payment-icon{
        width:35px;
        height:35px;
      }

      .qr-img {
        max-width: 300px;
        max-height: 300px;
      }

      .btn-outline-secondary{
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
        <a href="basket.php" class="btn btn-circle">
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
            <?php if (isset($products['error'])): ?>
                <tr>
                    <td colspan="4" class="text-danger">Error: <?php echo htmlspecialchars($products['error']); ?></td>
                </tr>
            <?php elseif (!empty($products)): ?>
                <?php
                $total = 0;
                $totalItems = 0;
                ?>
                <?php foreach ($products as $product): ?>
                    <?php
                    $subtotal = $product['price'] * $product['quantity'];
                    $total += $subtotal;
                    $totalItems += $product['quantity'];
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="./admin/<?php echo htmlspecialchars($product['image']); ?>" 
                                     class="product-img me-2" 
                                     alt="Product" />
                                <div>
                                    <strong><?php echo htmlspecialchars($product['product_name']); ?></strong><br />
                                    <?php if ($product['product_type'] === 'Uniform' && !empty($product['size']) && $product['size'] !== 'N/A'): ?>
                                        <small class="text-muted">Size: <?php echo htmlspecialchars($product['size']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">₱ <?php echo number_format($product['price'], 2); ?></td>
                        <td class="text-center"><?php echo $product['quantity']; ?></td>
                        <td class="text-end">₱ <?php echo number_format($subtotal, 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No items selected for purchase.</td>
                </tr>
            <?php endif; ?>
          </tbody>

        </table>
      </div>
      <div class="text-end pe-2 text-muted">
        Order Total (<span id="total-items"><?php echo $totalItems; ?></span> items):
        <span class="text-title fw-bold ms-2" id="order-total">₱ <?php echo number_format($total, 2); ?></span>
      </div>
    </div>

    <!-- Payment Method -->
    <div class="card p-4 section">
    <h4 class="title-text fw-bold mb-3">Payment Method</h4>
    <div class="d-flex flex-column justify-content-start align-items-start mb-2">
    <div class="form-check custom-radio mb-2 d-flex align-items-center">
        <input class="form-check-input me-2 radio-bordered" type="radio" name="paymentMethod" id="online" 
        value="Send Online Receipt"
        <?php echo isset($orderData['payment_method']) && $orderData['payment_method'] === 'Send Online Receipt' ? 'checked' : ''; ?>
        onclick="toggleNote(true); updatePaymentMethod(this.value)">
        <label class="form-check-label d-flex align-items-center" for="online">
            <img src="./admin/images/Gcash-icon.png" alt="Receipt Icon" class="payment-icon">
            <span class="ms-2">Send Online Receipt</span>
        </label>
    </div>

    <!-- ONLINE NOTE WITH UPLOAD SECTION -->
    <div id="onlineNote" class= "online-note">
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

                <label for="gcashReceiptInput" class="upload-box border rounded p-4 text-center position-relative d-block" id="dropArea">
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
                value="Cash (Pay at the Counter)"
                <?php echo !isset($orderData['payment_method']) || $orderData['payment_method'] === 'Cash (Pay at the Counter)' ? 'checked' : ''; ?>
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
          <a href="basket.php" class="btn btn-outline-danger me-2 btn-cancel">Cancel</a>
          <a href="order_complete.php">
              <button class="custom-navy-btn" type="submit">Proceed to Order</button>
          </a>
      </div>
  </div>

<script>
  // Function to toggle the note display
  function toggleNote(show) {
    const note = document.getElementById('onlineNote');
    note.style.display = show ? 'block' : 'none';
  }

  // Function to update payment method display
  function updatePaymentMethod(method) {
    const paymentMethodText = document.getElementById('paymentMethodText');
    const paymentMethodIcon = document.getElementById('paymentMethodIcon');
    const selectedMethod = method.trim();

    // Update UI
    if (selectedMethod === 'Send Online Receipt') {
      paymentMethodText.textContent = selectedMethod;
      paymentMethodIcon.src = './admin/images/Gcash-icon.png';
    } else {
      paymentMethodText.textContent = selectedMethod;
      paymentMethodIcon.src = './admin/images/Cash.png';
    }

    // Send AJAX request to update payment method
    fetch('update_payment_method.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'payment_method=' + encodeURIComponent(selectedMethod)
    })
    .then(response => response.json())
    .then(data => {
      if (!data.success) {
        console.error('Failed to update payment method:', data.error);
      }
    })
    .catch(error => {
      console.error('Error:', error);
    });
  }

  // Add validation before form submission
document.querySelector('a[href="order_complete.php"]').addEventListener('click', async function(e) {
  e.preventDefault();

  try {
  const onlinePaymentSelected = document.querySelector('input[name="paymentMethod"][value="Send Online Receipt"]').checked;
  const hasUploadedReceipt = document.querySelector('#previewContainer img') !== null;

  if (onlinePaymentSelected && !hasUploadedReceipt) {
    showBootstrapAlert('Please upload your GCash e-receipt before proceeding with the order.', 'warning');
    return;
  }

  let formData = new FormData();
  formData.append('payment_method', onlinePaymentSelected ? 'Send Online Receipt' : 'Cash (Pay at the Counter)');

  // If online payment, append receipt file
  if (onlinePaymentSelected && hasUploadedReceipt) {
    const receiptFile = document.querySelector('#gcashReceiptInput').files[0];
    if (receiptFile) {
    formData.append('receipt_image', receiptFile);
    }
  }

  // Process the order
  const response = await fetch('process_order.php', {
    method: 'POST',
    body: formData
  });

  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const data = await response.json();

  if (!data.success) {
    throw new Error(data.message || 'Order processing failed');
  }

  // If everything is successful, redirect to order completion page
  window.location.href = 'order_complete.php';

  } catch (error) {
  console.error('Error during order processing:', error);
  showBootstrapAlert('Order processing failed: ' + error.message, 'danger', 7000);
  }
});

// Bootstrap-styled alert helper (compatible with Bootstrap 5.x)
function ensureAlertContainer() {
  let container = document.getElementById('bs-alert-container');
  if (!container) {
  container = document.createElement('div');
  container.id = 'bs-alert-container';
  container.setAttribute('aria-live', 'polite');
  container.setAttribute('aria-atomic', 'true');
  container.style.position = 'fixed';
  container.style.top = '1rem';
  container.style.right = '1rem';
  container.style.zIndex = '10800';
  container.style.width = 'auto';
  container.style.maxWidth = '420px';
  document.body.appendChild(container);
  }
  return container;
}

/**
 * Get inline SVG icon HTML for alert types
 */
function getAlertIconHtml(type) {
  // Common attributes for icons
  const baseStyle = 'width:1.25rem;height:1.25rem;margin-right:.5rem;flex-shrink:0';
  switch (type) {
  case 'success':
    return `<svg xmlns="http://www.w3.org/2000/svg" style="${baseStyle}" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16" aria-hidden="true"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM6.97 11.03a.75.75 0 0 0 1.07-.02L11.03 8.06a.75.75 0 1 0-1.06-1.06L7.5 9.44 6.03 7.97A.75.75 0 0 0 4.97 9.03l2 2z"/></svg>`;
  case 'warning':
    return `<svg xmlns="http://www.w3.org/2000/svg" style="${baseStyle}" fill="currentColor" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16" aria-hidden="true"><path d="M8.982 1.566a1.13 1.13 0 0 0-1.964 0L.165 13.233c-.457.778.091 1.767.982 1.767h13.706c.89 0 1.438-.99.982-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg>`;
  case 'info':
    return `<svg xmlns="http://www.w3.org/2000/svg" style="${baseStyle}" fill="currentColor" class="bi bi-info-circle-fill" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0zM8.93 4.58a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0zM6.002 6.5a.5.5 0 0 1 .5-.5h2.5a.5.5 0 0 1 0 1H8.5v4a.5.5 0 0 1-1 0v-4H6.502a.5.5 0 0 1-.5-.5z"/></svg>`;
  case 'danger':
  default:
    return `<svg xmlns="http://www.w3.org/2000/svg" style="${baseStyle}" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16" aria-hidden="true"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM4.646 4.646a.5.5 0 0 0 0 .708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646a.5.5 0 0 0-.708 0z"/></svg>`;
  }
}

/**
 * Show Bootstrap alert with an icon
 * @param {string} message
 * @param {string} type - 'primary'|'secondary'|'success'|'danger'|'warning'|'info'|'light'|'dark'
 * @param {number} timeout - milliseconds before auto-dismiss (set 0 to persist)
 */
function showBootstrapAlert(message, type = 'danger', timeout = 5000) {
  const container = ensureAlertContainer();

  // Map some Bootstrap contexts to icon types (use simplified mapping)
  const iconTypeMap = {
  success: 'success',
  danger: 'danger',
  warning: 'warning',
  info: 'info',
  primary: 'info',
  secondary: 'info',
  light: 'info',
  dark: 'danger'
  };
  const iconHtml = getAlertIconHtml(iconTypeMap[type] || 'danger');

  const wrapper = document.createElement('div');
  wrapper.className = `alert alert-${type} alert-dismissible fade show d-flex align-items-start`;
  wrapper.role = 'alert';
  wrapper.style.boxShadow = '0 4px 12px rgba(0,0,0,0.08)';
  wrapper.innerHTML = `
  <div class="d-flex align-items-start" style="gap:.5rem; width:100%">
    <div aria-hidden="true">${iconHtml}</div>
    <div style="flex:1; min-width:0">${message}</div>
    <button type="button" class="btn-close ms-3" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
  `;

  container.appendChild(wrapper);

  if (timeout > 0) {
  setTimeout(() => {
    try {
    // Use Bootstrap's Alert disposal if available
    if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
      const alertInstance = bootstrap.Alert.getInstance(wrapper) || new bootstrap.Alert(wrapper);
      alertInstance.close();
    } else {
      wrapper.remove();
    }
    } catch (err) {
    wrapper.remove();
    }
  }, timeout);
  }
}

// Update the original GCash upload handling
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
    showBootstrapAlert("Invalid file type. Please upload a JPG, JPEG, or PNG image.", "warning");
    return;
  }

  if (file.size > 50 * 1024 * 1024) {
    showBootstrapAlert("File is too large. Please upload an image up to 50MB.", "warning");
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

  // File input change handler
  fileInput.addEventListener("change", function () {
  if (fileInput.files.length > 0) {
    handleFile(fileInput.files[0]);
  }
  });

  // Drag & Drop handling
  ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
  dropArea.addEventListener(eventName, e => {
    e.preventDefault();
    e.stopPropagation();
  });
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

// Prevent using browser back button
window.history.pushState(null, null, window.location.href);
window.onpopstate = function () {
    window.history.pushState(null, null, window.location.href);
    window.location.href = 'basket.php';
};

// Confirm before leaving page
window.addEventListener('beforeunload', function (e) {
    if (!window.submitClicked) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// Set flag when proceeding with order
document.querySelector('.custom-navy-btn').addEventListener('click', function() {
    window.submitClicked = true;
});
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>
</body>
</html>