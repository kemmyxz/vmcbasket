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
    <link rel="icon" href="images/vmc_basket_logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kulim+Park:ital,wght@0,200;0,300;0,400;0,600;0,700;1,200;1,300;1,400;1,600;1,700&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/style.css">

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
            background-color: #0d6efd; /* Bootstrap primary blue */
            color: white;
            border-radius: 10px;
            font-weight: 600;
            padding: 8px 16px;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        #calendarModal .btn:hover {
            background-color: #0b5ed7; /* Darker blue on hover */
        }

        /* Specific style for 'View This Month' button if you want */
        #viewMonthButton {
            background-color: #198754; 
        }

        /* DASHBOARD STYLES */
        .dashboard-card {
            box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease;
            background-image: url('./images/active-students.png');
        }

        .dashboard-card:hover {
            transform: translateY(-2px);
        }

        .card-dark {
            background-color: #00527F;
            color: white;
        }

        .card-light {
            background-color: #f8f9fa;
            color: #333;
            border: 1px solid #dee2e6;
        }

        .icon-float {
            position: absolute;
            top: 12px;
            right: 12px;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            text-decoration: none;
        }

        .stat-number {
            font-size: 64px;
            font-weight: 700;
            position: absolute;
            bottom: 12px;
            left: 16px;
        }

        .stat-number2 {
            font-size: 48px;
            font-weight: 700;
            position: absolute;
            bottom: 0px;
            left: 16px;
        }

        .unit-label {
            font-size: 1rem;
            font-weight: 400;
            opacity: 0.85;
        }

        .card-highlight {
            background-color: #f0f2ff;
            color: #3c4fe0;
        }

        .dashboard-metric {
            font-size: 48px;
            font-weight: bold;
        }

        /* Title text inside card */
        .dashboard-label {
            font-size: 20px;
        }

        .bottom-label{
            background-color: #00527F;
            color: white;
            font-size: 20px;
            font-weight: 500;
            padding: 10px;
        }

        /* Chart container */
        .chart-title {
            color: #00527F;
            font-size: 20px;
            font-weight: 700;
        }

        /* Bottom grid styling */
        .stocks-card{
            background-color: #E8261A;
            background-image: url('./images/active-students.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
        }

        .students-card{
            background-color: #00527F;
            background-image: url('./images/active-students.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
        }

        .message-card{
            background-color: #A9CEEA;
            background-image: url('./images/active-students.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
        }

        .ratings-card{
            background-color: #FBF4BC;
            background-image: url('./images/active-students.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
        }

        .sidebar a:hover img.receipt-icon {
            content: url('../Images/receipt-nav-clicked.png');
        }

        @media (max-width: 576px) {
            .stat-number {
                font-size: 45px;
                bottom: 0px;
            }
             .dashboard-label {
                font-size: 15px;
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
            <nav class="navbar navbar-light bg-light d-lg-none">
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
                        <a href="inquiries.php" class="nav-link">
                            <i class="bi bi-chat-dots me-2"></i> Messages
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
                    <li class="nav-item justify-content-end mt-lg-5">
                        <a href="logout.php" class="nav-link text-danger fw-semibold">
                            <i class="bi bi-box-arrow-right me-2"></i> Log Out
                        </a>
                    </li>
                </ul>
            </nav>
            
            <!-- Content Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 content">
                <div class="d-flex justify-content-end mb-5">
                    <div class="search-container">
                        <input type="text" class="form-control" placeholder="">
                        <button><img src="./images/search-icon.png" alt="Search"></button>
                    </div>
                </div>
                <div class="mt-2 d-flex flex-row align-items-center">
                    <img src="./images/Admin Nav/dashboard.png" alt="VMC Dashboard" class="img-fluid" style="max-width: 40px; margin-right: 10px;">
                    <h2 class="mb-0">Dashboard</h2>
                </div>

                <!-- Calendar Button -->
                <div class="d-flex justify-content-end mb-3"> 
                    <button id="calendarButton" type="button" class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#calendarModal">
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
                
                <!--dashboard content-->
                 <div class="row g-3">
                    <!-- Top Row: 4 Stats + Chart -->
                    <div class="col-md-7">
                    <div class="row g-3 h-100">
                        <!-- Total Orders -->
                        <div class="col-12 col-md-6">
                            <div class="dashboard-card rounded shadow-sm card-stat card-dark text-white p-3 rounded position-relative h-100 mb-3 mb-md-0 ">
                                <!-- Top Text -->
                                <div class="mb-5">
                                    <h6 class="fw-semibold dashboard-label">Total Orders for<br>this month</h6>
                                </div>

                                <!-- Floating Icon -->
                                <a href="orders.php" class="icon-float">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>

                                <!-- Bottom Number -->
                                <div class="stat-number">
                                    <?php echo $dashboardStats['total_orders']; ?> <span class="unit-label">orders</span>
                                </div>
                            </div>
                        </div>

                            <!-- New Orders -->
                            <div class="col-md-6">
                                <div class="dashboard-card card shadow-sm rounded overflow-hidden h-100 d-flex flex-column justify-content-between" style="height: 110px;">
                                    <!-- Top Section -->
                                    <div class="bg-white text-center py-3 flex-grow-1 d-flex align-items-center justify-content-center">
                                        <div class="dashboard-metric text-primary-dark"><?php echo $dashboardStats['new_orders']; ?></div>
                                    </div>
                                    <!-- Bottom Label Section -->
                                    <div class="bottom-label text-center py-2">
                                        <div class="fw-semibold">New Orders</div>
                                    </div>
                                </div>
                            </div>
                            <!-- Return Orders -->
                            <div class="col-md-6">
                                <div class=" dashboard-card card shadow-sm rounded overflow-hidden h-100 d-flex flex-column justify-content-between mt-lg-3" style="height: 110px;">
                                    <!-- Top Section -->
                                    <div class="bg-white text-center py-3 flex-grow-1 d-flex align-items-center justify-content-center">
                                        <div class="dashboard-metric text-warning"><?php echo $dashboardStats['return_orders']; ?></div>
                                    </div>
                                    <!-- Bottom Label Section -->
                                    <div class="bottom-label text-center py-2">
                                        <div class="fw-semibold">Return Order</div>
                                    </div>
                                </div>
                            </div>
                            <!-- Cancelled Orders -->
                             <div class="col-md-6">
                                <div class="dashboard-card card shadow-sm rounded overflow-hidden h-100 d-flex flex-column justify-content-between mt-lg-3" style="height: 110px;">
                                    <!-- Top Section -->
                                    <div class="bg-white text-center py-3 flex-grow-1 d-flex align-items-center justify-content-center">
                                        <div class="dashboard-metric text-danger"><?php echo $dashboardStats['cancelled_orders']; ?></div>
                                    </div>
                                    <!-- Bottom Label Section -->
                                    <div class="bottom-label text-center py-2">
                                        <div class="fw-semibold">Cancelled Order</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart beside the stats -->
                    <div class="col-md-5">
                        <div class="dashboard-card bg-white p-3 rounded shadow-sm border h-100">
                            <h5 class="mb-3 chart-title">Best Seller of the Month</h5>
                            <div style="position: relative; height: 250px; font-size: 5px;">
                                <canvas id="bestSellerChart" ></canvas>
                            </div>
                        </div>
                    </div>


                    <!-- Bottom Row -->
                    <div class="row g-2 mt-lg-4">
                    <!-- Low in Stocks -->
                    <div class="col-md-3">
                        <div class="dashboard-card stocks-card p-3 rounded shadow-sm text-start position-relative h-100 mb-3 mb-md-0">
                            <!-- Top Text -->
                            <div class="mb-5">
                                <h6 class="fw-semibold dashboard-label text-white">Low in Stocks</h6>
                            </div>

                            <!-- Bottom Number -->
                            <div class="stat-number2 text-white">
                                <?php echo $lowStock['stock']; ?> <span class="unit-label">pcs left of <strong><?php echo $lowStock['product_name']; ?></strong></span><br>
                            </div>
                        </div>
                    </div>

                    <!-- Active Students -->
                    <div class="col-md-3">
                        <div class="dashboard-card students-card p-3 rounded shadow-sm text-start position-relative h-100 mb-3 mb-md-0">
                            <!-- Top Text -->
                            <div class="mb-5">
                                <h6 class="fw-semibold dashboard-label text-white">Active Students</h6>
                            </div>

                            <!-- Bottom Number -->
                            <div class="stat-number2 text-white">
                                <?php echo $dashboardStats['active_students']; ?> <span class="unit-label"><strong>Students</strong></span><br>
                            </div>
                        </div>
                    </div>

                    <!-- Student Messages -->
                    <div class="col-md-3">
                        <div class="dashboard-card message-card p-3 shadow-sm rounded position-relative h-100 mb-3 mb-md-0">
                            <!-- Top Text -->
                            <div class="mb-5">
                                <h6 class="fw-semibold dashboard-label">Student Messages</h6>
                            </div>

                            <!-- Floating Icon -->
                            <a href="message.php" class="icon-float text-dark">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>

                            <!-- Bottom Number -->
                            <div class="stat-number2">
                                <?php echo $dashboardStats['new_messages']; ?> <span class="unit-label">new messages</span>
                            </div>
                        </div>
                    </div>

                    <!-- Rate and Reviews -->
                    <div class="col-md-3">
                        <div class="dashboard-card ratings-card p-3 shadow-sm rounded position-relative h-100 mb-3 mb-md-0">
                            <!-- Top Text -->
                            <div class="mb-5">
                                <h6 class="fw-semibold dashboard-label">Rating and Reviews</h6>
                            </div>

                            <!-- Floating Icon -->
                            <a href="ratings.php" class="icon-float text-dark">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>

                            <!-- Bottom Number -->
                            <div class="stat-number2">
                                <?php echo $dashboardStats['new_reviews']; ?> <span class="unit-label">new rate and reviews</span>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
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
            backgroundColor: '#00527F',
            barPercentage: 0.6,
            categoryPercentage: 0.7
        }]
    };

    new Chart(ctx, {
        type: 'bar',
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                title: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 5,
                        callback: function(value) {
                            return value + ' units';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        font: {
                            size: 11
                        }
                    }
                }
            },
            layout: {
                padding: {
                    left: 10,
                    right: 10,
                    top: 0,
                    bottom: 20
                }
            }
        }
    });
    </script>

</body>
</html>