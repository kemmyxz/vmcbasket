<?php
session_start();
require 'inc/config.php';

// -------------------------------
// Account Creation (Process POST)
// -------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {

  // Fetch form data safely
  $student_no = $_POST['student_no'] ?? '';
  $student_fname = $_POST['student_fname'] ?? '';
  $student_mname = $_POST['student_mname'] ?? '';
  $student_lname = $_POST['student_lname'] ?? '';
  $phone_number = $_POST['phone_number'] ?? '';
  $email = $_POST['email'] ?? '';
  $birthday = $_POST['birthday'] ?? '';
  $year_level = $_POST['year_level'] ?? '';

  // Validate correct date format (YYYY-MM-DD)
  if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)) {
    echo "<script>alert('Invalid date format! Please enter a valid birthday.'); window.history.back();</script>";
    exit();
  }
  // Ensure birthday's year is at least 2 years before the current year
  $birth_year = date('Y', strtotime($birthday));
  $current_year = date('Y');
  if ($birth_year > $current_year - 2) {
    echo "<script>alert('Birthdate must be at least 2 years before the current date!'); window.history.back();</script>";
    exit();
  }
  // Validate Student Number (must be exactly 6 digits)
  if (!preg_match('/^\d{6}$/', $student_no)) {
    echo "<script>alert('Error: Student Number must be exactly 6 digits!'); window.history.back();</script>";
    exit();
  }
  // Validate Phone Number (must be exactly 11 digits)
  if (!preg_match('/^\d{11}$/', $phone_number)) {
    echo "<script>alert('Error: Phone Number must be exactly 11 digits!'); window.history.back();</script>";
    exit();
  }
  // Also ensure birthday is at least 2 years before now (redundant check)
  $min_birthdate = date('Y-m-d', strtotime('-2 years'));
  if ($birthday > $min_birthdate) {
    echo "<script>alert('Error: Birthday must be at least 2 years before the current date!'); window.history.back();</script>";
    exit();
  }
  // Format birthday (YYYYMMDD) for default password generation
  $formatted_birthday = str_replace("-", "", $birthday);
  // Generate default password: lowercased middle name plus formatted birthday
  $student_pass = strtolower($student_mname) . $formatted_birthday;
  // Hash password securely
  $hashed_password = password_hash($student_pass, PASSWORD_BCRYPT);

  // Check if the Student Number already exists
  $check_sql = "SELECT student_no FROM users WHERE student_no = ?";
  if ($check_stmt = $conn->prepare($check_sql)) {
    $check_stmt->bind_param("s", $student_no);
    $check_stmt->execute();
    $check_stmt->store_result();
    if ($check_stmt->num_rows > 0) {
      echo "<script>alert('Error: Student ID already exists!'); window.history.back();</script>";
      exit();
    }
    $check_stmt->close();
  }

  // -------------------------------
  // Handle File Upload for Photo
  // -------------------------------
  // Set default photo filename (must be in the uploads folder)
  $photo = "profile_pic.png"; // Default image filename

  if (!empty($_FILES['photo']['name'])) {
    $target_dir = "uploads/";
    // Rename file with a timestamp to avoid collision
    $photo = time() . "_" . basename($_FILES["photo"]["name"]);
    $target_file = $target_dir . $photo;
    if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
      echo "<script>alert('Failed to upload photo'); window.history.back();</script>";
      exit();
    }
  }
  $status = $user['active_status'];
  // --------------------------------------------
  // Insert New User into Database (with activity)
  // --------------------------------------------
  $sql = "INSERT INTO users (student_no, student_pass, created_at, updated_at, student_fname, student_mname, student_lname, phone_number, email, birthday, year_level, photo, active_status, last_activity)
            VALUES (?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, ?, 'Active', NOW())";

  if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ssssssssss", $student_no, $hashed_password, $student_fname, $student_mname, $student_lname, $phone_number, $email, $birthday, $year_level, $photo);
    if ($stmt->execute()) {
      echo "<script>alert('User added successfully! Default Password: $student_pass'); window.location.href='cus.php';</script>";
      exit();
    } else {
      echo "<script>alert('Error: " . $stmt->error . "'); window.history.back();</script>";
      exit();
    }

  } else {
    echo "<script>alert('Error preparing statement: " . $conn->error . "'); window.history.back();</script>";
    exit();
  }
}

// ----------------------------------------------
// For GET requests: Continue with Page Display
// ----------------------------------------------

// Auto-delete accounts inactive for 3 months
//$threeMonthsAgo = date("Y-m-d H:i:s", strtotime("-3 months"));
//$conn->query("DELETE FROM users WHERE last_activity < '$threeMonthsAgo'");

// Pagination Setup

$limit =50; // Number of records per page
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Get current page number from URL parameter
$start = ($page - 1) * $limit; // Calculate the starting record for the SQL query

$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$where_clause = "";

if ($status_filter !== 'all') {
    $where_clause = "WHERE active_status = '$status_filter'";
}
// Query the users table
$sql = "SELECT id, student_fname, student_lname, student_no, phone_number, email, created_at, active_status, photo, last_activity 
        FROM users 
        $where_clause
        LIMIT $start, $limit";
$result = $conn->query($sql);
$total_results = $conn->query("SELECT COUNT(id) AS total FROM users $where_clause")->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit); // Calculate total pages

// Check if the "Previous" or "Next" buttons should be disabled
$previous_disabled = $page <= 1 ? 'disabled' : '';
$next_disabled = $page >= $total_pages ? 'disabled' : '';

// Generate page numbers dynamically
$page_links = '';
for ($i = 1; $i <= $total_pages; $i++) {
  $active_class = ($i == $page) ? 'active' : '';
  $page_links .= "<li class='page-item $active_class'><a class='page-link' href='?page=$i'>$i</a></li>";
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VMC Basket - Admin/Students</title>
  <?php include 'links.php'; ?>
  <style>
    .note {
      background-color: #C8D9E6;
      color: #26387D;
      padding: 3px 8px;
      border-radius: 100px;
      font-size: 16px;
      font-weight: 700;
      text-align: center;
    }

    #bulkActionsContainer {
      margin: 20px 0;
    }

    #bulkDisableBtn {
      background-color: #ffc107;
      border-color: #ffc107;
      color: #000;
    }

    #bulkDisableBtn:hover {
      background-color: #ffca2c;
      border-color: #ffc720;
    }

    .spinner-border-sm {
      width: 1rem;
      height: 1rem;
      margin-right: 0.5rem;
    }

    @media (max-width: 576px) {
      .note {
        font-size: 12px;
        padding: 8px 10px;
      }
    }

    .text-success {
      color: #198754;
    }

    .text-danger {
      color: #dc3545;
    }
  </style>
</head>

<body>
  <div class="container-fluid">
    <div class="row">
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
            <a href="cus.php" class="nav-link active">
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
      <main class="col-md-9 ms-sm-auto col-lg-10 content p-5">
        <div class="d-flex justify-content-end mb-5">
          <div class="search-container">
            <input type="text" class="form-control" placeholder="Search...">
            <button><i class="bi bi-search"></i></button>
          </div>
        </div>
        <div class="mt-2 mb-5">
          <h2>Students</h2>
        </div>
        <div class="row mb-3 g-2 align-items-center flex-column flex-md-row">
          <!-- DATE FILTER (Calendar) -->
          <div class="col-12 col-md">
            <form class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center" method="get"
              action="cus.php" style="gap: 8px;">
              <div class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center w-100">
                <label for="from_date" class="form-label mb-1 mb-sm-0 me-sm-1"
                  style="font-size: 15px;"><strong>From S.Y.</strong></label>
                <input type="year" class="form-control date-filter mb-2 mb-sm-0" id="from_date" name="from_date"
                  value="<?= htmlspecialchars($_GET['from_date'] ?? '') ?>">
                <label for="to_date" class="form-label mb-1 mb-sm-0 ms-sm-2 me-sm-1"
                  style="font-size: 15px;"><strong>To</strong></label>
                <input type="year" class="form-control date-filter mb-2 mb-sm-0" id="to_date" name="to_date"
                  value="<?= htmlspecialchars($_GET['to_date'] ?? '') ?>">
                <button type="submit" class="admin-btn ms-sm-2">Filter</button>
              </div>
            </form>
          </div>
          <!-- BUTTONS FOR ADDING CUSTOMER -->
          <div class="col-12 col-md-auto ms-md-0 mt-2 mt-md-3 d-flex justify-content-lg-end">
            <div class="btn-group w-100">
              <button type="button" class="admin-btn dropdown-toggle w-100" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="bi bi-plus-circle me-2"></i>Add Student
              </button>
              <ul class="dropdown-menu">
                <li>
                  <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                    Add Manually
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="#"
                    onclick="document.getElementById('csv-file').click(); return false;">
                    Add via CSV
                  </a>
                </li>
              </ul>
            </div>
            <input type="file" id="csv-file" style="display: none;" onchange="uploadCSV()">
          </div>
        </div>

        <div class="mt-4 mb-3 d-flex gap-4">
          <?php
          // Get total count
          $count_query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN active_status = 'Active' THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN active_status = 'Disabled' THEN 1 ELSE 0 END) as disabled_count
            FROM users";
          $count_result = $conn->query($count_query);
          $counts = $count_result->fetch_assoc();
          ?>
          <strong>Total Students: <?php echo $counts['total']; ?></strong>
          <span class="text-success">Active: <?php echo $counts['active_count']; ?></span>
          <span class="text-danger">Disabled: <?php echo $counts['disabled_count']; ?></span>
        </div>
        <!-- Bulk Disable Button (hidden by default) -->
        <div id="bulkDisableContainer" style="display:none; margin-top: 20px; margin-bottom: 20px;">
          <button id="bulkDisableBtn" class="btn btn-danger">
            <i class="bi bi-slash-circle"></i> Disable Account Selected
          </button>
        </div>
        <!-- Bulk Actions Container (hidden by default) -->
        <div id="bulkActionsContainer" style="display:none; margin-top: 20px; margin-bottom: 20px;">
          <button id="bulkDisableBtn" class="btn btn-warning me-2">
            <i class="bi bi-slash-circle"></i> Disable Account Selected
          </button>
    
        </div>

        <!-- ADD CUSTOMER MODAL -->
        <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
          aria-labelledby="staticBackdropLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="d-flex align-items-center">
                  <img src="./images/profile_pic.png" alt="add user icon" style="margin-right: 5px; height:50px;">Create
                  Student Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body mt-2 p-4">
                <div class="note">
                  <p class="mt-3">Note: Your student number must match with your school ID for verification.</p>
                </div>
                <!-- IMPORTANT: Added enctype attribute for file upload -->
                <form action="cus.php" method="POST" enctype="multipart/form-data">
                  <div class="row mt-4">
                    <div class="col-md-4 mb-2">
                      <label for="Fname" class="form-label">First Name</label>
                      <input type="text" class="form-control" id="name" name="student_fname" required
                        placeholder="Enter First Name">
                    </div>
                    <div class="col-md-4 mb-2">
                      <label for="name" class="form-label sm-mt-3">Middle Name</label>
                      <input type="text" class="form-control" id="name" name="student_mname" required
                        placeholder="Enter Middle Name">
                    </div>
                    <div class="col-md-4">
                      <label for="name" class="form-label sm-mt-3">Last Name</label>
                      <input type="text" class="form-control" id="name" name="student_lname" required
                        placeholder="Enter Last Name">
                    </div>
                  </div>
                  <div class="row mt-2">
                    <div class="col-md-6 mb-2">
                      <label for="email" class="form-label">E-mail</label>
                      <input type="email" class="form-control" id="email" name="email" placeholder="example@gmail.com"
                        required placeholder="Enter Email">
                    </div>
                    <div class="col-md-6">
                      <label for="phone" class="form-label sm-mt-3">Phone Number</label>
                      <input type="number" class="form-control" id="phone" name="phone_number"
                        placeholder="Ex. 0912 3456 789" required>
                    </div>
                  </div>
                  <div class="row mt-2">
                    <div class="col-md-6 mb-2">
                      <label for="studentNumber" class="form-label">Student Number</label>
                      <input type="text" class="form-control" id="student-number" name="student_no" required
                        placeholder="Ex. 210001">
                    </div>
                    <div class="col-md-6">
                      <label for="picture" class="form-label">Picture</label>
                      <input type="file" class="form-control" id="picture" name="photo">
                    </div>
                  </div>
                  <div class="row mb-2 mt-2">
                    <div class="col-md-12">
                      <label for="course" class="form-label">Year-Level/Course</label>
                      <select class="form-select" id="course" name="year_level" required>
                        <option selected disabled>Choose your year level/course</option>
                        <option value="Pre-School">Pre-School</option>
                        <option value="Kindergarten">Kindergarten</option>
                        <option value="Elementary Grade 1">Elementary Grade 1</option>
                        <option value="Elementary Grade 2">Elementary Grade 2</option>
                        <option value="Elementary Grade 3">Elementary Grade 3</option>
                        <option value="Elementary Grade 4">Elementary Grade 4</option>
                        <option value="Elementary Grade 5">Elementary Grade 5</option>
                        <option value="Elementary Grade 6">Elementary Grade 6</option>
                        <option value="Junior High School Grade 7">Junior High School Grade 7</option>
                        <option value="Junior High School Grade 8">Junior High School Grade 8</option>
                        <option value="Junior High School Grade 9">Junior High School Grade 9</option>
                        <option value="Junior High School Grade 10">Junior High School Grade 10</option>
                        <option value="Senior High School Grade 11">Senior High School Grade 11</option>
                        <option value="Senior High School Grade 12">Senior High School Grade 12</option>
                        <option value="Bachelor of Science in Information System">Bachelor of Science in Information
                          System</option>
                        <option value="Bachelor of Science in Business Administration">Bachelor of Science in Business
                          Administration</option>
                        <option
                          value="Bachelor of Science in Elementary Education Major Pre-School and Special Education">
                          Bachelor of Science in Elementary Education Major Pre-School and Special Education</option>
                        <option value="Bachelor of Science in Management and Tourism">Bachelor of Science in Management
                          and Tourism</option>
                        <option value="Bachelor of Science in Criminology">Bachelor of Science in Criminology</option>
                        <option value="Bachelor of Science in Hotel and Restaurant Management">Bachelor of Science in
                          Hotel and Restaurant Management</option>
                        <option value="Bachelor of Science in Secondary Education Major in English and Mathematics">
                          Bachelor of Science in Secondary Education Major in English and Mathematics</option>
                      </select>
                    </div>
                  </div>
                  <div class="row mb-2 mt-3">
                    <div class="col-md-12">
                      <label for="dob" class="form-label">Date of Birth</label>
                      <input type="date" class="form-control" id="dob" name="birthday" required
                        pattern="\d{4}-\d{2}-\d{2}">
                    </div>
                  </div>

                  <div class="modal-footer mt-4">
                    <button type="button" class="btn btn-outline-danger cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn custom-navy-btn add">Add Account</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div> <!-- End of Modal -->

        <!-- CUSTOMER TABLE -->
        <div class="table-responsive">
          <table class="table table-container text-center">
            <thead>
              <tr>
                <th>
                  <input type="checkbox" id="selectAllProducts" title="Select All" class="custom-checkbox">
                </th>
                <th>#</th>
                <th>Student Details</th>
                <th>Student ID</th>
                <th>Phone Number</th>
                <th class="align-middle text-center">
                    <div class="dropdown">
                      <button class="btn p-0 m-0 align-baseline table-dropdown dropdown-toggle" type="button"
                          id="stocksStatusDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                          style="text-decoration:none;">
                          Status
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="stocksStatusDropdown">
                          <li><a class="dropdown-item status-filter" href="#" data-status="all">All</a></li>
                          <li><a class="dropdown-item status-filter" href="#" data-status="Active">Active</a></li>
                          <li><a class="dropdown-item status-filter" href="#" data-status="Disabled">Disabled</a></li>
                      </ul>
                  </div>
                </th>
                <th>Date Created</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody class="align-middle">
              <?php $count = $start + 1;
              while ($row = $result->fetch_assoc()): ?>
                <?php
                // Check last_active timestamp to set active status.
                $activeStatus = "Disable";
                if (!empty($row['last_activity']) && strtotime($row['last_activity']) >= strtotime("-10 minutes")) {
                  $activeStatus = "Active";
                }
                $photoPath = ($row['photo'] === "profile_pic.png") ? "images\profile_pic.png" : "uploads/" . $row['photo'];
                ?>
                <tr>
                  <td>
                    <input type="checkbox" class="custom-checkbox product-checkbox" value="<?= $row['id']; ?>">
                  </td>
                  <td><?= $count++; ?></td>
                  <td class="text-start">
                    <div class="d-flex align-items-center">
                      <img src="<?php echo $photoPath; ?>" alt="User" class="user-img me-2"
                        style="width:50px; height:50px; object-fit:cover; border-radius:50%;">
                      <div>
                        <strong><?php echo $row['student_fname'] . " " . $row['student_lname']; ?></strong><br>
                        <small class="text-muted"><?php echo $row['email']; ?></small>
                      </div>
                    </div>
                  </td>
                  <td><?php echo $row['student_no']; ?></td>
                  <td><?php echo $row['phone_number']; ?></td>
                  <td>
                    <?php
                    $status = $row['active_status'];
                    $statusClass = strtolower($status);
                    ?>
                    <span class="status <?php echo $statusClass; ?>"><?php echo $status; ?></span></span>
                  </td>
                  <td class="text-center"><?php echo $row['created_at']; ?></td>

                  <td class="text-center">
                    <button class="btn btn-link view-student" data-bs-toggle="modal" data-bs-target="#studentInfoModal"
                      data-student-id="<?php echo $row['id']; ?>" style="font-size: 1.5rem; color: #333;">
                      <i class="bi bi-eye"></i>
                    </button>
                  </td>

                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
        <nav aria-label="Page navigation" class="d-flex justify-content-end mt-3">
          <ul class="pagination justify-content-center custom-pagination">
            <li class="page-item <?= $previous_disabled; ?>"><a class="page-link" href="?page=<?= $page - 1; ?>"><span
                  aria-hidden="true">&lt;</span></a></li>
            <?= $page_links; ?>
            <li class="page-item <?= $next_disabled; ?>"><a class="page-link" href="?page=<?= $page + 1; ?>"><span
                  aria-hidden="true">&gt;</span></a></li>
          </ul>
        </nav>
      </main>
    </div>
  </div>

  <!-- View Student Info Modal -->
  <div class="modal fade" id="studentInfoModal" tabindex="-1" aria-labelledby="studentInfoModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <!-- Header -->
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="studentInfoModalLabel">Student Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <!-- Body -->
        <div class="modal-body student-modal-body">
          <div class="row mb-4">
            <!-- Profile Picture -->
            <div class="col-md-3 text-center student-modal-photo">
              <img id="modal-student-photo" src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png"
                class="img-fluid rounded" style="max-width:150px;" alt="Student Profile">
            </div>

            <!-- Personal Information -->
            <div class="col-md-9 student-modal-info">
              <p><strong>Name:</strong> <span id="modal-student-name"></span></p>
              <p><strong>Student ID:</strong> <span id="modal-student-no"></span></p>
              <p><strong>Email:</strong> <span id="modal-student-email"></span></p>
              <p><strong>Phone Number:</strong> <span id="modal-student-phone"></span></p>
              <p><strong>Year-Level/Course:</strong> <span id="modal-student-year"></span></p>
              <p><strong>Date of Birthday:</strong> <span id="modal-student-birthday"></span></p>
            </div>
          </div>

          <!-- Search bar and Pagination aligned -->
          <div class="row mb-3 align-items-center">
            <div class="col-md-6">
              <div class="search-container">
                <input type="text" class="form-control" placeholder="Search...">
                <button><i class="bi bi-search"></i></button>
              </div>
            </div>
            <div class="col-md-6 d-flex justify-content-end">
              <nav aria-label="Page navigation">
                <ul class="pagination custom-pagination mb-0">
                  <li class="page-item <?= $previous_disabled; ?>"><a class="page-link"
                      href="?page=<?= $page - 1; ?>"><span aria-hidden="true">&lt;</span></a></li>
                  <?= $page_links; ?>
                  <li class="page-item <?= $next_disabled; ?>"><a class="page-link" href="?page=<?= $page + 1; ?>"><span
                        aria-hidden="true">&gt;</span></a></li>
                </ul>
              </nav>
            </div>
          </div>

          <!-- Orders Table -->
          <div class="table-responsive">
            <table class="table align-middle">
              <thead class="table-light">
                <tr>
                  <th>Photo</th>
                  <th>Transaction Id</th>
                  <th>Order Details</th>
                  <th>Ratings</th>
                </tr>
              </thead>
              <tbody>
                <!-- Notebook -->
                <tr>
                  <td><img src="https://via.placeholder.com/60" class="img-thumbnail" alt="Notebook"></td>
                  <td>#123456789</td>
                  <td>
                    <strong>Notebook</strong><br>
                    #12345678<br>
                    10/10/2024<br>
                    Qty: 5
                  </td>
                  <td>
                    <div class="text-warning">
                      ★★★★☆
                    </div>
                    <small>The notebooks are sturdy and last all year.</small>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    // Corrected event: Listen for change on the DOB input.
    document.getElementById('dob').addEventListener('change', function () {
      let inputDate = new Date(this.value);
      let currentYear = new Date().getFullYear();
      let minYear = currentYear - 2;
      if (inputDate.getFullYear() > minYear) {
        alert("Error: Birthdate must be at least 2 years before the current date!");
        this.value = "";
      }
    });



    function uploadCSV() {
      var fileInput = document.getElementById('csv-file');
      var formData = new FormData();
      formData.append('csv-file', fileInput.files[0]);

      var xhr = new XMLHttpRequest();
      xhr.open('POST', 'upload_csv.php', true);

      xhr.onload = function () {
        if (xhr.status === 200) {
          alert('CSV file uploaded and data processed successfully!');
          location.reload(); // Reload the page to see the new data
        } else {
          alert('Error uploading file.');
        }
      };

      xhr.send(formData);
    }

  </script>

  <script>

        document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('selectAllProducts');
        const checkboxes = document.querySelectorAll('.product-checkbox');
        const bulkDisableContainer = document.getElementById('bulkDisableContainer');
        const bulkDisableBtn = document.getElementById('bulkDisableBtn');
    
        // Select/Deselect all checkboxes
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            toggleBulkActions();
        });
    
        // Individual checkbox changes
        checkboxes.forEach(cb => {
            cb.addEventListener('change', function () {
                selectAll.checked = Array.from(checkboxes).every(cb => cb.checked);
                toggleBulkActions();
            });
        });
    
        function toggleBulkActions() {
            const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
            bulkDisableContainer.style.display = anyChecked ? 'block' : 'none';
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
      let currentStudentId = null;

      // Handle modal open
      document.querySelectorAll('.view-student').forEach(button => {
        button.addEventListener('click', function () {
          currentStudentId = this.getAttribute('data-student-id');
          loadStudentDetails(currentStudentId);
        });
      });

      // Handle search
      const searchInput = document.querySelector('.modal-body .search-container input');
      let searchTimeout;

      searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
          loadStudentDetails(currentStudentId, this.value);
        }, 500);
      });

      function loadStudentDetails(studentId, search = '') {
        fetch('get_student_details.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: `student_id=${studentId}&search=${search}`
        })
          .then(response => response.json())
          .then(data => {
            // Update student info
            document.getElementById('modal-student-photo').src = data.student.photo === "profile_pic.png" ?
              "images/profile_pic.png" : "uploads/" + data.student.photo;
            document.getElementById('modal-student-name').textContent =
              `${data.student.student_fname} ${data.student.student_mname} ${data.student.student_lname}`;
            document.getElementById('modal-student-no').textContent = data.student.student_no;
            document.getElementById('modal-student-email').textContent = data.student.email;
            document.getElementById('modal-student-phone').textContent = data.student.phone_number;
            document.getElementById('modal-student-year').textContent = data.student.year_level;
            document.getElementById('modal-student-birthday').textContent = data.student.birthday;

            // Update orders table
            const tbody = document.querySelector('#studentInfoModal .table tbody');
            tbody.innerHTML = '';

            data.orders.forEach(order => {
              const stars = '★'.repeat(order.rating || 0) + '☆'.repeat(5 - (order.rating || 0));
              tbody.innerHTML += `
                        <tr>
                            <td><img src="uploads/${order.product_image}" class="img-thumbnail" alt="${order.product_name}" style="width: 60px;"></td>
                            <td>${order.receipt_no}</td>
                            <td>
                                <strong>${order.product_name}</strong><br>
                                Order ID: #${order.id}<br>
                                ${new Date(order.order_date).toLocaleDateString()}<br>
                                Qty: ${order.quantity}
                            </td>
                            <td>
                                ${order.rating ? `
                                    <div class="text-warning">
                                        ${stars}
                                    </div>
                                    <small>${order.review_text || ''}</small>
                                ` : '<small>No rating yet</small>'}
                            </td>
                        </tr>
                    `;
            });

            if (data.orders.length === 0) {
              tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="text-center">No orders found</td>
                        </tr>
                    `;
            }
          })
          .catch(error => console.error('Error:', error));
      }
    });

document.addEventListener('DOMContentLoaded', function() {
    // Get all status filter links
    const statusFilters = document.querySelectorAll('.status-filter');
    
    // Add click event listener to each filter option
    statusFilters.forEach(filter => {
        filter.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get the selected status
            const status = this.getAttribute('data-status');
            
            // Update the dropdown button text
            const dropdownButton = document.getElementById('stocksStatusDropdown');
            dropdownButton.textContent = this.textContent;
            
            // Get current URL and update the status parameter
            let url = new URL(window.location.href);
            url.searchParams.set('page', '1'); // Reset to first page
            
            if (status === 'all') {
                url.searchParams.delete('status');
            } else {
                url.searchParams.set('status', status);
            }
            
            // Redirect to filtered URL
            window.location.href = url.toString();
        });
    });
    
    // Highlight active filter
    const currentStatus = new URLSearchParams(window.location.search).get('status') || 'all';
    statusFilters.forEach(filter => {
        if (filter.getAttribute('data-status') === currentStatus) {
            filter.classList.add('active');
            document.getElementById('stocksStatusDropdown').textContent = filter.textContent;
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const bulkDisableBtn = document.getElementById('bulkDisableBtn');
    
    bulkDisableBtn.addEventListener('click', function() {
        const selectedIds = Array.from(document.querySelectorAll('.product-checkbox:checked'))
            .map(checkbox => checkbox.value);
            
        if (selectedIds.length === 0) {
            alert('Please select at least one account to disable');
            return;
        }
        
        if (confirm('Are you sure you want to disable the selected accounts?')) {
            fetch('disable_accounts.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ ids: selectedIds })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Selected accounts have been disabled successfully');
                    location.reload();
                } else {
                    alert('Error disabling accounts: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while disabling accounts');
            });
        }
    });
});
</script>
</body>
</html>