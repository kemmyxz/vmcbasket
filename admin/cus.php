<?php
session_start();
require 'inc/config.php';

// -------------------------------
// Account Creation (Process POST)
// -------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Fetch form data safely
    $student_no    = $_POST['student_no'] ?? '';
    $student_fname = $_POST['student_fname'] ?? '';
    $student_mname = $_POST['student_mname'] ?? '';
    $student_lname = $_POST['student_lname'] ?? '';
    $phone_number  = $_POST['phone_number'] ?? '';
    $email         = $_POST['email'] ?? '';
    $birthday      = $_POST['birthday'] ?? '';
    $year_level    = $_POST['year_level'] ?? '';

    // Validate correct date format (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)) {
        echo "<script>alert('Invalid date format! Please enter a valid birthday.'); window.history.back();</script>";
        exit();
    }
    // Ensure birthday's year is at least 2 years before the current year
    $birth_year   = date('Y', strtotime($birthday));
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
    $status=$user['active_status'];
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
$threeMonthsAgo = date("Y-m-d H:i:s", strtotime("-3 months"));
$conn->query("DELETE FROM users WHERE last_activity < '$threeMonthsAgo'");

// Pagination Setup

$limit = 10; // Number of records per page
$page = isset($_GET['page']) ? $_GET['page'] : 1; // Get current page number from URL parameter
$start = ($page - 1) * $limit; // Calculate the starting record for the SQL query

// Query the users table
$sql = "SELECT id, student_fname, student_lname, student_no, phone_number, email, created_at, active_status, photo, last_activity 
        FROM users 
        LIMIT $start, $limit";
$result = $conn->query($sql);
$total_results = $conn->query("SELECT COUNT(id) AS total FROM users")->fetch_assoc()['total'];
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
  <title>VMC Basket - Admin/Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" 
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    .note {
      background-color: #C8D9E6; 
      color: #003153; 
      padding: 10px 10px;
      border-radius: 100px; 
      font-size: 16px;
      font-weight: 700;
      text-align: center;
    }
    .modal-header {
      border-bottom: 1px solid #00527F; 
    }
    .modal-footer {
      border-top: 1px solid #00527F; 
    }
    .modal-header h5 {
      font-family: 'Ubuntu', sans-serif;
      font-size: 24px;
      color: #00527F;
      font-weight: bold;
    }
    .modal-body {
      font-family: 'poppins', sans-serif;
      font-size: 16px;
      color: #003153;
    }
    .form-select, .form-control {
      border-color: #003153;
    }
    /* Remove spinner from number inputs */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }
    input[type=number] {
      -moz-appearance: textfield;
    }
    @media (max-width: 576px) {
      .note {
        font-size: 12px;
        padding: 8px 10px;
      }
      .modal-header h5 {
        font-size: 18px;
      }
    }

   
    .status {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
    }

    .status.active {
        background-color: #e6ffe6;
        color: #008000;
    }

    .status.inactive {
        background-color: #ffe6e6;
        color: #ff0000;
    }
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
          <img src="./images/Admin Nav/VMS-LOGO-ALternative-03.png" alt="VMC Logo" class="img-fluid" style="max-width: 150px;">
          <h5 class="LogoName">VMC Basket</h5>
        </div>
      </nav>
      
      <!-- Sidebar -->
      <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse"  style="position: fixed;">
        <div class="text-center my-3 mb-5 d-none d-md-block">
          <img src="./images/Admin Nav/VMS-LOGO-ALternative-03.png" alt="VMC Logo" class="img-fluid" style="max-width: 150px;">
          <h4 class="LogoName">VMC Basket</h4>
        </div>
        <a href="index.php"><img src="./images/Admin Nav/dashboard-nav.png" alt="Dashboard" class="dashboard-icon" style="max-width: 30px; margin-right: 10px;">Dashboard</a>
        <a href="orders.php" class="mt-2"><img src="./images/Admin Nav/orders-nav.png" alt="Orders" class="orders-icon" style="max-width: 30px; margin-right: 10px;">Orders</a>
        <a href="prod.php" class="mt-2"><img src="./images/Admin Nav/products-nav.png" alt="Products" class="products-icon" style="max-width: 30px; margin-right: 10px;">Products</a>
        <a href="cus.php" class="mt-2 active"><img src="./images/Admin Nav/customers-nav-clicked.png" alt="Customer" class="customer-icon" style="max-width: 30px; margin-right: 10px;">Students</a>
        <a href="inquiries.php" class="mt-2"><img src="./images/Admin Nav/message-nav.png" alt="Message" class="message-icon" style="max-width: 30px; margin-right: 10px;">Messages</a>
        <a href="ratings.php" class="mt-2"><img src="./images/Admin Nav/rating-nav.png" alt="Ratings & Reviews" class="reviews-icon" style="max-width: 30px; margin-right: 10px;">Ratings & Reviews</a>
        <a href="accounting.php" class="mt-2 mb-2"><img src="./images/Admin Nav/receipt-nav 1.png" alt="Accounting" Class="accounting-icon" style="max-width: 30px; margin-right: 10px;">Receipt Form</a>
        <a href="#" class="mt-2 mb-2"><img src="./images/Admin Nav/setting-nav.png" alt="Settings" class="settings-icon" style="max-width: 30px; margin-right: 10px;">Settings</a>
        <a href="logout.php" class="mt-xl-5"><img src="./images/Admin Nav/admin-logout-nav.png" alt="Logout" class="logout-icon" style="max-width: 30px; margin-right: 10px;">Logout</a>
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
          <img src="./images/Admin Nav/customers-nav.png" alt="VMC Dashboard" class="img-fluid" style="max-width: 40px; margin-right: 10px;">
          <h2>STUDENTS</h2>
        </div>
        <div class="d-flex justify-content-end mb-3"> 
          <!-- BUTTONS FOR ADDING CUSTOMER -->
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop" style="margin-right: 5px;">
            <img src="./images/add.png" alt="Add" style="max-width: 20px; margin-right: 5px;">
            Add Student
          </button>
          <button type="button" class="btn btn-primary" onclick="document.getElementById('csv-file').click();">
            <img src="./images/add.png" alt="Add" style="max-width: 20px; margin-right: 5px;">
            Add CSV
          </button>
          <input type="file" id="csv-file" style="display: none;" onchange="uploadCSV()">

          <!-- ADD CUSTOMER MODAL -->
          <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" 
               aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title d-flex align-items-center">
                    <img src="./images/profile_pic.png" alt="add user icon" style="margin-right: 5px; height:50px;">Create Student Account
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body mt-2 mb-3">
                  <div class="note">
                    <p>Note: Your student number must match with your school ID for verification.</p>
                  </div>
                  <!-- IMPORTANT: Added enctype attribute for file upload -->
                  <form action="cus.php" method="POST" enctype="multipart/form-data">
                    <div class="row mt-4">
                      <div class="col-md-4 mb-2">
                        <label for="Fname" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="name" name="student_fname" required>
                      </div>
                      <div class="col-md-4 mb-2">
                        <label for="name" class="form-label sm-mt-3">Middle Name</label>
                        <input type="text" class="form-control" id="name" name="student_mname" required>
                      </div>
                      <div class="col-md-4">
                        <label for="name" class="form-label sm-mt-3">Last Name</label>
                        <input type="text" class="form-control" id="name" name="student_lname" required>
                      </div>
                    </div>
                    <div class="row mt-2">
                      <div class="col-md-6 mb-2">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="example@gmail.com" required>
                      </div>
                      <div class="col-md-6">
                        <label for="phone" class="form-label sm-mt-3">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone_number" placeholder="(+63)09xx xxxx xxx" required>
                      </div>
                    </div>
                    <div class="row mt-2">
                      <div class="col-md-6 mb-2">
                        <label for="studentNumber" class="form-label">Student Number</label>
                        <input type="text" class="form-control" id="student-number" name="student_no" required>
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
                          <option value="Bachelor of Science in Information System">Bachelor of Science in Information System</option>
                          <option value="Bachelor of Science in Business Administration">Bachelor of Science in Business Administration</option>
                          <option value="Bachelor of Science in Elementary Education Major Pre-School and Special Education">Bachelor of Science in Elementary Education Major Pre-School and Special Education</option>
                          <option value="Bachelor of Science in Management and Tourism">Bachelor of Science in Management and Tourism</option>
                          <option value="Bachelor of Science in Criminology">Bachelor of Science in Criminology</option>
                          <option value="Bachelor of Science in Hotel and Restaurant Management">Bachelor of Science in Hotel and Restaurant Management</option>
                          <option value="Bachelor of Science in Secondary Education Major in English and Mathematics">Bachelor of Science in Secondary Education Major in English and Mathematics</option>
                        </select>
                      </div>
                    </div>
                    <div class="row mb-2 mt-3">
                      <div class="col-md-12">
                        <label for="dob" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="dob" name="birthday" required pattern="\d{4}-\d{2}-\d{2}">
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-outline-danger cancel" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary add">Add Account</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div> <!-- End of Modal -->
        </div>
        
        <!-- CUSTOMER TABLE -->
        <div class="table-container table-responsive-lg">
          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Student Number</th>        
                <th>Phone Number</th>
                <th>Email</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php $count = $start + 1; while ($row = $result->fetch_assoc()): ?>
                <?php 
                  // Check last_active timestamp to set active status.
                  $activeStatus = "Inactive";
                  if (!empty($row['last_activity']) && strtotime($row['last_activity']) >= strtotime("-10 minutes")) {
                    $activeStatus = "Active";
                  }
                  $photoPath = ($row['photo'] === "profile_pic.png") ? "images\profile_pic.png" : "uploads/" . $row['photo'];
                ?>
                <tr>
                  <td ><?= $count++; ?></td>
                  <td class="text-start">
                  <img src="<?php echo $photoPath; ?>" alt="User" class="user-img" style="width:auto; height:50px">
                    <span><?php echo $row['student_fname'] . " " . $row['student_lname']; ?></span>
                  </td>
                  <td><?php echo $row['student_no']; ?></td>
                  
                  <td><?php echo $row['phone_number']; ?></td>
                  <td><?php echo $row['email']; ?></td>
                  
                  <td>
                    <?php 
                      $status = $row['active_status']; 
                      $statusClass = strtolower($status);
                  ?>
                   <span class="status <?php echo $statusClass; ?>"><?php echo $status; ?></span></span></td>
                  <td><?php echo $row['created_at']; ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
        <nav class="d-flex justify-content-end mt-3">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $previous_disabled; ?>"><a class="page-link" href="?page=<?= $page - 1; ?>">Previous</a></li>
        <?= $page_links; ?>
        <li class="page-item <?= $next_disabled; ?>"><a class="page-link" href="?page=<?= $page + 1; ?>">Next</a></li>
    </ul>
</nav>
      </main>
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
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" 
          integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>
