<?php

require 'inc/config.php'; // Database connection

session_start();
$where_clause = "";

if (isset($_SESSION['filter_month']) && isset($_SESSION['filter_year'])) {
    $month = $_SESSION['filter_month'];
    $year = $_SESSION['filter_year'];
    $where_clause = "WHERE MONTH(o.order_date) = $month AND YEAR(o.order_date) = $year";
}

// Add status filter
if (isset($_GET['status']) && $_GET['status'] !== '') {
    $status = $conn->real_escape_string($_GET['status']);
    if ($where_clause === "") {
        $where_clause = "WHERE r.order_status = '$status'";
    } else {
        $where_clause .= " AND r.order_status = '$status'";
    }
}

if (isset($_GET['payment_method']) && $_GET['payment_method'] !== '') {
    $payment_method = $conn->real_escape_string($_GET['payment_method']);
    if ($where_clause === "") {
        $where_clause = "WHERE o.payment_method = '$payment_method'";
    } else {
        $where_clause .= " AND o.payment_method = '$payment_method'";
    }
}

if (!empty($_GET['from_date']) && !empty($_GET['to_date'])) {
    $from_date = $conn->real_escape_string($_GET['from_date']);
    $to_date = $conn->real_escape_string($_GET['to_date']);
    if ($where_clause === "") {
        $where_clause = "WHERE o.order_date BETWEEN '$from_date' AND '$to_date'";
    } else {
        $where_clause .= " AND o.order_date BETWEEN '$from_date' AND '$to_date'";
    }
} elseif (!empty($_GET['from_date'])) {
    $from_date = $conn->real_escape_string($_GET['from_date']);
    if ($where_clause === "") {
        $where_clause = "WHERE o.order_date >= '$from_date'";
    } else {
        $where_clause .= " AND o.order_date >= '$from_date'";
    }
} elseif (!empty($_GET['to_date'])) {
    $to_date = $conn->real_escape_string($_GET['to_date']);
    if ($where_clause === "") {
        $where_clause = "WHERE o.order_date <= '$to_date'";
    } else {
        $where_clause .= " AND o.order_date <= '$to_date'";
    }
}

// Set number of items per page
$items_per_page = 10;

// Get current page number from URL parameter
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
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

// Add this query after your existing $sql query
$sql_images = "SELECT ri.image_path 
               FROM order_receipts_images ri 
               WHERE ri.receipt_id = ?";

// Create an array to group orders by receipt_id
$grouped_orders = [];
while ($row = $results->fetch_assoc()) {
    $receipt_id = $row['receipt_id'];
    if (!isset($grouped_orders[$receipt_id])) {
        // Prepare statement for receipt image
        $stmt = $conn->prepare($sql_images);
        $stmt->bind_param("s", $receipt_id);
        $stmt->execute();
        $result_image = $stmt->get_result();
        $image_row = $result_image->fetch_assoc();
        $image_path = $image_row ? $image_row['image_path'] : null;

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
            'total_amount' => 0,
            'receipt_image' => $image_path // Add this line
        ];
    }

    $subtotal = $row['quantity'] * $row['price'];

    $grouped_orders[$receipt_id]['products'][] = [
        'product_name' => $row['product_name'],
        'quantity' => $row['quantity'],
        'price' => $row['price'],
        'subtotal' => $subtotal,
        'image' => $row['product_image'] // Add this line
    ];
    $grouped_orders[$receipt_id]['total_amount'] += $subtotal;
}

// Get calendar events for orders
function getCalendarEvents()
{
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

        while ($row = $result->fetch_assoc()) {
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
    <?php include 'links.php'; ?>
    <style>
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

        .Refund {
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

        .ToPickUp {
            background-color: #ADD8E6;
            color: #00527F;
            border: none;
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
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Toggle Button -->
            <!-- Top Navbar (visible only on small devices) -->
            <nav class="navbar navbar-light bg-light d-md-none">
                <div class="container-fluid d-flex justify-content-between align-items-center">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu"
                        aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <img src="images/vmc_basket_logo.png" alt="VMC Logo" class="vmc-logo img-fluid">
                </div>
            </nav>

            <!-- Sidebar -->
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-white sidebar collapse">

                <div class="text-center py-3 d-none d-md-block">
                    <img src="images/vmc_basket_logo.png" alt="VMC Logo" class="vmc-logo img-fluid">
                </div>

                <ul class="nav flex-column px-2 mb-3 mt-4 mt-md-0">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link">
                            <i class="bi bi-house-door me-2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="orders.php" class="nav-link active">
                            <i class="bi bi-bag-check me-2"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="prod.php" class="nav-link">
                            <i class="bi bi-box-seam me-2"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="cus.php" class="nav-link">
                            <i class="bi bi-people me-2"></i> Students
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="chat.php" class="nav-link">
                            <i class="bi bi-chat-dots me-2"></i> Chat
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="ratings.php" class="nav-link">
                            <i class="bi bi-list-stars me-2"></i> Ratings & Reviews
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="accounting.php" class="nav-link">
                            <i class="bi bi-receipt me-2"></i> Receipt Form
                        </a>
                    </li>
                    <!-- Logout for small screens (visible only on xs/sm) -->
                    <li class="nav-item d-block d-md-none">
                        <a href="logout.php" class="nav-link text-danger fw-semibold">
                            <i class="bi bi-box-arrow-right me-2"></i> Log Out
                        </a>
                    </li>
                </ul>
                <!-- Logout at the bottom for md/lg screens -->
                <div class="position-absolute w-100 d-none d-md-block" style="bottom: 30px; left: 0;">
                    <ul class="nav flex-column px-2">
                        <li class="nav-item">
                            <a href="logout.php" class="nav-link text-danger fw-semibold">
                                <i class="bi bi-box-arrow-right me-2"></i> Log Out
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Content Area -->
            <!-- Title Page and Search -->
            <main class="col-md-9 ms-sm-auto col-lg-10 content p-3">
                <div class="d-flex justify-content-end mb-5">
                    <div class="search-container">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search orders...">
                        <button><i class="bi bi-search"></i></button>
                    </div>
                </div>
                <div class="mt-2 mb-5">
                    <h2>Orders</h2>
                </div>
                <div class="col-12 col-md mb-3">
                    <form class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center" method="get"
                        action="orders.php" style="gap: 8px;">
                        <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center w-100">
                            <!-- NOTE: inputs now return values in YYYY-MM format.
                                 Update backend to convert them to full date ranges:
                                 Example in PHP:
                                 if (!empty($_GET['from_date'])) $from_date = $_GET['from_date'] . '-01';
                                 if (!empty($_GET['to_date']))   $to_date   = date('Y-m-t', strtotime($_GET['to_date'] . '-01'));
                            -->
                            <label for="from_date" class="form-label mb-1 mb-sm-0 me-sm-1"
                                style="font-size: 15px;"><strong>From</strong></label>
                            <input type="month" class="form-control date-filter mb-2 mb-sm-0" id="from_date"
                                name="from_date" value="<?= htmlspecialchars($_GET['from_date'] ?? '') ?>">
                            <label for="to_date" class="form-label mb-1 mb-sm-0 ms-sm-2 me-sm-1"
                                style="font-size: 15px;"><strong>To</strong></label>
                            <input type="month" class="form-control date-filter mb-2 mb-sm-0" id="to_date" name="to_date"
                                value="<?= htmlspecialchars($_GET['to_date'] ?? '') ?>">
                            <button type="submit" class="admin-btn ms-sm-2">Filter</button>
                        </div>
                    </form>
                </div>
                <div class="mt-4 mb-3">
                    <strong>Total Orders: <?= $total_records ?></strong>
                </div>

                <!-- Bulk Delete Button (hidden by default) -->
                <div id="bulkDeleteContainer" style="display:none; margin-top: 16px;">
                    <button id="bulkDeleteBtn" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Delete Selected
                    </button>
                </div>

                <!-- TABLE -->
                <div class="table-responsive">
                    <div class="d-flex">
                        <a href="orders.php" class="tab-button <?= !isset($_GET['status']) ? 'active' : '' ?>">All
                            Orders</a>
                        <a href="orders.php?status=Pending"
                            class="tab-button <?= ($_GET['status'] ?? '') === 'Pending' ? 'active' : '' ?>">Pending</a>
                        <a href="orders.php?status=ToPickUp"
                            class="tab-button <?= ($_GET['status'] ?? '') === 'ToPickUp' ? 'active' : '' ?>">To Pick
                            Up</a>
                        <a href="orders.php?status=Complete"
                            class="tab-button <?= ($_GET['status'] ?? '') === 'Complete' ? 'active' : '' ?>">Completed</a>
                        <a href="orders.php?status=Cancelled"
                            class="tab-button <?= ($_GET['status'] ?? '') === 'Cancelled' ? 'active' : '' ?>">Cancelled</a>
                        <a href="orders.php?status=Refund Requested"
                            class="tab-button <?= ($_GET['status'] ?? '') === 'Refund Requested' ? 'active' : '' ?>">Refund Requested</a>
                        <a href="orders.php?status=Refunded"
                            class="tab-button curve-tab <?= ($_GET['status'] ?? '') === 'Refunded' ? 'active' : '' ?>">Refunded</a>
                    </div>
                    <table class="table table-container">
                        <thead>
                            <tr>

                                <th>#</th>
                                <th>Product Details</th>
                                <th>Customer Details</th>
                                <th class="align-middle text-center">
                                    <div class="dropdown">
                                        <button class="btn p-0 m-0 align-baseline table-dropdown dropdown-toggle"
                                            type="button" id="mopDropdown" data-bs-toggle="dropdown"
                                            aria-expanded="false" style="text-decoration:none;">
                                            MOP
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="mopDropdown">
                                            <li><a class="dropdown-item"
                                                    href="orders.php<?= isset($_GET['status']) ? '?status=' . urlencode($_GET['status']) : '' ?>">All</a>
                                            </li>
                                            <li><a class="dropdown-item"
                                                    href="orders.php?<?= isset($_GET['status']) ? 'status=' . urlencode($_GET['status']) . '&' : '' ?>payment_method=Send Online Receipt">Send
                                                    Online Receipt (Gcash)</a></li>
                                            <li><a class="dropdown-item"
                                                    href="orders.php?<?= isset($_GET['status']) ? 'status=' . urlencode($_GET['status']) . '&' : '' ?>payment_method=Cash (Pay at the Counter)">Cash
                                                    (Pay at the Counter)</a></li>
                                        </ul>
                                    </div>
                                </th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            <?php $count = 1;
                            foreach ($grouped_orders as $order): ?>
                                <tr>

                                    <td><?= $count++ ?></td>
                                    <td>
                                        <div class="d-flex flex-column h-100 justify-content-between">
                                            <div>
                                                <?php foreach ($order['products'] as $product): ?>
                                                    <div class="d-flex align-items-center mb-2">
                                                        <img src="<?= $product['image'] ?>"
                                                            alt="<?= $product['product_name'] ?>" class="product-thumbnail me-2"
                                                            style="width: 40px; height: 40px; object-fit: cover;">
                                                        <span>
                                                            <?= $product['product_name'] ?> (x<?= $product['quantity'] ?>) -
                                                            ₱<?= number_format($product['price'], 2) ?>
                                                        </span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <div class="mt-2">
                                                <strong>Total Amount:
                                                </strong>₱<?= number_format($order['total_amount'], 2) ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>Receipt No.: </strong><?= $order['receipt_id'] ?><br>
                                        <strong>Student Name: </strong><?= $order['customer_name'] ?><br>
                                        <strong>Customer Type:
                                        </strong><?= $order['user_id'] ? 'Registered Student' : 'Walk-in Customer' ?><br>
                                        <strong>Date Ordered: </strong><?= $order['date_ordered'] ?><br>

                                    </td>
                                    <td class="text-center">
                                        <?php
                                        // Break long payment method text for better table fit
                                        $mop = $order['payment_method'];
                                        if (strlen($mop) > 20) {
                                            // Insert a <br> after 20 characters or at the first space after 15 chars
                                            $breakAt = strpos($mop, ' ', 15);
                                            if ($breakAt !== false && $breakAt < strlen($mop) - 1) {
                                                $mop = substr($mop, 0, $breakAt) . '<br>' . substr($mop, $breakAt + 1);
                                            } else {
                                                $mop = wordwrap($mop, 20, '<br>', true);
                                            }
                                        }
                                        echo $mop;
                                        ?>
                                        <br>
                                    </td>
                                    <td class="text-center"><span
                                            class="status-badge <?= $order['order_status'] ?>"><?= $order['order_status'] ?></span>
                                    </td>
                                    <td>
                                        <div class="dropdown text-center">
                                            <button class="btn btn-link p-0" type="button"
                                                id="actionDropdown<?= $order['receipt_id'] ?>" data-bs-toggle="dropdown"
                                                aria-expanded="false" style="font-size: 1.5rem; color: #333;">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="actionDropdown<?= $order['receipt_id'] ?>">
                                                <li>
                                                    <button class="dropdown-item view-details" type="button" data-bs-toggle="modal"
                                                        data-bs-target="#orderDetailsModal"
                                                        data-receipt-id="<?= $order['receipt_id'] ?>"
                                                        data-customer="<?= htmlspecialchars($order['customer_name']) ?>"
                                                        data-email="<?= htmlspecialchars($order['email']) ?>"
                                                        data-date="<?= $order['date_ordered'] ?>"
                                                        data-payment="<?= htmlspecialchars($order['payment_method']) ?>"
                                                        data-products='<?= json_encode($order['products']) ?>'
                                                        data-total="<?= $order['total_amount'] ?>"
                                                        data-receipt-image='<?= htmlspecialchars($order['receipt_image']) ?>'>
                                                        <i class="bi bi-file-text me-2"></i>View Details
                                                    </button>
                                                </li>

                                                <?php if ($order['order_status'] == 'Refund Requested'): ?>
                                                    <li>
                                                        <button class="dropdown-item approve-refund" type="button"
                                                            data-receipt-id="<?= $order['receipt_id'] ?>"
                                                            data-products='<?= json_encode($order['products']) ?>'>
                                                            <i class="bi bi-check-circle me-2"></i>Approve Refund
                                                        </button>
                                                    </li>
                                                <?php endif; ?>

                                                <?php
                                                // Place "To Pick Up" inside dropdown when applicable
                                                if (
                                                    ($order['payment_method'] == 'Cash (Pay at the Counter)' && $order['order_status'] == 'Pending')
                                                    || ($order['payment_method'] == 'Send Online Receipt' && $order['order_status'] == 'ToPickUp')
                                                ): ?>
                                                    <li>
                                                        <button class="dropdown-item topickup-order" type="button"
                                                            data-receipt-id="<?= $order['receipt_id'] ?>">
                                                            <i class="bi bi-check-circle me-2"></i>To Pick Up
                                                        </button>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <nav aria-label="Page navigation" class="d-flex justify-content-end mt-3">
                    <ul class="pagination justify-content-center custom-pagination">
                        <?php if ($total_pages > 1): ?>
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page - 1 ?>" <?= ($page <= 1) ? 'tabindex="-1" aria-disabled="true"' : '' ?>><span aria-hidden="true">&lt;</span></a>
                            </li>

                            <?php
                            // Calculate range of pages to show
                            $start_page = max(1, min($page - 2, $total_pages - 4));
                            $end_page = min($total_pages, max(5, $page + 2));

                            // Show first page if not in range
                            if ($start_page > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                if ($start_page > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }

                            // Show page numbers
                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor;

                            // Show last page if not in range
                            if ($end_page < $total_pages) {
                                if ($end_page < $total_pages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
                            }
                            ?>

                            <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page + 1 ?>" <?= ($page >= $total_pages) ? 'tabindex="-1" aria-disabled="true"' : '' ?>><span aria-hidden="true">&gt;</span></a>
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
                            <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total Amount:</strong></td>
                                    <td id="modalTotalAmount">₱0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <hr>

                    <!-- Customer Info -->
                    <h5 class="mb-3 fw-bold">Customer Information</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
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
                            <img id="receiptImage" src="uploads/receipts/<?= basename($order['receipt_image']) ?>"
                                alt="Receipt" class="img-fluid" style="max-height: 400px;"
                                onerror="this.onerror=null; this.src='';">
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
        document.addEventListener('DOMContentLoaded', function () {
            const toPickupButtons = document.querySelectorAll('.topickup-order');

            toPickupButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const receiptId = this.getAttribute('data-receipt-id');
                    updateToPickup(receiptId);
                });
            });
        });

        function showBootstrapAlert(message, type = 'success') {
            // Remove existing alert if present
            let existingAlert = document.getElementById('customBootstrapAlert');
            if (existingAlert) existingAlert.remove();

            // Create alert element
            const alertDiv = document.createElement('div');
            alertDiv.id = 'customBootstrapAlert';
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 mt-3 me-3 shadow`;
            alertDiv.style.zIndex = '9999';
            alertDiv.style.minWidth = '300px';
            alertDiv.innerHTML = `
                  <div class="d-flex align-items-center">
                    <span class="me-2">
                      ${type === 'success' ? '<i class="bi bi-check-circle-fill text-success"></i>' :
                    type === 'danger' ? '<i class="bi bi-x-circle-fill text-danger"></i>' :
                        type === 'warning' ? '<i class="bi bi-exclamation-triangle-fill text-warning"></i>' :
                            '<i class="bi bi-info-circle-fill text-info"></i>'}
                    </span>
                    <span>${message}</span>
                  </div>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
            document.body.appendChild(alertDiv);

            // Auto-dismiss after 2.5 seconds
            setTimeout(() => {
                alertDiv.classList.remove('show');
                alertDiv.classList.add('hide');
                setTimeout(() => alertDiv.remove(), 500);
            }, 2500);
        }

        function updateToPickup(receiptId) {
            let modal = document.getElementById('confirmPickupModal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'confirmPickupModal';
                modal.className = 'modal fade';
                modal.tabIndex = -1;
                modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h4 class="modal-title">Confirmation</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure this item is already paid and ready for pickup?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="confirmPickupBtn">Confirm</button>
                    </div>
                </div>
            </div>
        `;
                document.body.appendChild(modal);
            }

            // Show modal
            var bsModal = new bootstrap.Modal(modal);
            bsModal.show();

            // Remove previous event listener if any
            const confirmBtn = document.getElementById('confirmPickupBtn');
            confirmBtn.onclick = function () {
                bsModal.hide();
                fetch('update_order_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'receipt_id=' + encodeURIComponent(receiptId) + '&action=topickup'
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showBootstrapAlert('Order status updated to "To Pick Up" successfully.', 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showBootstrapAlert('Error: ' + (data.error || 'Unknown error occurred'), 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showBootstrapAlert('Error updating order status. Please try again.', 'danger');
                    });
            };
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Get modal element first
            const orderModal = document.getElementById('orderDetailsModal');
            if (!orderModal) {
                console.error('Order details modal not found');
                return;
            }

            // View details button handlers
            document.querySelectorAll('.view-details').forEach(button => {
                button.addEventListener('click', function () {
                    const productDetails = document.getElementById('productDetails');
                    if (!productDetails) {
                        console.error('Product details container not found');
                        return;
                    }

                    const products = JSON.parse(this.getAttribute('data-products') || '[]');

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

                    // Update other modal content...
                    updateModalContent(this);
                    orderModal.setAttribute('data-receipt-id', this.getAttribute('data-receipt-id'));
                });
            });
            
            // Helper function to update modal content
            function updateModalContent(button) {
                const receiptId = button.getAttribute('data-receipt-id');
                const customer = button.getAttribute('data-customer');
                const email = button.getAttribute('data-email');
                const date = button.getAttribute('data-date');
                const payment = button.getAttribute('data-payment');
                const receiptImage = button.getAttribute('data-receipt-image');

                // Update all text elements
                const elements = {
                    'customerID': receiptId,
                    'customerName': customer,
                    'customerEmail': email,
                    'dateOrdered': date,
                    'mop': payment
                };

                Object.entries(elements).forEach(([id, value]) => {
                    const element = document.getElementById(id);
                    if (element) {
                        element.textContent = value || 'N/A';
                    }
                });

                // Update receipt image

                const receiptImageElement = document.getElementById('receiptImage');
                if (receiptImageElement) {
                    if (receiptImage) {
                        // Use the correct path relative to admin folder
                        receiptImageElement.src = 'uploads/receipts/' + receiptImage.split('/').pop();
                        receiptImageElement.style.display = 'block';
                    } else {
                        receiptImageElement.src = ' ';
                        receiptImageElement.style.display = 'block';
                    }
                }


                // Update visibility of receipt sections
                const receiptSection = orderModal.querySelector('.online-receipt-section');
                // const confirmButton = orderModal.querySelector('.confirm-receipt');
                // const invalidButton = orderModal.querySelector('.invalid-receipt');
                const receiptActions = orderModal.querySelector('#receiptActions');

                if (payment === 'Cash (Pay at the Counter)') {
                    [receiptSection, receiptActions].forEach(el => {
                        if (el) el.style.display = 'none';
                    });
                } else {
                    [receiptSection, receiptActions].forEach(el => {
                        if (el) el.style.display = 'block';
                    });
                }
            }

            // Confirm Receipt Button Handler
            const confirmBtn = orderModal.querySelector('.confirm-receipt');
            if (confirmBtn) {
                confirmBtn.onclick = function () {
                    const receiptId = orderModal.getAttribute('data-receipt-id');
                    if (!receiptId) return;

                    confirmBtn.disabled = true;
                    const invalidBtn = orderModal.querySelector('.invalid-receipt');
                    if (invalidBtn) invalidBtn.disabled = true;

                    fetch('update_order_status.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'receipt_id=' + encodeURIComponent(receiptId) + '&action=topickup'
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showBootstrapAlert('Order status updated to "To Pick Up" successfully.', 'success');
                                // Hide buttons
                                const actionsDiv = orderModal.querySelector('#receiptActions');
                                if (actionsDiv) actionsDiv.style.display = 'none';
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showBootstrapAlert('Error: ' + (data.error || 'Unknown error occurred'), 'danger');
                                confirmBtn.disabled = false;
                                if (invalidBtn) invalidBtn.disabled = false;
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showBootstrapAlert('Error updating order status. Please try again.', 'danger');
                            confirmBtn.disabled = false;
                            if (invalidBtn) invalidBtn.disabled = false;
                        });
                };
            }

            // Invalid Receipt Button Handler
            const invalidBtn = orderModal.querySelector('.invalid-receipt');
            if (invalidBtn) {
                invalidBtn.onclick = function () {
                    const receiptId = orderModal.getAttribute('data-receipt-id');
                    if (!receiptId) return;

                    invalidBtn.disabled = true;
                    if (confirmBtn) confirmBtn.disabled = true;

                    fetch('update_order_status.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'receipt_id=' + encodeURIComponent(receiptId) + '&action=cancelled'
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showBootstrapAlert('Order status updated to "Cancelled".', 'success');
                                // Hide buttons
                                const actionsDiv = orderModal.querySelector('#receiptActions');
                                if (actionsDiv) actionsDiv.style.display = 'none';
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                showBootstrapAlert('Error: ' + (data.error || 'Unknown error occurred'), 'danger');
                                invalidBtn.disabled = false;
                                if (confirmBtn) confirmBtn.disabled = false;
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showBootstrapAlert('Error updating order status. Please try again.', 'danger');
                            invalidBtn.disabled = false;
                            if (confirmBtn) confirmBtn.disabled = false;
                        });
                };
            }
        });




        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('searchInput');
            const tableRows = document.querySelectorAll('.table-container tbody tr');

            searchInput.addEventListener('input', function () {
                const searchTerm = this.value.toLowerCase().trim();

                tableRows.forEach(function (row) {
                    const rowText = row.textContent.toLowerCase();

                    if (rowText.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Update visible row numbers
                updateRowNumbers();
            });

            function updateRowNumbers() {
                const visibleRows = Array.from(tableRows).filter(row => row.style.display !== 'none');
                visibleRows.forEach((row, index) => {
                    const numberCell = row.querySelector('td:nth-child(2)');
                    if (numberCell) {
                        numberCell.textContent = index + 1;
                    }
                });
            }
        });


        var calendar;

        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendar');

            // Only initialize calendar if element exists
            if (calendarEl) {
                var now = new Date();
                var monthYear = now.toLocaleString('default', { month: 'long', year: 'numeric' });

                // Update button and modal title with null checks
                const calendarButton = document.getElementById('calendarButton');
                const calendarModalLabel = document.getElementById('calendarModalLabel');

                if (calendarButton) {
                    calendarButton.innerHTML = '<i class="bi bi-calendar3 me-2"></i>' + monthYear;
                }

                if (calendarModalLabel) {
                    calendarModalLabel.innerText = 'Monthly Transactions - ' + monthYear;
                }

                // Initialize Calendar with dynamic events from PHP
                calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    height: 600,
                    initialDate: now,
                    events: <?php echo json_encode($calendarEvents); ?>,
                    eventClick: function (info) {
                        // Handle event click - you can add functionality here
                        alert('Orders on ' + info.event.startStr + ': ' + info.event.title);
                    }
                });

                calendar.render();

                // Rerender calendar after modal fully shown - ONLY if modal exists
                var calendarModal = document.getElementById('calendarModal');
                if (calendarModal) {
                    calendarModal.addEventListener('shown.bs.modal', function () {
                        calendar.render();
                    });
                }

                // View month button - ONLY if button exists
                const viewMonthButton = document.getElementById('viewMonthButton');
                if (viewMonthButton) {
                    viewMonthButton.addEventListener('click', function () {
                        var currentDate = calendar.getDate();
                        var currentMonth = currentDate.getMonth() + 1;
                        var currentYear = currentDate.getFullYear();

                        // Filter orders for the selected month
                        filterOrdersByMonth(currentMonth, currentYear);

                        // Close the modal
                        var modal = bootstrap.Modal.getInstance(document.getElementById('calendarModal'));
                        if (modal) {
                            modal.hide();
                        }
                    });
                }
            }
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Patch the view-details handler to update the total in the modal
            document.querySelectorAll('.view-details').forEach(button => {
                button.addEventListener('click', function () {
                    const products = JSON.parse(this.getAttribute('data-products'));
                    let total = 0;
                    products.forEach(product => {
                        total += parseFloat(product.subtotal);
                    });
                    document.getElementById('modalTotalAmount').textContent = '₱' + total.toFixed(2);
                });
            });
        });


        document.addEventListener('DOMContentLoaded', function() {
    // Handle refund approval
    document.querySelectorAll('.approve-refund').forEach(button => {
        button.addEventListener('click', function () {
            const receiptId = this.getAttribute('data-receipt-id');
            const products = JSON.parse(this.getAttribute('data-products') || '[]');

            // Create modal if it doesn't exist
            let modal = document.getElementById('refundDetailsModal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'refundDetailsModal';
                modal.className = 'modal fade';
                modal.tabIndex = -1;
                modal.innerHTML = `
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Refund Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Receipt No.:</strong> <span id="refundReceiptId"></span></p>
                                <div class="table-responsive shadow-none mb-2">
                                    <table class="table table-sm text-center vertical-align-middle">
                                        <thead>
                                            <tr>
                                                <th>Image</th>
                                                <th>Product</th>
                                                <th>Qty</th>
                                                <th>Price</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody id="refundProducts"></tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                                <td id="refundTotalAmount">₱0.00</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="mb-2">
                                    <p><strong>Reason for Return</strong></p>
                                    <p id="refundNotes"></p>
                                </div>
                                <div class="mb-2">
                                    <p><strong>Photos</strong></p>
                                    <p id="refundPhotos"></p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" id="refundApproveBtn" class="btn btn-success">Approve Refund</button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            }

            // Populate modal contents
            const refundReceiptEl = modal.querySelector('#refundReceiptId');
            const refundProductsTbody = modal.querySelector('#refundProducts');
            const refundTotalEl = modal.querySelector('#refundTotalAmount');
            const refundNotesEl = modal.querySelector('#refundNotes');

            refundReceiptEl.textContent = receiptId;
            refundNotesEl.value = '';

            let total = 0;
            refundProductsTbody.innerHTML = products.map(p => {
                const img = p.image ? `<img src="${p.image}" alt="${p.product_name}" style="width:50px;height:50px;object-fit:cover;border:1px solid #ddd;">` : '';
                const price = parseFloat(p.price || 0);
                const qty = parseInt(p.quantity || 0, 10);
                const subtotal = parseFloat(p.subtotal || (price * qty));
                total += subtotal;
                return `<tr>
                            <td>${img}</td>
                            <td>${p.product_name || 'N/A'}</td>
                            <td>${qty}</td>
                            <td>₱${price.toFixed(2)}</td>
                            <td>₱${subtotal.toFixed(2)}</td>
                        </tr>`;
            }).join('');

            refundTotalEl.textContent = '₱' + total.toFixed(2);

            // Show modal
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();

            // Wire approve button (replace previous handler to avoid duplicates)
            const approveBtn = modal.querySelector('#refundApproveBtn');
            approveBtn.onclick = function () {
                approveBtn.disabled = true;
                const notes = (refundNotesEl.value || '').trim();

                // pass notes if needed by backend: we attach to products object for now
                const payloadProducts = products.map(p => Object.assign({}, p));

                // Optionally include notes in the approve action by adding to the request body
                // approveRefund currently sends receiptId and products; you can adjust backend to accept notes.
                approveRefund(receiptId, payloadProducts.concat([{ _notes: notes }]));

                // hide modal after calling
                bsModal.hide();
            };
        });
    });

    function approveRefund(receiptId, products) {
        fetch('approve_refund.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                receipt_id: receiptId,
                products: products
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showBootstrapAlert('Refund approved successfully', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showBootstrapAlert(data.message || 'Error approving refund', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showBootstrapAlert('Error approving refund', 'danger');
        });
    }
});
    </script>



</body>

</html>