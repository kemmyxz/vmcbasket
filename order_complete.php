<?php
require('admin/inc/config.php');
session_start();
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'User not logged in']);
    exit;
}

// Get the item count from session
$limit = isset($_SESSION['item_count']) ? (int)$_SESSION['item_count'] : 0;

// Create an array to store all orders with their product details
$orders = [];

// Generate a unique receipt number based on the user ID and current time
$userId = $_SESSION['user_id'];
$receiptNo = date('YmdHis') . $userId;

// Start a database transaction
$conn->begin_transaction();

try {
    // Step 1: Insert into order_receipt with the generated receipt number
    $stmt = $conn->prepare("INSERT INTO order_receipt (receipt_id) VALUES (?)");
    $stmt->bind_param("s", $receiptNo);
    $stmt->execute();

    // Step 2: Update ONLY the selected orders (those without a receipt number) for this user
    $stmt = $conn->prepare("
        UPDATE orders 
        SET receipt_no = ? 
        WHERE user_id = ? 
        AND receipt_no IS NULL 
        AND status = 'Pending'
        AND order_date = CURDATE()
        ORDER BY id DESC 
        LIMIT ?
    ");
    $stmt->bind_param("sii", $receiptNo, $userId, $limit);
    $stmt->execute();

    // Step 3: Fetch ONLY the orders that were just updated with the new receipt number
    $stmt = $conn->prepare("
        SELECT o.*, p.product_name, p.price, p.image 
        FROM orders o
        JOIN products p ON o.product_id = p.id
        WHERE o.user_id = ? 
        AND o.receipt_no = ?
        ORDER BY o.id DESC
    ");
    $stmt->bind_param("is", $userId, $receiptNo);
    $stmt->execute();
    $order_result = $stmt->get_result();

    while ($row = $order_result->fetch_assoc()) {
        // Calculate total for this order
        $total = $row['price'] * $row['quantity'];
        
        // Add order and product details to the orders array
        $orders[$row['receipt_no']][] = [
            'order' => [
                'id' => $row['id'],
                'quantity' => $row['quantity'],
                'size' => $row['size'],
                'order_date' => $row['order_date'],
                'payment_method' => $row['payment_method']
            ],
            'product' => [
                'product_name' => $row['product_name'],
                'price' => $row['price'],
                'image' => $row['image']
            ],
            'total' => $total
        ];
    }

    // Commit transaction
    $conn->commit();

} catch (Exception $e) {
  $conn->rollback();
  echo json_encode(['error' => $e->getMessage()]);
  exit;
}

if (empty($orders)) {
    echo "<h2 class='text-center mt-5'>No orders found. <a href='shop_uniforms.php' class='btn btn-primary ms-2'>Go to Shop</a></h2>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VMC Basket- Order Placed</title>
  <link rel="icon" href="admin/images/vmc_basket_logo.png" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="style.css">

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

    .banner {
      background: url('./admin/images/Order_Success.png') no-repeat center center;
      background-size: contain;
      width: 100%;
      max-width: 850px;
      height: 200px;
      border-radius: 8px;
    }

    .order-container {
      max-width: 800px;
      margin: 0 auto;
      background: white;
      padding: 30px;
    }

    .table td,
    .table th {
      vertical-align: middle;
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
    <div class="card shadow-lg ">
      <div class="card-body text-center">

        <div class="order-container mt-2">
            <?php foreach ($orders as $receiptNo => $orderDataArray): ?>
                <h4 class="text-center fw-bold">Receipt No: 
                    <a href="#" class="text-decoration-none order-title"><?= $receiptNo ?></a>
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
                      <?php foreach ($orderDataArray as $orderData): ?>
                        <tr>
                          <td><img src="admin/<?= htmlspecialchars($orderData['product']['image']) ?>" alt="Product" width="60"></td>
                          <td>
                            <div class="fw-bold"><?= htmlspecialchars($orderData['product']['product_name']) ?></div>
                            <div class="text-muted" style="font-size: 0.85rem;">Size: <?= htmlspecialchars($orderData['order']['size']) ?></div>
                          </td>
                          <td>₱ <?= number_format($orderData['product']['price'], 2) ?></td>
                          <td><?= $orderData['order']['quantity'] ?></td>
                          <td>₱ <?= number_format($orderData['total'], 2) ?></td>
                        </tr>
                      <?php endforeach; ?>
                      <?php 
                        // Get the first order's date and payment method since all orders in the same receipt have same values
                        $firstOrder = reset($orderDataArray);
                      ?>
                      <tr>
                        <td><small class="fw-bold text-start">Order Date</small></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                          <small><?= date('M d, Y', strtotime($firstOrder['order']['order_date'])) ?></small>
                        </td>
                      </tr>
                      <tr>
                        <td><small class="fw-bold text-start">Payment</small></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                          <small><?= htmlspecialchars($firstOrder['order']['payment_method']) ?></small>
                        </td>
                      </tr>
                      <tr>
                        <td><h5 class="fw-bold text-start total-text">Total:</h5></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                          <p class="total-text">₱ <?= number_format(array_sum(array_column($orderDataArray, 'total')), 2) ?></p>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              <?php endforeach; ?>

            <a href="purchase_history.php"><button class="custom-navy-btn mt-3">View My Purchase</button></a>
        </div>
      </div>
    </div>
  </div>

</body>

</html>
