<?php
require 'inc/config.php';

// Get dashboard statistics
function getDashboardStats() {
    global $conn;
    
    $currentMonth = date('m');
    $currentYear = date('Y');
    
    $stats = [
        'total_orders' => 0,
        'new_orders' => 0,
        'return_orders' => 0,
        'cancelled_orders' => 0,
        'active_students' => 0,
        'new_messages' => 0,
        'new_reviews' => 0
    ];
    
    // Total Orders
    $sql = "SELECT COUNT(*) as total FROM orders 
            WHERE MONTH(order_date) = ? AND YEAR(order_date) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $currentMonth, $currentYear);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_orders'] = $result->fetch_assoc()['total'];
    
    // New Orders (Pending)
    $sql = "SELECT COUNT(*) as total FROM orders 
            WHERE status = 'Pending'";
    $result = $conn->query($sql);
    $stats['new_orders'] = $result->fetch_assoc()['total'];
    
    // Return Orders
    $sql = "SELECT COUNT(*) as total FROM orders 
            WHERE status = 'Refunded'";
    $result = $conn->query($sql);
    $stats['return_orders'] = $result->fetch_assoc()['total'];
    
    // Cancelled Orders
    $sql = "SELECT COUNT(*) as total FROM orders 
            WHERE status = 'Cancelled'";
    $result = $conn->query($sql);
    $stats['cancelled_orders'] = $result->fetch_assoc()['total'];
    
    // Active Students
    $sql = "SELECT COUNT(*) as total FROM users 
            WHERE active_status = 1";
    $result = $conn->query($sql);
    $stats['active_students'] = $result->fetch_assoc()['total'];
    
    // New Messages
    $sql = "SELECT COUNT(*) as total FROM inquiries 
            WHERE DATE(created_at) = CURDATE()";
    $result = $conn->query($sql);
    $stats['new_messages'] = $result->fetch_assoc()['total'];
    
    // New Reviews
    $sql = "SELECT COUNT(*) as total FROM product_reviews 
            WHERE DATE(created_at) = CURDATE()";
    $result = $conn->query($sql);
    $stats['new_reviews'] = $result->fetch_assoc()['total'];
    
    return $stats;
}

// Get low stock product
function getLowStockProduct() {
    global $conn;
    
    $sql = "SELECT p.product_name, pv.stock 
            FROM products p 
            JOIN product_variants pv ON p.id = pv.product_id 
            WHERE pv.stock <= 50 
            ORDER BY pv.stock ASC 
            LIMIT 1";
            
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return ['product_name' => 'No products', 'stock' => 0];
}

// Get the stats
$dashboardStats = getDashboardStats();
$lowStock = getLowStockProduct();

// Include calendar events
include 'get_calendar_events.php';
$calendarEvents = getCalendarEvents();

// Include bestseller data
include 'get_bestsellers.php';
$chartData = getBestSellers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket - Admin/Dashboard</title>
    <?php include 'links.php'; ?>

    <style>
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

        /* Hover Day Cell */
        .fc-daygrid-day:hover {
            background-color: #e9ecef;
            cursor: pointer;
        }

        /* Style the day headers (Mon, Tue, Wed, etc.) */
        .fc-col-header-cell {
            background-color: #26387D; 
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

        #viewMonthButton {
            background-color: #26387D; 
        }

       /* Dashboard Card Base */
        .dashboard-card {
            border-radius: 15px;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 6px 12px rgba(0,0,0,0.08);
        }
        .dashboard-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 8px 18px rgba(0,0,0,0.12);
        }

        /* Gradient Backgrounds */
        .gradient-dark { background: linear-gradient(286deg, #26387D 1.72%, #4566E3 98.69%);}
        .gradient-green { background: linear-gradient(104deg, rgba(7, 148, 0, 0.50) 5.15%, rgba(2, 46, 0, 0.50) 102.95%); color: #033900; }
        .gradient-warning { background: linear-gradient(105deg, rgba(246, 201, 14, 0.50) 3.37%, rgba(225, 168, 0, 0.50) 97.92%); color: #666600; }
        .gradient-danger { background: linear-gradient(104deg, rgba(255, 77, 77, 0.50) 3.59%, rgba(225, 29, 72, 0.50) 97.4%); color: #B50B00; }
        .gradient-info { background: linear-gradient(135deg, #56ccf2, #2f80ed); color: #fff; }
        .gradient-yellow { background: linear-gradient(135deg, #f9f871, #f6c90e); }

        /* Stats */
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #fff;
        }
        .stat-number2 {
            font-size: 2rem;
            font-weight: 600;
        }
        .unit-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        /* Metric Center Style */
        .stat-center {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            color: #fff;
        }
        .dashboard-metric {
            font-size: 2.5rem;
            font-weight: 700;
        }

        /* Labels & Titles */
        .dashboard-label {
            font-size: 1rem;
            opacity: 0.9;
        }
        .chart-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #26387d;
        }

        /* Floating Icon */
        .icon-float {
            position: absolute;
            top: 12px;
            right: 12px;
            font-size: 20px;
            color: #fff;
            opacity: 0.9;
        }
        .icon-float:hover {
            opacity: 1;
        }
        .low-stock-card {
        border-radius: 15px;
        overflow: hidden;
        }

        .low-stock-card .card-header {
        font-size: 1rem;
        padding: 12px;
        }

        .low-stock-card .list-group-item {
        border: none;
        border-bottom: 1px solid #f1f1f1;
        padding: 8px 10px;
        font-size: 0.95rem;
        }

        .low-stock-card .list-group-item:last-child {
        border-bottom: none;
        }

        .low-stock-card img {
        border-radius: 5px;
        }

        .low-stock-card .card-footer {
        font-size: 0.9rem;
        }
        .low-stock-card .card-footer a:hover {
        text-decoration: underline;
        }
        .student-messages{
            background-color: #26387d;
        }
        .chart-container {
            position: relative;
            height: 350px;
            width: 100%;
        }


        @media (max-width: 576px) {
            .stat-number {
                font-size: 45px;
                bottom: 0px;
            }
             .dashboard-label {
                font-size: 15px;
             }
             .chart-container{
                height: 200px;
             }

        }

        .dashboard-card canvas {
            width: 100% !important;
            height: 100% !important;
        }

        @media (max-width: 768px) {
            .dashboard-card canvas {
                height: 200px !important;
            }
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
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
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
                    <a href="index.php" class="nav-link active">
                        <i class="bi bi-house-door me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="orders.php" class="nav-link">
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
            <main class="col-md-9 ms-sm-auto col-lg-10 content p-5">
                <div class="d-flex justify-content-end mb-5">
                    <div class="search-container">
                        <input type="text" class="form-control" placeholder="Search...">
                        <button><i class="bi bi-search"></i></button>
                    </div>
                </div>
                <div class="mt-2 mb-4">
                    <h2>Dashboard</h2>
                </div>

                <!-- Calendar Button -->
                <div class="d-flex justify-content-end mb-3"> 
                    <button id="calendarButton" type="button" class="admin-btn d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#calendarModal">
                    </button>
                </div>
                
                <!-- Calendar Modal -->
                    <div class="modal fade" id="calendarModal" tabindex="-1" aria-labelledby="calendarModalLabel" aria-hidden="true">
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
                            </div>
                            </div>
                        </div>
                    </div>
                
                <!-- Dashboard Content -->
                <div class="row g-3">
                    <!-- Top Row: Stats -->
                    <div class="col-md-7">
                        <div class="row g-3 h-100">
                            <!-- Total Orders -->
                            <div class="col-12 col-md-6">
                                <div class="dashboard-card stat-card gradient-dark h-100">
                                    <div class="text-center">
                                        <div class="stat-number"><?php echo $dashboardStats['total_orders']; ?></div>
                                        <p class="fw-semibold text-white mt-2">Total Orders</p>
                                    </div>
                                    <a href="orders.php" class="icon-float"><i class="bi bi-box-arrow-up-right"></i></a>
                                </div>
                            </div>

                            <!-- New Orders -->
                            <div class="col-md-6">
                                <div class="dashboard-card stat-card gradient-green h-100">
                                    <div class="text-center">
                                        <div class="dashboard-metric"><?php echo $dashboardStats['new_orders']; ?></div>
                                        <p class="fw-semibold mt-2">New Orders</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Cancelled Orders -->
                            <div class="col-md-6">
                                <div class="dashboard-card stat-card gradient-danger h-100">
                                    <div class="text-center">
                                        <div class="dashboard-metric"><?php echo $dashboardStats['cancelled_orders']; ?></div>
                                        <p class="fw-semibold mt-2">Cancelled Orders</p>
                                    </div>
                                </div>
                            </div>

                             <!-- Return Orders -->
                            <div class="col-md-6">
                                <div class="dashboard-card stat-card gradient-warning h-100">
                                    <div class="text-center">
                                        <div class="dashboard-metric"><?php echo $dashboardStats['return_orders']; ?></div>
                                        <p class="fw-semibold mt-2">Return Orders</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Low in Stocks -->
                    <div class="col-md-5">
                        <div class="dashboard-card card low-stock-card shadow-sm h-100">
                            <div class="card-header bg-danger text-white fw-semibold text-center">
                                Low in Stocks
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                        <img src="images/pencil.png" alt="Pencil" width="35" class="me-2">
                                            <div>
                                                <span class="fw-semibold text-danger">Pencil</span><br>
                                                <small class="text-muted">D.R. No: DR1234677</small>
                                            </div>
                                        </div>
                                        <span class="text-danger fw-semibold">10pc</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-footer text-center bg-white">
                                <a href="#" class="text-danger fw-semibold text-decoration-none small">See All <i class="bi bi-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Row -->
                <div class="row g-3 mt-3">
                    <!-- Chart beside stats -->
                    <div class="col-md-7">
                        <div class="dashboard-card bg-white p-3 rounded shadow-sm h-100">
                            <h5 class="mb-3 chart-title">Best Seller of the Month</h5>
                            <div class="chart-container">
                                <canvas id="bestSellerChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Active Students 
                    <div class="col-md-3">
                        <div class="dashboard-card gradient-dark text-white p-3 h-100">
                            <h6 class="fw-semibold">Active Students</h6>
                            <h2 class="stat-number2">
                                <?php echo $dashboardStats['active_students']; ?> 
                                <span class="unit-label"><strong>Students</strong></span>
                            </h2>
                        </div>
                    </div>-->

                    <!-- Student Messages -->
                     <div class="col-md-5">
                        <div class="dashboard-card card low-stock-card shadow-sm h-100">
                            <div class="card-header student-messages text-white fw-semibold text-center">
                                Student Messages
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex align-items-center">
                                        <div class="me-2">
                                            <img src="images/profile_pic.png" class="chat-avatar" alt="Profile Picture">
                                        </div>
                                        <div>
                                            <strong>Name</strong>
                                            <div class="small text-muted">Subject: Complain</div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-footer text-center bg-white">
                                <a href="chat.php" class="fw-semibold text-decoration-none small">See All <i class="bi bi-chevron-right"></i></a>
                            </div>
                        </div>
                    </div>

                    <!-- Ratings & Reviews
                    <div class="col-md-3">
                        <div class="dashboard-card gradient-yellow p-3 h-100 position-relative">
                            <h6 class="fw-semibold">Rating & Reviews</h6>
                            <h2 class="stat-number2">
                                <?php echo $dashboardStats['new_reviews']; ?> 
                                <span class="unit-label">new reviews</span>
                            </h2>
                            <a href="ratings.php" class="icon-float"><i class="bi bi-star"></i></a>
                        </div>
                    </div> -->
                </div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

    // For Calendar
    var calendar;

    document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var now = new Date();
    var monthYear = now.toLocaleString('default', { month: 'long', year: 'numeric' });

    // Update button and modal title
    document.getElementById('calendarButton').innerHTML = '<i class="bi bi-calendar3 me-2"></i>' + monthYear;
    document.getElementById('calendarModalLabel').innerText = 'Monthly Transactions - ' + monthYear;

    // Initialize Calendar
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 600,
        initialDate: now,
        events: <?php echo json_encode($calendarEvents); ?>
    });

    calendar.render();
    });

    // Rerender calendar after modal fully shown
    var calendarModal = document.getElementById('calendarModal');
    calendarModal.addEventListener('shown.bs.modal', function () {
    calendar.render();
    });

    document.getElementById('viewMonthButton').addEventListener('click', function() {
    var calendar = FullCalendar.getCalendar('calendar'); // Get the current FullCalendar instance
    var currentDate = calendar.getDate(); // Get the currently viewed month and year

    var currentMonth = currentDate.getMonth() + 1; // Months are 0-indexed
    var currentYear = currentDate.getFullYear();

    console.log("Selected Month:", currentMonth, "Year:", currentYear);

    // Example: Call your table updating function here
    updateTransactionTable(currentMonth, currentYear);

    // Close the modal after clicking
    var modal = bootstrap.Modal.getInstance(document.getElementById('calendarModal'));
    modal.hide();
    });

    // FOR CHART
    const ctx = document.getElementById('bestSellerChart').getContext('2d');
    const chartData = {
        labels: <?php echo json_encode($chartData['labels']); ?>,
        datasets: [{
            label: 'Units Sold',
            data: <?php echo json_encode($chartData['data']); ?>,
            backgroundColor: '#26387d',
            barPercentage: 0.6,
            categoryPercentage: 0.7
        }]
    };

    new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: { display: false },
                tooltip: {
                    backgroundColor: '#B0B8D7',
                    titleColor: '#26387d',
                    bodyColor: '#26387d',
                    borderColor: '#26387d',
                    borderWidth: 1,
                    padding: 12
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: '#f0f0f0',
                        borderColor: '#e0e0e0',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#26387d',
                        font: { size: 13, weight: 'bold' },
                        maxTicksLimit: 5,
                        callback: function(value) {
                            return value + ' units';
                        }
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        color: '#26387d',
                        font: { size: 12, weight: 'bold' },
                        maxRotation: 0,
                        minRotation: 0
                    }
                }
            },
            layout: {
                padding: { left: 10, right: 10, top: 10, bottom: 10 }
            }
        }
    });
</script>

</body>
</html>