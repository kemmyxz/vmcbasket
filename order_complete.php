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
    echo "<h2 class='text-center mt-5'>No orders found. <a href='shop.php' class='btn btn-primary ms-2'>Go to Shop</a></h2>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VMC Basket-My Favorites</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="CSS/style.css">

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
      border-top: 1px solid #000000;
      border-bottom: 1px solid #000000;
      font-weight: normal;
    }

    .summary-section {
      display: flex;
      justify-content: space-between;
      margin-top: 20px;
      font-size: 0.95rem;
    }

    .summary-section strong {
      font-weight: 600;
    }

    .btn-view {
      display: block;
      margin: 30px auto 0;
      background-color: #567C8D;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      font-weight: 500;
    }

    .btn-view:hover {
      background-color: #3d6576;
      color: white;
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

  <div class="d-flex justify-content-center mt-1">
    <div class="card shadow-lg" style="width: 100%; max-width: 850px; border: none;">
      <div class="card-body">
        <div class="banner-wrapper">
          <div class="banner"></div>
        </div>

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
                        </tbody>
                    </table>
                </div>

                <div class="summary-section mt-2">
                    <div>
                        <h6 class="order-title">Order Date</h6>
                        <?php 
                        // Get the first order's date since all orders in the same receipt have same date
                        $firstOrder = reset($orderDataArray);
                        ?>
                        <div><?= date('M d, Y', strtotime($firstOrder['order']['order_date'])) ?></div>
                        <h6 class="mt-3 order-title">Payment</h6>
                        <div><?= htmlspecialchars($firstOrder['order']['payment_method']) ?></div>
                    </div>
                    <div class="d-flex flex-column align-items-start">
                        <h6 class="order-title">Order Summary</h6>
                        <div class="d-flex align-items-center">
                            <h4 class="fw-bold me-2">Total:</h4>
                            <h4 class="fw-bold">₱ <?= number_format(array_sum(array_column($orderDataArray, 'total')), 2) ?></h4>
                        </div>
                    </div>
                </div>

                <hr class="order-line my-4">
            <?php endforeach; ?>

            <a href="purchase_history.php"><button class="btn btn-view mt-5">View My Purchase</button></a>
        </div>
      </div>
    </div>
  </div>

</body>

</html>