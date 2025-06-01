<?php

require 'inc/config.php'; // Database connection

session_start();
$where_clause = "";

if(isset($_SESSION['filter_month']) && isset($_SESSION['filter_year'])) {
    $month = $_SESSION['filter_month'];
    $year = $_SESSION['filter_year'];
    $where_clause = "WHERE MONTH(o.order_date) = $month AND YEAR(o.order_date) = $year";
}

// Set number of items per page
$items_per_page = 10;

// Get current page number from URL parameter
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Modify your main SQL query to include LIMIT and OFFSET
$sql = "
SELECT 
    r.receipt_id,
    r.order_status,
    o.payment_method,
    o.customer_name,
    o.email,
    o.phone,
    o.product_name,
    o.quantity,
    o.price,
    o.total_price,
    o.order_date AS date_ordered,
    o.image AS product_image,
    o.user_id
FROM 
    order_receipt r
JOIN 
    orders o ON r.receipt_id = o.receipt_no
$where_clause
ORDER BY 
    r.receipt_id DESC
LIMIT $items_per_page OFFSET $offset";

// Add this code to get total number of records for pagination
$count_sql = "
SELECT 
    COUNT(DISTINCT r.receipt_id) as total 
FROM 
    order_receipt r
JOIN 
    orders o ON r.receipt_id = o.receipt_no
$where_clause";

$count_result = $conn->query($count_sql);
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $items_per_page);

$results = $conn->query($sql);

// Add this query near the top of the file after your existing queries
$sql_receipt_images = "SELECT r.*, ri.image_path 
        FROM order_receipt r 
        LEFT JOIN order_receipts_images ri ON r.receipt_id = ri.receipt_id";

// Create an array to group orders by receipt_id
$grouped_orders = [];
while ($row = $results->fetch_assoc()) {
    $receipt_id = $row['receipt_id'];
    if (!isset($grouped_orders[$receipt_id])) {
        $grouped_orders[$receipt_id] = [
            'receipt_id' => $receipt_id,
            'order_status' => $row['order_status'],
            'payment_method' => $row['payment_method'],
            'customer_name' => $row['customer_name'] ?: 'Walk-in Customer', // Default value if null
            'email' => $row['email'] ?: 'N/A', // Default value if null
            'phone' => $row['phone'] ?: 'N/A', // Default value if null
            'date_ordered' => $row['date_ordered'],
            'user_id' => $row['user_id'], // Add this line
            'products' => [],
            'total_amount' => 0
        ];
    }

    $subtotal = $row['quantity'] * $row['price'];

    $grouped_orders[$receipt_id]['products'][] = [
        'product_name' => $row['product_name'],
        'quantity' => $row['quantity'],
        'price' => $row['price'],
        'subtotal' => $subtotal,
        'image' => $row['product_image']
    ];
    $grouped_orders[$receipt_id]['total_amount'] += $subtotal;
}

// Get calendar events for orders
function getCalendarEvents() {
    global $conn;
    
    $events = array();
    
    // Array of all order statuses and their styling
    $statuses = [
        'Complete' => [
            'className' => 'fc-event-completed',
            'color' => '#28a745'
        ],
        'Pending' => [
            'className' => 'fc-event-pending',
            'color' => '#ffc107'
        ],
        'ToPickUp' => [
            'className' => 'fc-event-topickup',
            'color' => '#17a2b8'
        ],
        'Cancelled' => [
            'className' => 'fc-event-cancelled',
            'color' => '#dc3545'
        ],
        'Refunded' => [
            'className' => 'fc-event-refunded',
            'color' => '#02395E'
        ]
    ];
    
    // Get orders for each status
    foreach ($statuses as $status => $style) {
        $sql = "SELECT COUNT(*) as count, DATE(order_date) as date 
                FROM orders 
                WHERE status = ? 
                GROUP BY DATE(order_date)";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $status);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while($row = $result->fetch_assoc()) {
            $events[] = array(
                'title' => $row['count'] . ' ' . $status . ' Orders',
                'date' => $row['date'],
                'className' => $style['className'],
                'backgroundColor' => $style['color']
            );
        }
    }
    
    return $events;
}

$calendarEvents = getCalendarEvents();
?>




<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VMC Basket - Admin/Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Ubuntu:wght@400;500;700&display=swap"
    rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="style.css">
  <style>
    .tab-button {
      border: none;
      background-color: #D5E9F3;
      font-weight: 600;
      padding: 10px 20px;
      color: #898A8B;
      border-bottom: 2px solid transparent;
    }

    .tab-button.active {
      border-bottom: 2px solid #3D87F5;
      color: #3D87F5;
    }

    .status-badge {
      padding: 5px 12px;
      border-radius: 15px;
      font-size: 0.9rem;
      font-weight: 500;
    }

    .Complete {
      background-color: #A1E389;
      color: #267F05;
    }

    .Pending {
      background-color: #FFCDAB;
      color: #D85600;
    }

    .Return {
      background-color: #ADB6F4;
      color: #4400FF;
    }

    .Refunded {
      background-color: #54AAEB;
      color: #02395E;
    }

    .Cancelled {
      background-color: #FEBFBC;
      color: #E8261A;
    }

    /* Calendar Header */
    .fc-toolbar-title {
      font-size: 24px;
      color: #343a40;
    }

    /* Events styling */
    .fc-event {
      font-size: 14px;
      font-weight: bold;
      border: none;
      padding: 2px 5px;
      border-radius: 5px;
    }

    /* Specific Colors */
    .fc-event-completed {
      background-color: #28a745 !important;
      color: white !important;
    }

    .fc-event-pending {
      background-color: #ffc107 !important;
      color: black !important;
    }

    .fc-event-cancelled {
      background-color: #dc3545 !important;
      color: white !important;
    }

    .fc-event-refunded {
      background-color: #02395E !important;
      color: white !important;
    }

    .fc-event-returned {
      background-color: #4400FF !important;
      color: white !important;
    }

    .fc-event-topickup {
      background-color: #17a2b8 !important;
      color: white !important;
    }

    /* Hover Day Cell */
    .fc-daygrid-day:hover {
      background-color: #e9ecef;
      cursor: pointer;
    }

    /* Style the day headers (Mon, Tue, Wed, etc.) */
    .fc-col-header-cell {
      background-color: #00527F;
      font-weight: bold;
      font-size: 1rem;
      color: white;
      padding: 10px 0;
    }

    .fc-col-header-cell-cushion {
      text-decoration: none !important;
      color: inherit;
    }

    /* Remove underline on day numbers */
    .fc-daygrid-day-number {
      text-decoration: none !important;
      font-weight: 500;
      color: #555;
    }

    .fc-daygrid-day-number:hover {
      text-decoration: none;
      color: #000;
    }

    #calendarModal .btn {
      background-color: #0d6efd;
      /* Bootstrap primary blue */
      color: white;
      border-radius: 10px;
      font-weight: 600;
      padding: 8px 16px;
      font-size: 1rem;
      transition: background-color 0.3s;
    }

    #calendarModal .btn:hover {
      background-color: #0b5ed7;
      /* Darker blue on hover */
    }

    /* Specific style for 'View This Month' button if you want */
    #viewMonthButton {
      background-color: #198754;
    }

    #viewMonthButton:hover {
      background-color: #157347;
    }

    #orderDetailsModal .modal-body h6 {
      font-weight: 600;
      color: #00527F;
      /* Bootstrap primary color */
    }

    #orderDetailsModal .table th,
    #orderDetailsModal .table td {
      vertical-align: middle;
    }

    #orderDetailsModal img {
      border: 1px solid #dee2e6;
      padding: 5px;
      background-color: #f8f9fa;
    }
    .product-thumbnail {
    border-radius: 4px;
    border: 1px solid #dee2e6;
    transition: transform 0.2s;
}

.product-thumbnail:hover {
    transform: scale(1.1);
}

#orderDetailsModal .table td {
    vertical-align: middle;
}

    /* Add a legend for the calendar */
    .calendar-legend {
        display: flex;
        justify-content: center;
        gap: 15px;
        padding: 10px;
        flex-wrap: wrap;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 0.9rem;
    }

    .legend-color {
        width: 15px;
        height: 15px;
        border-radius: 3px;
    }

    .legend-complete { background-color: #198754; }
    .legend-pending { background-color: #ffc107; }
    .legend-topickup { background-color: #17a2b8; }
    .legend-cancelled { background-color: #dc3545; }
    .legend-refunded { background-color: #02395E; }
  </style>
</head>

<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar Toggle Button -->
      <nav class="navbar navbar-light bg-light d-md-none">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu"
          aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="text-center">
          <img src="./images/Admin Nav/VMS-LOGO-ALternative-03.png" alt="VMC Logo" class="img-fluid"
            style="max-width: 150px;">

          <h5 class="LogoName">VMC Basket</h5>
        </div>
      </nav>

      <!-- Sidebar -->
      <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse" style="position: fixed;">
        <div class="text-center my-3 mb-5 d-none d-md-block">
          <img src="./images/Admin Nav/VMS-LOGO-ALternative-03.png" alt="VMC Logo" class="img-fluid"
            style="max-width: 150px;">
          <h4 class="LogoName">VMC Basket</h4>
        </div>
        <a href="index.php" class="mt-2"><img src="./images/Admin Nav/dashboard-nav.png" alt="Dashboard"
            Class="dashboard-icon" style="max-width: 30px; margin-right: 10px;">Dashboard</a>
        <a href="orders.php" class="active"><img src="./images/Admin Nav/orders-nav-clicked.png" alt="Orders"
            Class="orders-icon" style="max-width: 30px; margin-right: 10px;">Orders</a>
        <a href="prod.php" class="mt-2"><img src="./images/Admin Nav/products-nav.png" alt="Products"
            Class="products-icon" style="max-width: 30px; margin-right: 10px;">Products</a>
        <a href="cus.php" class="mt-2"><img src="./images/Admin Nav/customers-nav.png" alt="Customer"
            Class="customer-icon" style="max-width: 30px; margin-right: 10px;">Students</a>
        <a href="inquiries.php" class="mt-2"><img src="./images/Admin Nav/message-nav.png" alt="Message" Class="message-icon"
            style="max-width: 30px; margin-right: 10px;">Messages</a>
        <a href="ratings.php" class="mt-2"><img src="./images/Admin Nav/rating-nav.png" alt="Ratings & Reviews"
            Class="reviews-icon" style="max-width: 30px; margin-right: 10px;">Ratings & Reviews</a>
        <a href="accounting.php" class="mt-2 mb-2"><img src="./images/Admin Nav/receipt-nav 1.png" alt="Accounting"
            Class="accounting-icon" style="max-width: 30px; margin-right: 10px;">Receipt Form</a>
        <a href="#" class="mt-2 mb-2"><img src="./images/Admin Nav/setting-nav.png" alt="Settings" Class="settings-icon"
            style="max-width: 30px; margin-right: 10px;">Settings</a>
        <a href="#" class="mt-xl-5"><img src="./images/Admin Nav/admin-logout-nav.png" alt="Logout" Class="logout-icon"
            style="max-width: 30px; margin-right: 10px;">Logout</a>
      </nav>

      <!-- Content Area -->
      <main class="col-md-9 ms-sm-auto col-lg-10 content">
        <div class="d-flex justify-content-end mb-5">
          <div class="search-container">
            <input type="text" class="form-control" placeholder="">
            <button><img src="./images/search-icon.png" alt="Search"></button>
          </div>
        </div>
        <div class="mt-2 d-flex flex-row align-items-center mb-5">
          <img src="./images/admin nav/Orders.png" alt="VMC Dashboard" class="img-fluid"
            style="max-width: 40px; margin-right: 10px;">
          <h2>Orders</h2>
          <!-- <span class="ms-3 text-secondary"> 5 Orders found</span> -->
          <button id="calendarButton" type="button" class="btn btn-primary d-flex align-items-end justify-content-center ms-auto"
            data-bs-toggle="modal" data-bs-target="#calendarModal">
          </button>
        </div>
        <div class="d-flex justify-content-end mb-3">


          <!-- Calendar Modal -->
          <div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="calendarModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="calendarModalLabel">Monthly Transactions</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div id="calendar" style="padding: 10px; background-color: #F0F5F8; border-radius: 10px;"></div>
                  <div class="text-end mt-3">
                    <button id="viewMonthButton" class="btn btn-success">View This Month</button>
                  </div>
                  <div class="calendar-legend mt-3">
                    <div class="legend-item">
                      <div class="legend-color legend-complete"></div>
                      <span>Completed</span>
                    </div>
                    <div class="legend-item">
                      <div class="legend-color legend-pending"></div>
                      <span>Pending</span>
                    </div>
                    <div class="legend-item">
                      <div class="legend-color legend-topickup"></div>
                      <span>To Pick Up</span>
                    </div>
                    <div class="legend-item">
                      <div class="legend-color legend-cancelled"></div>
                      <span>Cancelled</span>
                    </div>
                    <div class="legend-item">
                      <div class="legend-color legend-refunded"></div>
                      <span>Refunded</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TABLE -->
        <div class="table-container table-responsive-lg">

          <div class="d-flex mb-3">
            <button class="tab-button">All Orders</button>
            <button class="tab-button">Pending</button>
            <button class="tab-button">To Pick Up</button>
            <button class="tab-button">Completed</button>
            <button class="tab-button">Cancelled</button>
            <button class="tab-button">Returns</button>
            <button class="tab-button">Refunded</button>
          </div>
          <table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Product</th>
            <th>Total Amount</th>
            <th>Customer Details</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php $count = 1; foreach ($grouped_orders as $order) : ?>
            <tr>
                <td><?= $count++ ?></td>
                
                
                <td>
                    <?php foreach ($order['products'] as $product) : ?>
                        <div class="d-flex align-items-center mb-2">
                            <img src="<?= $product['image'] ?>" 
                                 alt="<?= $product['product_name'] ?>" 
                                 class="product-thumbnail me-2"
                                 style="width: 40px; height: 40px; object-fit: cover;">
                            <span>
                                <?= $product['product_name'] ?> (x<?= $product['quantity'] ?>) - ₱<?= number_format($product['price'], 2) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </td>
                <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                <td class="text-start">
                    <strong>Receipt No.: </strong><?= $order['receipt_id'] ?><br>
                    <strong>Student Name: </strong><?= $order['customer_name'] ?><br>
                    <strong>Customer Type: </strong><?= $order['user_id'] ? 'Registered Student' : 'Walk-in Customer' ?><br>
                    <strong>Date Ordered: </strong><?= $order['date_ordered'] ?><br>
                    <strong>Payment: </strong> <?= $order['payment_method'] ?><br>
                    <?php if($order['phone'] != 'N/A'): ?>
                        <strong>Phone: </strong><?= $order['phone'] ?>
                    <?php endif; ?>
                </td>
                <td><span class="status-badge <?= $order['order_status'] ?>"><?= $order['order_status'] ?></span></td>
                <td >
    <button class="btn btn-primary view-details" 
            data-bs-toggle="modal" 
            data-bs-target="#orderDetailsModal"
            data-receipt-id="<?= $order['receipt_id'] ?>"
            data-customer="<?= htmlspecialchars($order['customer_name']) ?>"
            data-email="<?= htmlspecialchars($order['email']) ?>"
            data-date="<?= $order['date_ordered'] ?>"
            data-payment="<?= htmlspecialchars($order['payment_method']) ?>"
            data-products='<?= json_encode($order['products']) ?>'
            data-total="<?= $order['total_amount'] ?>">
        <i class="bi bi-file-text " ></i> Details
    </button>
    <br><br>
    <?php if(($order['payment_method'] == 'Cash (Pay at the Counter)' && $order['order_status'] == 'Pending') 
         || ($order['payment_method'] == 'Send Online Receipt' && $order['order_status'] == 'ToPickUp')): ?>
    <button class="bi bi-check-circle btn btn-success complete-order" 
            data-receipt-id="<?= $order['receipt_id'] ?>"
            style="border-radius: 5px;">
        COMPLETE
    </button>
<?php endif; ?>
</td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

          
        </div>
        <nav aria-label="Page navigation" class="d-flex justify-content-end mt-3">
    <ul class="pagination justify-content-center">
        <?php if($total_pages > 1): ?>
            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page - 1 ?>" <?= ($page <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Previous</a>
            </li>
            
            <?php
            // Calculate range of pages to show
            $start_page = max(1, min($page - 2, $total_pages - 4));
            $end_page = min($total_pages, max(5, $page + 2));
            
            // Show first page if not in range
            if($start_page > 1) {
                echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                if($start_page > 2) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
            
            // Show page numbers
            for($i = $start_page; $i <= $end_page; $i++): ?>
                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor;
            
            // Show last page if not in range
            if($end_page < $total_pages) {
                if($end_page < $total_pages - 1) {
                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
            }
            ?>
            
            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page + 1 ?>" <?= ($page >= $total_pages) ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Next</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
      </main>
    </div>
  </div>



  <!-- Order Details Modal -->
  <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" id="orderDetailsModalLabel">Order Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <!-- Product Info -->
          <h5 class="mb-3 fw-bold">Product Information</h5>
          <div class="table-responsive mb-4">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Product Image</th>
                  <th>Product Name</th>
                  <th>Qty</th>
                  <th>Price</th>
                  <th>Subtotal</th>
                </tr>
              </thead>
              <tbody id="productDetails">
                <!-- Product details will be dynamically populated -->
              </tbody>
            </table>
          </div>

          <hr>

          <!-- Customer Info -->
          <h5 class="mb-3 fw-bold">Customer Information</h5>
          <div class="row mb-4" >
            <div class="col-md-6" >
              <strong>Receipt Number:</strong> <span id="customerID"></span><br>
              <strong>Name:</strong> <span id="customerName"></span><br>
              <strong>Email:</strong> <span id="customerEmail"></span><br>
              <strong>Date Ordered:</strong> <span id="dateOrdered"></span><br>
              <strong>Mode of Payment:</strong> <span id="mop"></span>
            </div>
          </div>

          <!-- Online Receipt Section -->
          <div class="online-receipt-section">
            <hr>
            <h5 class="mb-3 fw-bold">Online Payment Receipt</h5>
            <div class="text-center">
              <img id="receiptImage" src="../Images/sample-receipt.png" alt="Receipt" class="img-fluid"
                style="max-height: 400px;">
            </div>
          </div>

        </div>

        <!-- Replace the existing modal footer with this -->
        <div class="modal-footer">
            <div id="receiptActions">
                <button type="button" class="btn btn-primary confirm-receipt">Confirm Receipt</button>
                <button type="button" class="btn btn-danger invalid-receipt">Invalid Receipt</button>
            </div>
        </div>

      </div>
    </div>
  </div>

  <script>
document.addEventListener('DOMContentLoaded', function() {
    const completeButtons = document.querySelectorAll('.complete-order');
    
    completeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const receiptId = this.getAttribute('data-receipt-id');
            
            updateOrderStatus(receiptId);
        });
    });
});

function updateOrderStatus(receiptId) {
    if (confirm('Are you sure you want to complete this order? This will update product stock levels.')) {
        fetch('update_order_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'receipt_id=' + encodeURIComponent(receiptId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let message = 'Order completed successfully.\n';
                if (data.stockUpdates) {
                    message += '\nStock levels updated:';
                    data.stockUpdates.forEach(update => {
                        message += `\n- ${update.product_name}: ${update.old_stock} → ${update.new_stock}`;
                    });
                }
                alert(message);
                location.reload();
            } else {
                if (data.error === 'insufficient_stock') {
                    let errorMessage = 'Cannot complete order due to insufficient stock:\n\n';
                    data.details.forEach(item => {
                        if (item.error) {
                            errorMessage += `${item.product_name}: ${item.error}\n`;
                        } else {
                            errorMessage += `${item.product_name}: Requested: ${item.requested}, Available: ${item.available}\n`;
                        }
                    });
                    alert(errorMessage);
                } else {
                    alert('Error: ' + (data.error || 'Unknown error occurred'));
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating order status. Please try again.');
        });
    }
}
</script>

  <script>
document.addEventListener('DOMContentLoaded', function() {
    const orderModal = document.getElementById('orderDetailsModal');
    
    // Add confirm receipt button handler
    const confirmReceiptBtn = orderModal.querySelector('.btn-primary');
    confirmReceiptBtn.addEventListener('click', function() {
        const receiptId = document.getElementById('customerID').textContent;
        
        if(confirm('Confirm this receipt? This will change the order status to "ToPickUp"')) {
            updateReceiptStatus(receiptId);
        }
    });
    
    document.querySelectorAll('.view-details').forEach(button => {
        button.addEventListener('click', function() {
            const receiptId = this.getAttribute('data-receipt-id');
            const customer = this.getAttribute('data-customer');
            const email = this.getAttribute('data-email');
            const date = this.getAttribute('data-date');
            const payment = this.getAttribute('data-payment');
            const products = JSON.parse(this.getAttribute('data-products'));
            
            // Update customer information
            document.getElementById('customerID').textContent = receiptId;
            document.getElementById('customerName').textContent = customer;
            document.getElementById('customerEmail').textContent = email;
            document.getElementById('dateOrdered').textContent = date;
            document.getElementById('mop').textContent = payment;
            
            // Update product details with images
            const productDetails = document.getElementById('productDetails');
            productDetails.innerHTML = products.map(product => `
                <tr>
                    <td>
                        <img src="${product.image}" 
                             alt="${product.product_name}" 
                             class="product-thumbnail"
                             style="width: 50px; height: 50px; object-fit: cover;">
                    </td>
                    <td>${product.product_name}</td>
                    <td>${product.quantity}</td>
                    <td>₱${parseFloat(product.price).toFixed(2)}</td>
                    <td>₱${parseFloat(product.subtotal).toFixed(2)}</td>
                </tr>
            `).join('');
            
            // Show/hide receipt section and confirm button based on payment method
            const receiptSection = orderModal.querySelector('.online-receipt-section');
            const confirmButton = orderModal.querySelector('.btn-primary');
            
            if (payment === 'Cash (Pay at the Counter)') {
                receiptSection.style.display = 'none';
                confirmButton.style.display = 'none';
            } else {
                receiptSection.style.display = 'block';
                confirmButton.style.display = 'inline-block';
            }

            // Update receipt image if available
            const receiptImage = orderModal.querySelector('#receiptImage');
            fetch('get_receipt_image.php?receipt_id=' + receiptId)
                .then(response => response.json())
                .then(data => {
                    if (data.image_path) {
                        receiptImage.src = '../' + data.image_path;
                        receiptSection.style.display = 'block';
                    } else {
                        receiptSection.style.display = 'none';
                    }
                });

            // Check if receipt is already confirmed
            fetch('check_receipt_status.php?receipt_id=' + receiptId)
                .then(response => response.json())
                .then(data => {
                    const receiptActions = orderModal.querySelector('#receiptActions');
                    const confirmReceiptBtn = orderModal.querySelector('.confirm-receipt');
                    const invalidReceiptBtn = orderModal.querySelector('.invalid-receipt');

                    if (data.status === 'ToPickUp' || data.status === 'Complete') {
                        // Hide both buttons if order is already confirmed
                        receiptActions.style.display = 'none';
                    } else if (data.status === 'Cancelled') {
                        // Hide both buttons if order is cancelled
                        receiptActions.style.display = 'none';
                    } else if (payment === 'Cash (Pay at the Counter)') {
                        // Hide both buttons for cash payments
                        receiptActions.style.display = 'none';
                    } else {
                        // Show both buttons for pending online receipt orders
                        receiptActions.style.display = 'block';
                        confirmReceiptBtn.style.display = 'inline-block';
                        invalidReceiptBtn.style.display = 'inline-block';
                    }
                });
        });
    });

    // Invalid receipt button handler
    const invalidReceiptBtn = orderModal.querySelector('.invalid-receipt');
    invalidReceiptBtn.addEventListener('click', function() {
        const receiptId = document.getElementById('customerID').textContent;
        
        if(confirm('Mark this receipt as invalid? This will cancel the order.')) {
            fetch('invalidate_receipt.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'receipt_id=' + receiptId
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Receipt marked as invalid. Order has been cancelled.');
                    location.reload(); // Refresh the page
                } else {
                    alert('Error updating receipt status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating receipt status');
            });
        }
    });
});

// Replace or update the updateReceiptStatus function
function updateReceiptStatus(receiptId) {
    fetch('update_receipt_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'receipt_id=' + receiptId
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Receipt confirmed! Order status updated to To Pick Up.');
            location.reload(); // Refresh the page to show updated status
        } else {
            alert('Error updating receipt status: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating receipt status');
    });
}
</script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const tabButtons = document.querySelectorAll('.tab-button');

      tabButtons.forEach(button => {
        button.addEventListener('click', () => {

          tabButtons.forEach(btn => btn.classList.remove('active'));
          button.classList.add('active');


          let filter = button.textContent.trim();
          if (filter === 'Completed') filter = 'Complete';
          if (filter === 'Cancel') filter = 'Cancelled';
          if (filter === 'To Pick Up') filter = 'ToPickUp';
          if (filter === 'Pending') filter = 'Pending';
          if (filter === 'Returns') filter = 'Return';
          if (filter === 'Refund') filter = 'Refunded';


          document.querySelectorAll('table tbody tr').forEach(row => {
            const badge = row.querySelector('.status-badge');
            const status = badge
              ? Array.from(badge.classList).find(c => c !== 'status-badge')
              : '';

            if (filter === 'All Orders' || status === filter) {
              row.style.display = '';
            } else {
              row.style.display = 'none';
            }
          });
        });
      });
    });

    var calendar;

    document.addEventListener('DOMContentLoaded', function () {
      var calendarEl = document.getElementById('calendar');
      var now = new Date();
      var monthYear = now.toLocaleString('default', { month: 'long', year: 'numeric' });

      // Update button and modal title
      document.getElementById('calendarButton').innerHTML = '<i class="bi bi-calendar3 me-2"></i>' + monthYear;
      document.getElementById('calendarModalLabel').innerText = 'Monthly Transactions - ' + monthYear;

      // Initialize Calendar with dynamic events from PHP
      calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 600,
        initialDate: now,
        events: <?php echo json_encode($calendarEvents); ?>,
        eventClick: function(info) {
            // Handle event click - you can add functionality here
            alert('Orders on ' + info.event.startStr + ': ' + info.event.title);
        }
      });

      calendar.render();
    });

    // Rerender calendar after modal fully shown
    var calendarModal = document.getElementById('calendarModal');
    calendarModal.addEventListener('shown.bs.modal', function () {
      calendar.render();
    });

    document.getElementById('viewMonthButton').addEventListener('click', function () {
      var currentDate = calendar.getDate();
      var currentMonth = currentDate.getMonth() + 1;
      var currentYear = currentDate.getFullYear();

      // Filter orders for the selected month
      filterOrdersByMonth(currentMonth, currentYear);

      // Close the modal
      var modal = bootstrap.Modal.getInstance(document.getElementById('calendarModal'));
      modal.hide();
    });

    function filterOrdersByMonth(month, year) {
      // Add AJAX call to filter orders
      fetch(`filter_orders.php?month=${month}&year=${year}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Refresh the page or update the orders table
            location.reload();
          } else {
            alert('Error filtering orders');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('Error filtering orders');
        });
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>





</body>

</html>