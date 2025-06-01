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
  <title>VMC Basket-My Favorites</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css"
    rel="stylesheet">
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Ubuntu:wght@400;500;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="./css/style.css">

  <style>
    .header-order {
      background-color: #0b2c4d;
      color: white;
      padding: 0.5rem 1rem;
      font-size: 0.9rem;
      font-weight: bold;
      text-align: center;
    }

    .logo-name {
      color: #003153;
      font-size: 30px;
      font-family: "Ubuntu", sans-serif;
      font-weight: bold;
    }

    .order-title {
      margin: 0;
      padding: 0;
      color: #00527F;
      font-family: "Ubuntu", sans-serif;
      font-weight: bold;
    }

    .brand {
      display: flex;
      align-items: center;
      padding: 1rem;
      background-color: #ffffff;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .brand img {
      height: 40px;
      margin-right: 10px;
    }

    .table-headerbg {
      background-color: #E8EDEF;
    }

    .section {
      background-color: #E8EDEF;
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
      vertical-align: middle;
    }

    .total {
      font-weight: bold;
      color: #2d8dd6;
    }

    .order-line {
      border: 1px solid #000000;
      opacity: 0.3;
      margin-top: 5px;
      margin-botton: 5px;
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
      margin-bottom: 1rem;
    }

    .btn-circle {
      border-radius: 50%;
      width: 36px;
      height: 36px;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: #004b75;
      color: #fff;
      border: none;
    }

    .btn-circle:hover {
      background-color: #003a5c;
      color: white;
    }

    .online-note {
      background-color: #ffffff;
      border-left: 4px solid #2d8dd6;
      padding: 1rem;
      margin-top: 1rem;
      display: none;
      border-radius: 4px;
      margin-bottom: 30px;
    }
  </style>

</head>

<body>

  <div class="header-order">
    ALL PRODUCTS ARE AVAILABLE FOR PICK-UP ONLY AT VILLAGERS MONTESSORI COLLEGE
  </div>

  <div class="brand d-flex justify-content-center border-bottom shadow-sm">
    <img src="admin/images/Admin Nav/VMS-LOGO-Alternative-03.png" alt="Logo" />
    <h2 class="mb-0 logo-name">VMC Basket</h2>
  </div>

  <div class="container mt-4">

    <!-- Return Button -->
    <div class="order-header">
      <button class="btn btn-circle" onclick="window.history.back();">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left"
          viewBox="0 0 16 16">
          <path fill-rule="evenodd"
            d="M15 8a.5.5 0 0 1-.5.5H2.707l4.147 4.146a.5.5 0 0 1-.708.708l-5-5a.5.5 0 0 1 0-.708l5-5a.5.5 0 1 1 .708.708L2.707 7.5H14.5A.5.5 0 0 1 15 8z" />
        </svg>
      </button>
      <h3 class="mb-0 order-title"><strong>Order Details</strong></h3>
    </div>
   
    <!-- Order Details -->
    <div class="section card">
      <div class="table-responsive">
        <table class="table summary-table">
          <thead class="table-headerbg">
            <tr>
              <th>
                <h4 class="order-title">Product Details</h4>
              </th>
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
                        Size: <?php echo htmlspecialchars($product['size']); ?>
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
      <hr class="order-line">
      <div class="text-end pe-2 text-muted">
        Order Total (<span id="total-items"><?php echo $totalItems; ?></span> items):
        <span class="order-title ms-2" id="order-total">₱ <?php echo number_format($total, 2); ?></span>
      </div>
    </div>

    <!-- Payment Method -->
    <div class="card p-4 section">
    <h4 class="order-title mb-3">Payment Method</h4>
    <div class="d-flex flex-column justify-content-start align-items-start mb-2">
        <div class="form-check custom-radio mb-2 d-flex align-items-center">
            <input class="form-check-input me-2" type="radio" name="paymentMethod" id="online" 
                value="Send Online Receipt"
                <?php echo isset($orderData['payment_method']) && $orderData['payment_method'] === 'Send Online Receipt' ? 'checked' : ''; ?>
                onclick="toggleNote(true); updatePaymentMethod(this.value)">
            <label class="form-check-label d-flex align-items-center" for="online">
                <img src="./admin/images/Gcash-icon.png" alt="Receipt Icon" style="height: 50px; width: 50px; border-radius: 50%;">
                <span class="ms-2">Send Online Receipt</span>
            </label>
        </div>

        <div id="onlineNote" class="online-note" style="display:none;">
            Pay via GCash and upload the receipt in <b>'My Purchase'</b> for validation. Orders are processed within 24 hours after payment confirmation. Payment must be made within <b>24 hours</b>, or the order will be canceled.
        </div>

        <div class="form-check custom-radio d-flex align-items-center mt-1">
            <input class="form-check-input me-2" type="radio" name="paymentMethod" id="cash" 
                value="Cash (Pay at the Counter)"
                <?php echo !isset($orderData['payment_method']) || $orderData['payment_method'] === 'Cash (Pay at the Counter)' ? 'checked' : ''; ?>
                onclick="toggleNote(false); updatePaymentMethod(this.value)">
            <label class="form-check-label d-flex align-items-center" for="cash">
                <img src="./admin/images/Cash.png" alt="Cash Icon" style="height: 50px; width: 50px; border-radius: 50%;">
                <span class="ms-2">Cash (Pay at the Counter)</span>
            </label>
        </div>
    </div>

    <hr class="order-line">
    <div class="d-flex justify-content-between align-items-center mt-2 mb-3">
        <div>
            <img src="./admin/images/Cash.png" alt="Payment Icon" style="height: 50px; width: 50px; border-radius: 50%;" id="paymentMethodIcon"> 
            <span id="paymentMethodText">Cash (Pay at the Counter)</span>
        </div> 
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted me-5">Total Payment:</div>
            <h3 class="order-title">₱<?php echo number_format($total, 2); ?></h3>
        </div>
    </div>

      <hr class="order-line">
      <div class="d-flex justify-content-end mt-2">
        <button class="btn btn-outline-danger me-2 btn-cancel btn-lg" onclick="window.history.back();">Cancel</button>
        <a href="order_complete.php"><button class="btn btn-order btn-lg" type="submit">Proceed to Order</button></a>
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
</script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>
</body>

</html>
``` 