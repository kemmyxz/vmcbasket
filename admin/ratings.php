<?php
require 'inc/config.php';

// Total Reviews (reviews with non-empty review_text)
$totalReviewsQuery = "SELECT COUNT(*) AS total_reviews FROM product_reviews WHERE review_text IS NOT NULL AND TRIM(review_text) != ''";
$totalReviewsResult = mysqli_query($conn, $totalReviewsQuery);
$totalReviews = ($totalReviewsResult && $row = mysqli_fetch_assoc($totalReviewsResult)) ? (int)$row['total_reviews'] : 0;

// Total Ratings (all rows in product_reviews)
$totalRatingsQuery = "SELECT COUNT(*) AS total_ratings FROM product_reviews";
$totalRatingsResult = mysqli_query($conn, $totalRatingsQuery);
$totalRatings = ($totalRatingsResult && $row = mysqli_fetch_assoc($totalRatingsResult)) ? (int)$row['total_ratings'] : 0;

// Ratings breakdown (count per rating 1-5)
$ratingsBreakdown = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
$breakdownQuery = "SELECT rating, COUNT(*) as count FROM product_reviews GROUP BY rating";
$breakdownResult = mysqli_query($conn, $breakdownQuery);
if ($breakdownResult) {
    while ($row = mysqli_fetch_assoc($breakdownResult)) {
        $r = (int)$row['rating'];
        if ($r >= 1 && $r <= 5) $ratingsBreakdown[$r] = (int)$row['count'];
    }
}

// For growth, compute percentage increase compared to previous period (e.g., previous month)
function getPreviousPeriodCounts($conn, $column = 'created_at', $table = 'product_reviews', $reviewOnly = false) {
    // Get first day of this month and previous month
    $firstDayThisMonth = date('Y-m-01');
    $firstDayPrevMonth = date('Y-m-01', strtotime('-1 month'));
    $lastDayPrevMonth = date('Y-m-t', strtotime('-1 month'));

    // Build WHERE clause
    $where = "$column >= '$firstDayPrevMonth' AND $column < '$firstDayThisMonth'";
    if ($reviewOnly) {
        $where .= " AND review_text IS NOT NULL AND TRIM(review_text) != ''";
    }

    $sql = "SELECT COUNT(*) AS cnt FROM $table WHERE $where";
    $res = mysqli_query($conn, $sql);
    $row = $res ? mysqli_fetch_assoc($res) : ['cnt' => 0];
    return (int)$row['cnt'];
}

// Current period: this month
$firstDayThisMonth = date('Y-m-01');
$today = date('Y-m-d');

// Reviews this month
$reviewsThisMonthQuery = "SELECT COUNT(*) AS cnt FROM product_reviews WHERE created_at >= '$firstDayThisMonth' AND created_at <= '$today' AND review_text IS NOT NULL AND TRIM(review_text) != ''";
$reviewsThisMonthResult = mysqli_query($conn, $reviewsThisMonthQuery);
$reviewsThisMonth = ($reviewsThisMonthResult && $row = mysqli_fetch_assoc($reviewsThisMonthResult)) ? (int)$row['cnt'] : 0;

// Ratings this month
$ratingsThisMonthQuery = "SELECT COUNT(*) AS cnt FROM product_reviews WHERE created_at >= '$firstDayThisMonth' AND created_at <= '$today'";
$ratingsThisMonthResult = mysqli_query($conn, $ratingsThisMonthQuery);
$ratingsThisMonth = ($ratingsThisMonthResult && $row = mysqli_fetch_assoc($ratingsThisMonthResult)) ? (int)$row['cnt'] : 0;

// Previous month
$reviewsPrevMonth = getPreviousPeriodCounts($conn, 'created_at', 'product_reviews', true);
$ratingsPrevMonth = getPreviousPeriodCounts($conn, 'created_at', 'product_reviews', false);

// Compute growth percentage
function computeGrowth($current, $previous) {
    if ($previous == 0) {
        return $current > 0 ? "100% ⬈" : "0%";
    }
    $growth = (($current - $previous) / $previous) * 100;
    $arrow = $growth >= 0 ? "⬈" : "⬊";
    return number_format(abs($growth), 1) . "% $arrow";
}

$reviewsGrowth = computeGrowth($reviewsThisMonth, $reviewsPrevMonth);
$ratingsGrowth = computeGrowth($ratingsThisMonth, $ratingsPrevMonth);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket - Admin/Ratings</title>
    <?php include 'links.php'; ?>
    <style>
        .thumbnail-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid #ccc;
        }   

        .modal-img {
            width: 100%;
            max-height: 500px;
            object-fit: contain;
        }

        /* Minimalist modal image */
        .modal-img {
            width: 100%;
            max-height: 500px;
            object-fit: contain;
            border-radius: 12px;
            background-color: white;
        }

        /* Custom navigation buttons */
        .custom-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: white;
            color: black;
            font-size: 2rem;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            z-index: 10;
            cursor: pointer;
        }
     </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
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
                    <a href="index.php" class="nav-link">
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
                    <a href="ratings.php" class="nav-link active">
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
                        <input type="text" class="form-control" placeholder="Search...">
                        <button><i class="bi bi-search"></i></button>
                    </div>
                </div>
                <div class="row mb-3 g-2 align-items-center flex-column flex-md-row">
                <div class="mt-2 mb-3">
                    <h2>Ratings & Reviews</h2>
                </div>
                <div class="col-12 col-md mb-3">
                    <form class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center" method="get" action="ratings.php" style="gap: 8px;">
                        <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center w-100">
                            <label for="from_month" class="form-label mb-1 mb-sm-0 me-sm-1" style="font-size: 15px;"><strong>From</strong></label>
                            <input type="month" class="form-control date-filter mb-2 mb-sm-0" id="from_month" name="from_month" value="<?= htmlspecialchars($_GET['from_month'] ?? '') ?>">
                            <label for="to_month" class="form-label mb-1 mb-sm-0 ms-sm-2 me-sm-1" style="font-size: 15px;"><strong>To</strong></label>
                            <input type="month" class="form-control date-filter mb-2 mb-sm-0" id="to_month" name="to_month" value="<?= htmlspecialchars($_GET['to_month'] ?? '') ?>">
                            <button type="submit" class="admin-btn ms-sm-2">Filter</button>
                        </div>
                    </form>
                </div>
                <div class="col-12 col-md-auto d-flex justify-content-end" style="gap: 10px;"> 
                    <!-- Reviews & Ratings Stats -->
                    <div class="stats-container d-flex flex-wrap gap-4">
                
                        <!-- Total Reviews -->
                        <div class="stat-box text-center">
                        <p class="stat-title">Total Reviews</p>
                        <div class="d-flex flex-row gap-2 justify-content-center align-items-center">
                            <h2 class="stat-number mb-1"><?= $totalReviews ?></h2>
                            <span class="growth"><?= $reviewsGrowth ?></span>
                        </div>
                        <p class="growth-label">Growth in Reviews</p>
                        </div>

                        <!-- Total Ratings -->
                        <div class="stat-box text-center">
                        <p class="stat-title">Total Ratings</p>
                        <div class="d-flex flex-row gap-2 justify-content-center align-items-center">
                            <h2 class="stat-number mb-1"><?= $totalRatings ?></h2>
                            <span class="growth"><?= $ratingsGrowth ?></span>
                        </div>
                        <p class="growth-label">Growth in Ratings</p>
                        </div>

                        <!-- Ratings Breakdown -->
                        <div class="ratings-breakdown">
                            <div class="rating-row"><span><i class="bi bi-star-fill"></i> 5</span><div class="bar bg-success" style="width:<?= $totalRatings ? round($ratingsBreakdown[5]/$totalRatings*100) : 0 ?>%"></div><span><?= $ratingsBreakdown[5] ?></span></div>
                            <div class="rating-row"><span><i class="bi bi-star-fill"></i> 4</span><div class="bar bg-warning" style="width:<?= $totalRatings ? round($ratingsBreakdown[4]/$totalRatings*100) : 0 ?>%"></div><span><?= $ratingsBreakdown[4] ?></span></div>
                            <div class="rating-row"><span><i class="bi bi-star-fill"></i> 3</span><div class="bar bg-primary" style="width:<?= $totalRatings ? round($ratingsBreakdown[3]/$totalRatings*100) : 0 ?>%"></div><span><?= $ratingsBreakdown[3] ?></span></div>
                            <div class="rating-row"><span><i class="bi bi-star-fill"></i> 2</span><div class="bar bg-orange" style="width:<?= $totalRatings ? round($ratingsBreakdown[2]/$totalRatings*100) : 0 ?>%"></div><span><?= $ratingsBreakdown[2] ?></span></div>
                            <div class="rating-row"><span><i class="bi bi-star-fill"></i> 1</span><div class="bar bg-danger" style="width:<?= $totalRatings ? round($ratingsBreakdown[1]/$totalRatings*100) : 0 ?>%"></div><span><?= $ratingsBreakdown[1] ?></span></div>
                        </div>
                    </div>
                </div>
                <div class="mt-md-3 mb-3">
                    <strong>Total Ratings & Reviews: 100</strong>
                </div>


                <!-- TABLE -->
                <div class="table-responsive">
                    <table class="table table-container text-center">
                        <thead>
                            <tr>

                                <th>#</th>
                                <th>Product Name</th>
                                <th>Student Name</th>
                                <th>Ratings & Reviews</th>
                                <th>Photos</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            <?php

                            
                            // Get filter values
                            $from_month = isset($_GET['from_month']) ? $_GET['from_month'] : '';
                            $to_month = isset($_GET['to_month']) ? $_GET['to_month'] : '';

                            // Build WHERE clause for filtering
                            $where = "1";
                            if ($from_month) {
                                $from_date = $from_month . "-01";
                                $where .= " AND pr.created_at >= '$from_date'";
                            }
                            if ($to_month) {
                                // Get last day of the selected month
                                $to_date = date('Y-m-t', strtotime($to_month . "-01"));
                                $where .= " AND pr.created_at <= '$to_date'";
                            }

                            // Query to get reviews with product and user information (with filter)
                            $query = "SELECT pr.*, p.product_name, CONCAT(u.student_fname, ' ', u.student_lname) as student_name, 
                                     GROUP_CONCAT(ri.image_path) as review_images
                                     FROM product_reviews pr
                                     JOIN products p ON pr.product_id = p.id
                                     JOIN users u ON pr.user_id = u.id
                                     LEFT JOIN review_images ri ON pr.id = ri.review_id
                                     WHERE $where
                                     GROUP BY pr.id
                                     ORDER BY pr.created_at DESC";
                            
                            $result = mysqli_query($conn, $query);
                            $counter = 1;

                            while($row = mysqli_fetch_assoc($result)) {
                                $images = $row['review_images'] ? explode(',', $row['review_images']) : [];
                                ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td class="text-start"><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                    <td style="max-width: 200px;">
                                        <?php
                                            $rating = (int)$row['rating'];
                                            $stars = str_repeat('⭐', $rating);
                                            echo $stars . " ($rating)";
                                        ?>
                                         <small class="text-muted"><?php echo date('m-d-Y', strtotime($row['created_at'])); ?></small>
                                        <div style="word-break: break-word; white-space: normal;">
                                            <?php echo htmlspecialchars($row['review_text']); ?>
                                        </div>
                                    </td>
                                    <td class="photos-cell">
                                        <div class="photo-thumbnails d-flex flex-wrap gap-2 justify-content-center align-items-center">
                                            <?php 
                                            foreach($images as $index => $image) {
                                                echo "<img src='../" . htmlspecialchars($image) . "' 
                                                class='thumbnail-img' alt='Review Photo' data-index='$index'>";
                                            }
                                            ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <nav aria-label="Page navigation" class="d-flex justify-content-end mt-3">
                    <ul class="pagination justify-content-center custom-pagination">
                        <li class="page-item disabled"><a class="page-link" href="#"><span aria-hidden="true">&lt;</span></a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#"><span aria-hidden="true">&gt;</span></a></li>
                    </ul>
                </nav>
            </main>
        </div>
    </div>

    <!-- Photo Viewer Modal -->
    <div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 bg-transparent">
                <div class="modal-body text-center position-relative p-0">
                    <!-- Close Button (top-right corner) -->
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                    <!-- Custom Navigation Buttons -->
                    <button type="button" id="prevBtn" class="custom-nav-btn start-0" style="display:none;">&lsaquo;</button>
                    <img id="modalImage" src="" class="modal-img rounded" alt="Full Size">
                    <button type="button" id="nextBtn" class="custom-nav-btn end-0" style="display:none;">&rsaquo;</button>
                </div>
            </div>
        </div>
    </div>


<script>
// Helper to show Bootstrap 5.3.3 alerts
function showBootstrapAlert(message, type = 'success', timeout = 3000) {
    // Remove existing alerts
    document.querySelectorAll('.custom-bs-alert').forEach(el => el.remove());
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} custom-bs-alert position-fixed top-0 end-0 m-3 fade show`;
    alertDiv.role = 'alert';
    alertDiv.style.zIndex = 9999;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(alertDiv);
    // Auto-dismiss after timeout
    setTimeout(() => {
        alertDiv.classList.remove('show');
        alertDiv.classList.add('hide');
        setTimeout(() => alertDiv.remove(), 500);
    }, timeout);
}




    // Photo Viewer Modal (single initialization)
    let currentImages = [];
    let currentIndex = 0;
    const modalElement = document.getElementById('photoModal');
    const modal = new bootstrap.Modal(modalElement);
    const modalImage = document.getElementById('modalImage');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    // On thumbnail click
    document.querySelectorAll('.photos-cell').forEach(cell => {
        const thumbnails = cell.querySelectorAll('.thumbnail-img');
        const images = Array.from(thumbnails).map(img => img.src);

        thumbnails.forEach((thumb, index) => {
            thumb.addEventListener('click', () => {
                currentImages = images;
                currentIndex = index;
                modalImage.src = currentImages[currentIndex];
                // Show/hide navigation buttons
                if (currentImages.length > 1) {
                    prevBtn.style.display = '';
                    nextBtn.style.display = '';
                } else {
                    prevBtn.style.display = 'none';
                    nextBtn.style.display = 'none';
                }
                modal.show();
            });
        });
    });

    // Navigation
    nextBtn.addEventListener('click', () => {
        if (currentImages.length <= 1) return;
        currentIndex = (currentIndex + 1) % currentImages.length;
        modalImage.src = currentImages[currentIndex];
    });

    prevBtn.addEventListener('click', () => {
        if (currentImages.length <= 1) return;
        currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length;
        modalImage.src = currentImages[currentIndex];
    });

    // Ensure modal backdrop is removed on close
    modalElement.addEventListener('hidden.bs.modal', function () {
        document.body.classList.remove('modal-open');
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    });
</script>
</body>
</html>

