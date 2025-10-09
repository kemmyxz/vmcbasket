<?php
require 'inc/config.php';
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
            <main class="col-md-9 ms-sm-auto col-lg-10 content p-5">
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
                    <form class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center" method="get" action="cus.php" style="gap: 8px;">
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
                            <h2 class="stat-number mb-1">90</h2>
                            <span class="growth">10.1% ⬈</span>
                        </div>
                        <p class="growth-label">Growth in Reviews</p>
                        </div>

                        <!-- Total Ratings -->
                        <div class="stat-box text-center">
                        <p class="stat-title">Total Ratings</p>
                        <div class="d-flex flex-row gap-2 justify-content-center align-items-center">
                            <h2 class="stat-number mb-1">90</h2>
                            <span class="growth">10.1% ⬈</span>
                        </div>
                        <p class="growth-label">Growth in Ratings</p>
                        </div>

                        <!-- Ratings Breakdown -->
                        <div class="ratings-breakdown">
                            <div class="rating-row"><span><i class="bi bi-star-fill"></i> 5</span><div class="bar bg-success" style="width:60%"></div><span>30</span></div>
                            <div class="rating-row"><span><i class="bi bi-star-fill"></i> 4</span><div class="bar bg-warning" style="width:50%"></div><span>25</span></div>
                            <div class="rating-row"><span><i class="bi bi-star-fill"></i> 3</span><div class="bar bg-primary" style="width:40%"></div><span>20</span></div>
                            <div class="rating-row"><span><i class="bi bi-star-fill"></i> 2</span><div class="bar bg-orange" style="width:20%"></div><span>10</span></div>
                            <div class="rating-row"><span><i class="bi bi-star-fill"></i> 1</span><div class="bar bg-danger" style="width:10%"></div><span>5</span></div>
                        </div>
                    </div>
                </div>
                <div class="mt-md-3 mb-3">
                    <strong>Total Ratings & Reviews: 100</strong>
                </div>

                 <!-- Bulk Delete Button (hidden by default) -->
                <div id="bulkDeleteContainer" style="display:none; margin-bottom: 16px;">
                    <button id="bulkDeleteBtn" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Delete Selected
                    </button>
                </div>

                <!-- TABLE -->
                <div class="table-responsive">
                    <table class="table table-container text-center">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAllProducts" title="Select All" class="custom-checkbox">
                                </th>
                                <th>#</th>
                                <th>Product Name</th>
                                <th>Student Name</th>
                                <th>Ratings & Reviews</th>
                                <th>Photos</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle">
                            <?php

                            
                            // Query to get reviews with product and user information
                            $query = "SELECT pr.*, p.product_name, CONCAT(u.student_fname, ' ', u.student_lname) as student_name, 
                                     GROUP_CONCAT(ri.image_path) as review_images
                                     FROM product_reviews pr
                                     JOIN products p ON pr.product_id = p.id
                                     JOIN users u ON pr.user_id = u.id
                                     LEFT JOIN review_images ri ON pr.id = ri.review_id
                                     GROUP BY pr.id
                                     ORDER BY pr.created_at DESC";
                            
                            $result = mysqli_query($conn, $query);
                            $counter = 1;

                            while($row = mysqli_fetch_assoc($result)) {
                                $images = $row['review_images'] ? explode(',', $row['review_images']) : [];
                                ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="custom-checkbox product-checkbox" value="<?= $row['id']; ?>">
                                    </td>
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
                                    <td>
                                        <div class="d-flex justify-content-center align-items-center">
                                            <button type="button" class="btn btn-danger delete-review" 
                                                    data-review-id="<?php echo $row['id']; ?>">
                                                <i class="bi bi-trash text-light"></i>
                                            </button>
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

// Delete review functionality
document.querySelectorAll('.delete-review').forEach(button => {
    button.addEventListener('click', function() {
        const reviewId = this.getAttribute('data-review-id');
        const row = this.closest('tr');

        // Create confirmation modal
        const modalDiv = document.createElement('div');
        modalDiv.className = 'modal fade';
        modalDiv.tabIndex = -1;
        modalDiv.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h4 class="modal-title">Confirm Delete</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this review?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modalDiv);
        const bsModal = new bootstrap.Modal(modalDiv);
        bsModal.show();

        modalDiv.querySelector('#confirmDeleteBtn').addEventListener('click', function() {
            fetch('delete_review.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `review_id=${reviewId}`
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    row.remove();
                    showBootstrapAlert('Review deleted successfully', 'success');
                    // Renumber table rows
                    const rows = document.querySelectorAll('table tbody tr');
                    rows.forEach((tr, idx) => {
                        const numCell = tr.querySelector('td:nth-child(2)');
                        if(numCell) numCell.textContent = idx + 1;
                    });
                } else {
                    showBootstrapAlert(data.message || 'Error deleting review', 'danger');
                }
                bsModal.hide();
                modalDiv.remove();
            })
            .catch(error => {
                console.error('Error:', error);
                showBootstrapAlert('Error deleting review: ' + error.message, 'danger');
                bsModal.hide();
                modalDiv.remove();
            });
        });

        // Remove modal from DOM when closed
        modalDiv.addEventListener('hidden.bs.modal', function () {
            modalDiv.remove();
        });
    });
});

// Delete all reviews functionality
const deleteAllBtn = document.getElementById('deleteAllBtn');
if (deleteAllBtn) {
    deleteAllBtn.addEventListener('click', function() {
        if(confirm('Are you sure you want to delete ALL reviews? This action cannot be undone.')) {
            fetch('delete_all_reviews.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    // Remove all rows from the table
                    const tbody = document.querySelector('table tbody');
                    tbody.innerHTML = '';
                    showBootstrapAlert('All reviews deleted successfully', 'success');
                } else {
                    showBootstrapAlert(data.message || 'Error deleting all reviews', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showBootstrapAlert('Error deleting all reviews: ' + error.message, 'danger');
            });
        }
    });
}
</script>

<script>
//Multiple delete functionality
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAllProducts');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const bulkDeleteContainer = document.getElementById('bulkDeleteContainer');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

    // Select/Deselect all checkboxes
    selectAll.addEventListener('change', function() {
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        toggleBulkDelete();
    });

    // If any checkbox is changed, update selectAll and bulk delete button
    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            selectAll.checked = Array.from(checkboxes).every(cb => cb.checked);
            toggleBulkDelete();
        });
    });

    function toggleBulkDelete() {
        const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
        bulkDeleteContainer.style.display = anyChecked ? 'block' : 'none';
    }

    // Helper to get icon HTML based on alert type
    function getAlertIcon(type) {
        switch(type) {
            case 'success': return '<i class="bi bi-check-circle-fill me-2"></i>';
            case 'danger':  return '<i class="bi bi-exclamation-triangle-fill me-2"></i>';
            case 'warning': return '<i class="bi bi-exclamation-circle-fill me-2"></i>';
            case 'info':    return '<i class="bi bi-info-circle-fill me-2"></i>';
            default:        return '';
        }
    }

    // Override showBootstrapAlert to include icon
    window.showBootstrapAlert = function(message, type = 'success', timeout = 3000) {
        document.querySelectorAll('.custom-bs-alert').forEach(el => el.remove());
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} custom-bs-alert position-fixed top-0 end-0 m-3 fade show`;
        alertDiv.role = 'alert';
        alertDiv.style.zIndex = 9999;
        alertDiv.innerHTML = `
            ${getAlertIcon(type)}${message}
            <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => {
            alertDiv.classList.remove('show');
            alertDiv.classList.add('hide');
            setTimeout(() => alertDiv.remove(), 500);
        }, timeout);
    };

    // Bulk delete action with modal confirmation
     bulkDeleteBtn.addEventListener('click', function() {
        const selectedIds = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        if (selectedIds.length === 0) return;
        if (confirm('Are you sure you want to delete the selected products?')) {
            // TODO: Send selectedIds to server for deletion (AJAX or form)
            alert('Selected IDs: ' + selectedIds.join(', '));
        }
     });
});

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

