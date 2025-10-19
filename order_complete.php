<?php
require('admin/inc/config.php');
session_start();

// Check if there's a completed order
if (!isset($_SESSION['last_receipt_no']) || !isset($_SESSION['checkout_products'])) {
    header('Location: basket.php');
    exit;
}

$receiptNo = $_SESSION['last_receipt_no'];
$products = $_SESSION['checkout_products'];

$stmt = $conn->prepare("
    SELECT o.*, u.student_fname, u.student_lname, 
           p.type as product_type
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN products p ON o.product_id = p.id
    WHERE o.receipt_no = ?
");
$stmt->bind_param("s", $receiptNo);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

if (empty($orders)) {
    header('Location: basket.php');
    exit;
}

// Get payment method from the first order (all orders in same receipt have same payment method)
$paymentMethod = $products[0]['payment_method'] ?? $orders[0]['payment_method'];
$orderDate = $products[0]['order_date'] ?? $orders[0]['order_date'];
$customerName = $orders[0]['student_fname'] . ' ' . $orders[0]['student_lname'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VMC Basket- Order Placed</title>
  <?php include 'links.php'; ?>
  

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

    .brand {
      display: flex;
      align-items: center;
      padding: 1rem;
      background: #ffffff;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .thankyou-section {
      background: linear-gradient(102deg, rgba(255, 166, 171, 0.50) -4.28%, rgba(86, 121, 255, 0.50) 46.09%, rgba(255, 237, 152, 0.50) 121.59%);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
      height: 180px;
    }

    .thankyou-title {
      font-size: 3rem;
      color: #333;
      text-shadow: 2px 2px 6px rgba(0, 0, 0, 0.25);
      display: inline-block;
      white-space: nowrap;
      overflow: hidden;
      box-sizing: border-box;
      border-right: .12em solid rgba(0, 0, 0, 0.75); /* caret */
      width: 0;
      /* play typing once and keep the final state, start caret blink only after typing finishes */
      animation: typing 2.2s steps(10, end) forwards,
           blink-caret .75s step-end infinite 2.2s;
    }

    @keyframes typing {
      from { width: 0; }
      to { width: 10ch; } /* adjust ch value to match text length if needed */
    }

    @keyframes blink-caret {
      from, to { border-color: transparent; }
      50% { border-color: rgba(0, 0, 0, 0.75); }
    }

    .thankyou-section p {
      font-size: 1.25rem;
      color: #555;
    }

    .order-title {
      margin: 0;
      padding: 0;
      color: #26387D;
      font-family: "Montserrat", sans-serif;
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
      height: 50px;
      margin-right: 10px;
    }

    .receipt-card{
      width: 100%;
      max-width: 850px; 
      border: 1px solid black;
    }
    .banner-wrapper {
      display: flex;
      justify-content: center;
      margin-top: 20px;
    }

    .order-container {
      max-width: 800px;
      margin: 0 auto;
      background: #e8edef;
      padding: 30px;
    }

    .table td,
    .table th {
      vertical-align: middle;
      background-color: #e8edef;
      border-bottom: 1px solid #D9D9D9;
      font-weight: normal;
    }
    .total-text {
      font-size: 1.25rem;
      font-weight: bold;
    }

    @media (max-width: 767.98px) {
  .thankyou-title {
      font-size: 2.5rem;
      color: #333;
    }

    .thankyou-section p {
      font-size: 1rem;
      color: #555;
    }
    .table td,
    .table th {
      font-size: 0.75rem;
    }
       
    }
    @media (max-width: 575.98px) {
    .thankyou-title {
      font-size: 1.5rem;
      color: #333;
    }

    .thankyou-section p {
      font-size: 0.9rem;
      color: #555;
    }
    .table td,
    .table th {
      font-size: 0.75rem;
    }

    .total-text {
      font-size: 0.75rem;
      font-weight: medium;
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

  <div class="thankyou-section d-flex flex-column justify-content-center align-items-center py-3">
    <h2 class="fw-bold thankyou-title text-center">Thank You!</h2>
    <p class="text-center">Your order has been placed.</p>
  </div>

  <div class="d-flex justify-content-center p-4">
    <div class="card shadow-lg">
      <div class="card-body text-center">

        <div class="order-container mt-2">
                    <h4 class="text-center fw-bold">Receipt No: 
                        <a href="#" class="text-decoration-none order-title"><?= htmlspecialchars($receiptNo) ?></a>
                    </h4>
                    
                    <div class="table-responsive">
                        <table class="table mt-3 mb-2">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Description</th>
                                    <th>Unit Price</th>
                                    <th>Qty.</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalAmount = 0;
                                foreach ($orders as $order): 
                                    $subtotal = $order['price'] * $order['quantity'];
                                    $totalAmount += $subtotal;
                                ?>
                                    <tr>
                                        <td><img src="admin/<?= htmlspecialchars($order['image']) ?>" alt="Product" width="60"></td>
                                                                            
                                        <td>
                                            <div class="fw-bold"><?= htmlspecialchars($order['product_name']) ?></div>
                                            <?php if ($order['product_type'] === 'Uniform' && !empty($order['size']) && $order['size'] !== 'N/A'): ?>
                                                <div class="text-muted" style="font-size: 0.85rem;">Size: <?= htmlspecialchars($order['size']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>₱ <?= number_format($order['price'], 2) ?></td>
                                        <td><?= $order['quantity'] ?></td>
                                        <td>₱ <?= number_format($subtotal, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>

                                <tr>
                                    <td><small class="fw-bold text-start">Order Date</small></td>
                                    <td colspan="3"></td>
                                    <td><small><?= date('M d, Y', strtotime($orderDate)) ?></small></td>
                                </tr>
                                <tr>
                                    <td><small class="fw-bold text-start">Payment</small></td>
                                    <td colspan="3"></td>
                                    <td><small><?= htmlspecialchars($paymentMethod) ?></small></td>
                                </tr>
                                <tr>
                                    <td><h5 class="fw-bold text-start total-text">Total:</h5></td>
                                    <td colspan="3"></td>
                                    <td><p class="total-text">₱ <?= number_format($totalAmount, 2) ?></p></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <a href="purchase_history.php" class="custom-navy-btn mt-3 text-decoration-none">View My Purchase</a>
                </div>
      </div>
    </div>
  </div>

  <?php
    // Clear checkout session data
    unset($_SESSION['checkout_products']);
    unset($_SESSION['last_receipt_no']);
    ?>

  <script>
    // Prevent back button
    window.history.pushState(null, null, window.location.href);
    window.onpopstate = function() {
        window.history.pushState(null, null, window.location.href);
        window.location.href = 'purchase_history.php';
    };

    // Prevent browser back button
    window.addEventListener('load', function() {
        window.history.forward();
    });

    // Disable back button in browser
    window.onunload = function() {
        null;
    };

    // If user tries to leave the page
    window.onbeforeunload = function() {
        window.setTimeout(function() {
            window.location = 'purchase_history.php';
        }, 0);
    };
</script>
</body>
</html>
