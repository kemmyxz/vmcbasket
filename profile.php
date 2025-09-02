<?php
session_start();
require 'admin/inc/config.php';

if (!isset($_SESSION['student_no'])) {
    header("Location: login.php"); // or an appropriate redirect
    exit();
}
// Get user info
$student_no = $_SESSION['student_no'];
$sql = "SELECT * FROM users WHERE student_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_no);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Profile image fallback
$profilePic = $user['photo'];
$fullName = $user['student_fname'] . ' ' . $user['student_mname'] . ' ' . $user['student_lname'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $birthday = $_POST['birthday'];
    $year_level = $_POST['year_level'];

   
    

    // Handle new image upload
    if (isset($_FILES["profile_pic"]) && $_FILES["profile_pic"]['error'] == 0) {
        $target_dir = "admin/uploads/";

        // Delete old image if it exists and is not the default
        if (!empty($user['photo']) && file_exists($target_dir . $user['photo'])) {
            unlink($target_dir . $user['photo']);
        }

        // Rename the new image to avoid name collision (e.g., timestamp + original filename)
        $newImageName = time() . '_' . basename($_FILES["profile_pic"]["name"]);
        $target_file = $target_dir . $newImageName;

        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
            $photo = $newImageName; // Save new image filename to DB
        }
    }

    // Update user info
    $update_sql = "UPDATE users SET email = ?, phone_number = ?, birthday = ?, year_level = ?, photo = ? WHERE student_no = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssss", $email, $phone, $birthday, $year_level, $photo, $student_no);
    $update_stmt->execute();

    // Redirect with success message
    echo "<script>
  
    alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket-Profile</title>
    <link rel="icon" href="admin/images/vmc_basket_logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="style.css">

    <style>
    .highlight-orange {
        background-color: #ffd498;
        padding: 0.3rem 1rem;
        border-radius: 6px;
        border: 1px solid black;
        box-shadow: 3px 3px 0px #000;
        font-weight: 600;
    }       

    .profile-container {
        max-width: 950px;
        height: auto;
        background-color: #F5EFEB;
        padding: 10px;
        border-radius: 10px;
        border: solid 1px #FF9E5E;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .profile-container h3 {
        font-family: "Ubuntu", sans-serif;
        font-size: 30px;
        color: #00527F;
        font-weight: bold;
    }
    .profile-container hr{
        border: solid 1px #FF9E5E;
        opacity: 0.6;
    }
    .changebtn{
        border-top: solid 1px #CED4DA;
        border-right: solid 1px #CED4DA;
        border-bottom: solid 1px #CED4DA;
        border-left:0px;
        background-color: white;
        color: #0066FF;
    }
    .changebtn:hover, .changebtn:focus, .changebtn.active {
        background-color: #0066FF;
        color: white;
    }

    .profile-img{
        width: 150px;
        height: 150px;
        object-fit: cover;
    }

    /* Tablet styles */
    @media (max-width: 991.98px) {
        .profile-container {
            padding: 8px;
        }
        .profile-img {
            width: 120px;
            height: 120px;
        }
        .profile-container h3 {
            font-size: 24px;
        }
    }
    @media (max-width: 575.98px) {

        .highlight-orange {
            font-size: 1rem;
        }
        .profile-container {
            padding: 5px;
        }
        .profile-img {
            width: 80px;
            height: 80px;
        }
        .profile-container h3 {
            font-size: 18px;
        }
        .profile-container p{
            font-size: 0.85rem;
        }
        .navbar-custom {
            padding: 0.5rem 1rem;
            flex-direction: column;
            align-items: flex-start;
        }
        .container-fluid.d-flex.align-items-center {
            justify-content: start;
        }
        .vmc-logo {
            max-width: 90px;
        }
        .search-box {
            width: 100%;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }

        .basket-btn {
            width: 38px;
            height: 38px;
            font-size: 1.2rem;
            margin-right: 5px;
        }

        .profile-section img {
            width: 70px;
            height: 70px;
        }
        footer {
            font-size: 1rem;
        }
    }

 </style>

</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-custom shadow-sm fixed-top">
        <div class="container-fluid d-flex align-items-center">
            <!-- Hamburger -->
            <button class="btn btn-link text-dark me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sideMenu">
                <i class="fas fa-bars fa-lg"></i>
            </button>

            <!-- Logo -->
            <a class="navbar-brand" href="home.php">
                <img src="admin/images/vmc_basket_logo.png" alt="VMC Basket" class="vmc-logo">
            </a>

            <!-- Search bar (desktop) -->
            <div class="flex-grow-1 position-relative me-3 d-none d-sm-block">
                <input type="text" class="form-control search-box" placeholder="Search products here...">
                <i class="fas fa-search search-icon"></i>
            </div>

            <!-- Right-aligned buttons for small devices -->
            <div class="d-flex d-sm-none ms-auto align-items-center" style="margin-right: 10px;">
                <!-- Search icon (mobile) -->
                <button class="btn p-0" type="button" id="mobileSearchToggle">
                    <i class="fas fa-search fa-lg"></i>
                </button>
            </div>
            <!-- Cart -->
            <a href="basket.php" class=" basket-btn text-decoration-none">
                <i class="fas fa-shopping-basket"></i>
            </a>

            <!-- Collapsible search bar (mobile) -->
            <div class="w-100 mt-2 d-none" id="mobileSearchBar">
                <input type="text" class="form-control search-box" placeholder="Search products here...">
            </div>
        </div>
    </nav>

    <!-- Offcanvas Sidebar -->
    <div class="offcanvas offcanvas-start offcanvas-custom" tabindex="-1" id="sideMenu">
        <div class="offcanvas-body p-0">
            <div class="d-flex justify-content-end p-2 close d-block d-lg-none" data-bs-theme="dark">
                <button type="button" class="btn-close btn btn-light" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="profile-section">
                <img src="admin/images/profile_pic.png">
                <h4 class="mt-2">Janella Clare Gomez</h4>
            </div>

            <div class="px-3">
                <div class="mb-2">
                    <button class="btn btn-link text-white w-100 text-start dropdown-toggle text-decoration-none" data-bs-toggle="collapse" data-bs-target="#profileMenu">
                    Profile
                    </button>
                    <div class="collapse ps-3" id="profileMenu">
                    <a href="profile.php">My Account</a>
                    <a href="purchase_history.php">My Purchase</a>
                    <a href="favorites.php">My Favorites</a>
                    </div>
                </div>

            <a href="home.php">Home</a>

            <div class="mt-2">
                <button class="btn btn-link text-white w-100 text-start dropdown-toggle text-decoration-none" data-bs-toggle="collapse" data-bs-target="#shopMenu">
                Shop
                </button>
                <div class="collapse ps-3" id="shopMenu">
                <a href="shop_uniforms.php">Uniforms</a>
                <a href="shop_supplies.php">School Supplies</a>
                <a href="shop_merch.php">School-related Merchandise</a>
                </div>
            </div>

            <a href="logout.php" class="mt-3 d-block">Log out</a>
            </div>
        </div>
    </div>

    <!-- My Profile Side-bar-->
    <div class="d-flex justify-content-center" style="margin-top: 100px; margin-bottom: 30px;">
        <div class="profile-container flex-grow-1 p-4">
            <h2 class="mt-4 mb-3">
                <span class="highlight-orange">My Profile</span>
            </h2>
            <p class="mb-3">Manage and protect your account</p>
            <hr>

    <!-- Editable Form -->
    <form class="mt-4" method="POST" enctype="multipart/form-data">
            <div class="text-center">
                <!-- Dynamic Profile Picture -->
                <!-- Display the Profile Image -->
                <img src= "admin/uploads/<?= $profilePic ?>" class="rounded-circle mb-3 profile-img" id="profileImage" name="profileImage" alt="profile">
                <br>
                <input type="file" id="imageUpload" accept="image/*" name="profile_pic" style="display: none;" onchange="previewImage(event)">
                <button type="button" class="custom-navy-btn mt-2" onclick="document.getElementById('imageUpload').click();">Select Image</button>
                <hr>
            </div>
        <div class="row mb-3">
            <div class="col-md-6 mb-2">
            <label class="form-label">Student Number</label>
            <input type="text" name="student_no" class="form-control" value="<?php echo $user['student_no']; ?>" readonly tabindex="-1" style="pointer-events: none; background-color: #e9ecef;">
            </div>
            <div class="col-md-6 mb-2">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" value="<?php echo $fullName; ?>" readonly tabindex="-1" style="pointer-events: none; background-color: #e9ecef;">
            </div>
        </div>

        <div class="row mb-3 mb-2">
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <div class="input-group">
                <input type="email" name="email" id="email" class="form-control" value="<?php echo $user['email']; ?>" required>
                    <button class="btn changebtn" type="button" onclick="enableEdit('email', this)">Change</button>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone Number</label>
                <div class="input-group">     
                    <input type="text" id="phone" name="phone" class="form-control" value="<?php echo $user['phone_number']; ?>" required >
                    <button class="btn changebtn" type="button" onclick="enableEdit('phone', this)">Change</button>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6 mb-2">
                <label class="form-label">Date of Birth</label>
                <div class="input-group">
                    <input type="date" name="birthday" class="form-control" value="<?php echo $user['birthday']; ?>" required>
                    <button class="btn changebtn" type="button" onclick="enableEdit('dob', this)">Change</button>
                </div>
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">Year-level</label>
                <div class="input-group">
                    <input type="text" id="year" name="year_level" class="form-control" value="<?php echo $user['year_level']; ?>" required>
                    <button class="btn changebtn" type="button" onclick="enableEdit('year', this)">Change</button>
                </div>
            </div>
        </div>
        <hr>
        <div class="col-md-6 mb-2">
            <a href="forgot_password.php" class="text-decoration-none">Reset Password</a>
        </div>
        <div class="text-center mt-5">
            <button type="submit" class="custom-navy-btn" id="saveBtn" disabled>Save</button>
        </div>
    </form>
</div>

     </div>
</div>

   <!-- Footer -->
    <footer class="footer">
        <div class="container p-5">

            <!-- Logo Row -->
            <div class="row justify-content-start mb-4">
            <div class="col-auto d-flex justify-content-center align-items-center gap-3 footer-logo">
                <img src="admin/images/vmc_basket_logo.png" alt="VMC Basket Logo" class="footer-logo" >
                <img src="admin/images/VMC School logo.png" alt="School Logo" class="footer-logo">
            </div>
            </div>

            <!-- Links & Contacts Row -->
            <div class="row text-start gy-3">

            <!-- Quick Links -->
            <div class="col-md-3">
                <h5 class="fw-bold">Quick Links</h5>
                <ul class="list-unstyled">
                <li><a href="#" class="footer-link">Home</a></li>
                <li><a href="#" class="footer-link">Shop</a></li>
                </ul>
            </div>

            <!-- Contacts -->
            <div class="col-md-7">
                <h5 class="fw-bold">Contacts</h5>
                <p class="mb-1">
                <i class="bi bi-geo-alt-fill"></i>
                18 Dalsol Rd. GSIS Village, Sangandaan, Quezon City, 1116 Metro Manila, Philippines
                </p>
                <p class="mb-1">
                <i class="bi bi-telephone-fill"></i>
                +63 2 8929 0856
                </p>
                
                <div class="d-flex gap-3">
                    <p class="mb-1 fw-medium">Socials Media</p>
                    <a href="#" class="footer-icon fs-5"><i class="bi bi-globe"></i></a>
                    <a href="#" class="footer-icon fs-5"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="footer-icon fs-5"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="footer-icon fs-5"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

            <!-- Back to top -->
            <div class="col-md-2 d-flex align-items-end justify-content-md-end">
                <a href="#" class="footer-link">↑ Back to top</a>
            </div>
            </div>

            <!-- Divider -->
            <hr class="mt-5 mb-3">

            <!-- Copyright -->
            <div class="sub-footer text-center small">
            © 2024 Villager’s Montessori College. All rights served.
            </div>
        </div>
    </footer>

<!-- Javascript -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
    // Disable Save button on page load
    document.getElementById("saveBtn").disabled = true;

    // Listen for changes in the Email and Date of Birth fields
    document.getElementById('email').addEventListener('change', enableSaveButton);
    document.getElementById('dob').addEventListener('change', enableSaveButton);
});

function enableEdit(fieldId, btn) {
    let field = document.getElementById(fieldId);
    let saveButton = document.getElementById("saveBtn");

    if (fieldId === "year") {
        let currentValue = field.value;
        let select = document.createElement("select");
        select.className = "form-control shadow-none";
        select.id = fieldId;
        select.name = "year_level"; 
        let options = [
            "Pre-School",
            "Elementary Grade 1",
            "Elementary Grade 2",
            "Elementary Grade 3",
            "Elementary Grade 4",
            "Elementary Grade 5",
            "Elementary Grade 6",
            "Junior High School Grade 7",
            "Junior High School Grade 8",
            "Junior High School Grade 9",
            "Senior High School Grade 11",
            "Senior High School Grade 12",
            "Bachelor of Science in Information System",
            "Bachelor of Science in Business Administration",
            "Bachelor of Science in Elementary Education Major Pre-School and Special Education",
            "Bachelor of Science in Management and Tourism",
            "Bachelor of Science in Criminology",
            "Bachelor of Science in Hotel and Restaurant Management",
            "Bachelor of Science in Secondary Education Major in English and Mathematics"
        ];

        options.forEach(optionText => {
            let option = document.createElement("option");
            option.value = optionText;
            option.textContent = optionText;
            if (optionText === currentValue) option.selected = true;
            select.appendChild(option);
        });

        field.replaceWith(select);
        select.focus();
    } 
    else if (fieldId === "dob") {
        let currentValue = field.value;
        let dateInput = document.createElement("input");
        dateInput.type = "date";
        dateInput.className = "form-control shadow-none";
        dateInput.id = fieldId;
        dateInput.value = currentValue.replace(/\*/g, ""); // Remove masking0

        field.replaceWith(dateInput);
        dateInput.focus();
    } 
    else if (fieldId === "phone") {
        field.disabled = false;
        field.type = "tel"; 
        field.setAttribute("pattern", "[0-9]*");
        field.setAttribute("inputmode", "numeric");
        field.focus();
        field.name = "phone";
    } 
    else {
        field.disabled = false;
        field.focus();
    }

    // Keep Change button active (blue)
    btn.classList.add("active");

    // Enable Save button
    document.getElementById("saveBtn").disabled = false;
}

function enableSaveButton() {
    // Enable Save button whenever there is a change in email or date of birth
    document.getElementById("saveBtn").disabled = false;
}

function resetForm() {
    // Reset all Change buttons to default style
    document.querySelectorAll(".changebtn").forEach(button => {
        button.classList.remove("active");
    });

    // Reset only form inputs inside the profile-container (except the image upload button)
    document.querySelectorAll(".profile-container input, .profile-container select").forEach(field => {
        let value = field.value;

        if (field.id === "imageUpload") {
            // Skip resetting the image upload input
            return;
        }

        if (field.tagName === "SELECT") {
            // Convert select back to text input
            let textInput = document.createElement("input");
            textInput.type = "text";
            textInput.className = "form-control";
            textInput.id = field.id;
            textInput.value = value;
            textInput.disabled = true;
            field.replaceWith(textInput);
        } else {
            if (field.type === "date") {
                // Convert date input back to text input
                let textInput = document.createElement("input");
                textInput.type = "text";
                textInput.className = "form-control";
                textInput.id = field.id;
                textInput.value = value;
                textInput.disabled = true;
                field.replaceWith(textInput);
            } else {
                field.disabled = true;
            }
        }
    });

    // Re-enable the sidebar and other buttons (if they were disabled by mistake)
    document.querySelectorAll("button").forEach(button => {
        if (button.id !== "saveBtn" && button.id !== "imageUpload") {
            button.disabled = false;
        }
    });

    // Disable Save button again
    document.getElementById("saveBtn").disabled = true;
}

function previewImage(event) {
    var reader = new FileReader();
    reader.onload = function(){
        var output = document.getElementById('profileImage');
        output.src = reader.result;
        document.getElementById("saveBtn").disabled = false; // ✅ Enable Save
    };
    reader.readAsDataURL(event.target.files[0]);
}


</script>
</body>
</html>