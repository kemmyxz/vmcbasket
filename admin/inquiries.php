<?php
require 'inc/config.php';

// Fetch inquiries with user details using JOIN
$query = "SELECT i.id, u.student_fname, u.student_lname, u.student_no, u.email, 
          i.message, i.created_at 
          FROM inquiries i 
          JOIN users u ON i.user_id = u.id 
          ORDER BY i.created_at DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket - Admin/Messages</title>
    <link rel="icon" href="images/vmc_basket_logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kulim+Park:ital,wght@0,200;0,300;0,400;0,600;0,700;1,200;1,300;1,400;1,600;1,700&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/style.css">
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
                        <a href="inquiries.php" class="nav-link active">
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
                <div class="mt-2 d-flex flex-row align-items-center mb-5">
                    <img src="./images/Admin Nav/message.png" alt="VMC Dashboard" class="img-fluid" style="max-width: 40px; margin-right: 10px;">
                    <h2>Messages</h2>
                </div>
                <div class="d-flex justify-content-end mb-3"> 
                    <!-- BUTTON FOR READ ALL AND DELETE -->
                    <button type="button" id="markAllReadBtn" class="btn btn-primary me-2">
                        <img src="./images/read.png" alt="Add" style="max-width: 20px;">
                        Mark all read
                    </button>
                    <button type="button" class="btn btn-danger">
                        <i class="bi bi-trash" class="text-light" style="margin-right: 5px;"></i>Delete All
                    </button>
                </div>
                
                <!-- TABLE -->
                <div class="table-container table-responsive-lg">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Student Number</th>
                                <th>Email</th>
                                <th>Message</th>
                                <th>Date Sent</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($result->num_rows > 0) {
                                $counter = 1;
                                while ($row = $result->fetch_assoc()) {
                            ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><?php echo htmlspecialchars($row['student_fname'] . ' ' . $row['student_lname']); ?></td>
                                    <td><?php echo htmlspecialchars($row['student_no']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td style="max-width: 200px;">
                                        <div style="word-break: break-word; white-space: normal;">
                                            <?php echo htmlspecialchars($row['message']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo date('m-d-Y', strtotime($row['created_at'])); ?></td>
                                    <td>
                                        <div class="d-flex flex-column gap-1 justify-content-center align-items-center">
                                            <button type="button" class="btn btn-primary mb-1 read-btn" style="width: 50px;">
                                                <img src="./images/read.png" alt="Read" style="max-width: 20px;">
                                            </button>
                                            <button type="button" class="btn btn-danger delete-btn" 
                                                    data-id="<?php echo $row['id']; ?>" style="width: 50px;">
                                                <i class="bi bi-trash text-light"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                }
                            } else {
                            ?>
                                <tr>
                                    <td colspan="7" class="text-center">No inquiries found</td>
                                </tr>
                            <?php
                            }
                            ?>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

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

    // Delete inquiry functionality
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this inquiry?')) {
                const inquiryId = this.getAttribute('data-id');
                
                fetch('delete_inquiry.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + inquiryId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the row from the table
                        this.closest('tr').remove();
                    } else {
                        alert('Error deleting inquiry');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting inquiry');
                });
            }
        });
    });
</script>
</body>
</html>

