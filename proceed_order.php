<?php
require 'admin/inc/config.php';

// Get products data
function getProducts()
{
  global $conn;
  $products = [];
  session_start(); // Must be called before accessing $_SESSION

  $limit = isset($_SESSION['item_count']) ? (int) $_SESSION['item_count'] : 0;

  if ($limit <= 0) {
    return ['error' => 'Invalid item count'];
  }

  // Query to fetch products and their quantities from the orders table
  $stmt = $conn->prepare("SELECT orders.product_name, orders.quantity, orders.price, orders.image, orders.size 
                          FROM orders ORDER BY orders.id DESC LIMIT ?");
  if (!$stmt) {
    return ['error' => $conn->error];
  }

  $stmt->bind_param("i", $limit);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result === false) {
    return ['error' => $conn->error];
  }

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $row['image'] = !empty($row['image'])
        ? 'admin/' . $row['image']
        : 'admin/images/default.png';
      $products[] = $row;
    }
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
      <button class="btn btn-circle" onclick="window.history.back();">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left"
          viewBox="0 0 16 16">
          <path fill-rule="evenodd"
            d="M15 8a.5.5 0 0 1-.5.5H2.707l4.147 4.146a.5.5 0 0 1-.708.708l-5-5a.5.5 0 0 1 0-.708l5-5a.5.5 0 1 1 .708.708L2.707 7.5H14.5A.5.5 0 0 1 15 8z" />
        </svg>
      </button>
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
              $total = 0; // Initialize total
              $totalItems = 0; // Initialize total items
              ?>
              <?php foreach ($products as $product): ?>
                <?php
                // Example values; update as needed with real quantity logic
                $itemQuantity = $product['quantity'];// Assuming quantity is passed from the form
                $subtotal = $product['price'] * $itemQuantity;
                $total += $subtotal; // Add to total
                $totalItems += $itemQuantity; // Add to total items
                ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <img src="<?php echo htmlspecialchars($product['image']); ?>" class="product-img me-2"
                        alt="Product" />
                      <div>
                        <strong><?php echo htmlspecialchars($product['product_name']); ?></strong><br />
                        <?php if (!empty($order['size']) && $order['size'] !== 'N/A'): ?>
                            Size: <?php echo htmlspecialchars($order['size']); ?>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>
                  <td class="text-center">₱ <?php echo number_format($product['price'], 2); ?></td>
                  <td class="text-center"><?php echo $itemQuantity; ?></td>
                  <td class="text-end">₱ <?php echo number_format($subtotal, 2); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="4">No featured products available.</td>
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
        <button class="btn btn-outline-danger me-2 btn-cancel " onclick="window.history.back();">Cancel</button>
        <a href="order_complete.php"><button class="custom-navy-btn " type="submit">Proceed to Order</button></a>
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
document.querySelector('a[href="order_complete.php"]').addEventListener('click', function(e) {
    const onlinePaymentSelected = document.querySelector('input[name="paymentMethod"][value="Send Online Receipt"]').checked;
    const hasUploadedReceipt = document.querySelector('#previewContainer img') !== null;

    if (onlinePaymentSelected && !hasUploadedReceipt) {
        e.preventDefault();
        alert('Please upload your GCash e-receipt before proceeding with the order.');
        return false;
    }
    
    // If receipt is uploaded for online payment or if it's cash payment, proceed with form submission
    const formData = new FormData();
    
    if (onlinePaymentSelected && hasUploadedReceipt) {
        // Get the receipt image file
        const receiptFile = document.querySelector('#gcashReceiptInput').files[0];
        formData.append('receipt_image', receiptFile);
        
        // Send the receipt first
        fetch('upload_receipt.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Only proceed to order_complete.php if upload was successful
                window.location.href = 'order_complete.php';
            } else {
                alert('Error uploading receipt: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error uploading receipt');
        });
        
        e.preventDefault(); // Prevent default navigation
    }
});

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
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>
</body>
</html>