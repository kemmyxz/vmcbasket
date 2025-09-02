<?php
require 'inc/config.php';
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket - Admin/Ratings</title>
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
                        <a href="inquiries.php" class="nav-link">
                            <i class="bi bi-chat-dots me-2"></i> Messages
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
                <div class="mt-2 d-flex flex-row align-items-center mb-5">
                    <img src="./images/Admin Nav/rating.png" alt="VMC Dashboard" class="img-fluid" style="max-width: 40px; margin-right: 10px;">
                    <h2>Ratings & Reviews</h2>
                </div>
                <div class="d-flex justify-content-end mb-3"> 
                    <!-- BUTTON FOR READ ALL AND DELETE -->
                    <button type="button" id="markAllReadBtn" class="btn btn-primary me-2">
                        <img src="./images/read.png" alt="Add" style="max-width: 20px;">
                        Mark all read
                    </button>
                    <button type="button" class="btn btn-danger" id="deleteAllBtn">
                        <i class="bi bi-trash text-light" style="margin-right: 5px;"></i>Delete All
                    </button>
                </div>
                
                <!-- TABLE -->
                <div class="table-container table-responsive-lg">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Name</th>
                                <th>Name</th>
                                <th>Rating</th>
                                <th>Review</th>
                                <th>Photos</th>
                                <th>Date Posted</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
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
                                    <td><?php echo $counter++; ?></td>
                                    <td class="text-start"><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                                    <td><?php echo $row['rating']; ?></td>
                                    <td style="max-width: 200px;">
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
                                    <td><?php echo date('m-d-Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <div class="d-flex flex-column gap-1 justify-content-center align-items-center">
                                            <button type="button" class="btn btn-primary mb-1 read-btn" style="width: 100px;">
                                                <img src="./images/read.png" alt="Read" style="max-width: 20px;">
                                                READ
                                            </button>
                                            <button type="button" class="btn btn-danger delete-review" 
                                                    data-review-id="<?php echo $row['id']; ?>" style="width: 100px;">
                                                <i class="bi bi-trash text-light"></i>
                                                DELETE
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <nav class="d-flex justify-content-end mt-3">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
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
            <!-- Custom Navigation Buttons -->
            <button type="button" id="prevBtn" class="custom-nav-btn start-0">&lsaquo;</button>
            <img id="modalImage" src="" class="modal-img rounded" alt="Full Size">
            <button type="button" id="nextBtn" class="custom-nav-btn end-0">&rsaquo;</button>
            <!-- Close Button -->
            <button type="button" class="btn-close position-absolute top-0 end-0 m-4" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        </div>
    </div>
    </div>


<script>
    // Hide individual read button when clicked
    document.querySelectorAll('.read-btn').forEach(button => {
        button.addEventListener('click', function () {
            this.style.display = 'none';
        });
    });

    // Hide all read buttons when "Mark All as Read" is clicked
    document.getElementById('markAllReadBtn').addEventListener('click', function () {
        document.querySelectorAll('.read-btn').forEach(button => {
            button.style.display = 'none';
        });
    });

     // Photo Viewer Modal
    document.addEventListener('DOMContentLoaded', function () {
    let currentImages = [];
    let currentIndex = 0;
    const modal = new bootstrap.Modal(document.getElementById('photoModal'));
    const modalImage = document.getElementById('modalImage');

    // On thumbnail click
    document.querySelectorAll('.photos-cell').forEach(cell => {
        const thumbnails = cell.querySelectorAll('.thumbnail-img');
        const images = Array.from(thumbnails).map(img => img.src);

        thumbnails.forEach((thumb, index) => {
            thumb.addEventListener('click', () => {
                currentImages = images;
                currentIndex = index;
                modalImage.src = currentImages[currentIndex];
                modal.show();
            });
        });
    });

    // Navigation
    document.getElementById('nextBtn').addEventListener('click', () => {
        if (currentImages.length === 0) return;
        currentIndex = (currentIndex + 1) % currentImages.length;
        modalImage.src = currentImages[currentIndex];
    });

    document.getElementById('prevBtn').addEventListener('click', () => {
        if (currentImages.length === 0) return;
        currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length;
        modalImage.src = currentImages[currentIndex];
    });
});

// Delete review functionality
document.querySelectorAll('.delete-review').forEach(button => {
    button.addEventListener('click', function() {
        const reviewId = this.getAttribute('data-review-id');
        const row = this.closest('tr');
        
        if(confirm('Are you sure you want to delete this review?')) {
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
                    alert('Review deleted successfully');
                } else {
                    alert(data.message || 'Error deleting review');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting review: ' + error.message);
            });
        }
    });
});

// Delete all reviews functionality
document.getElementById('deleteAllBtn').addEventListener('click', function() {
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
                alert('All reviews deleted successfully');
            } else {
                alert(data.message || 'Error deleting all reviews');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting all reviews: ' + error.message);
        });
    }
});
</script>
</body>
</html>

