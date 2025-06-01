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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"><link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="CSS/style.css">

    <style>
    .profile-container {
        max-width: 950px;
        max-height: 850px;
        background-color: #F5EFEB;
        padding: 10px;
        margin-left:60px;
        border-radius: 10px;
        border: solid 1px #FF9E5E;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    .sidebar {
        width: 350px;
        background: white;
        padding: 20px;
        height: 900px;
    }

    .btn-active {
        font-weight: bold;
        color: #00527F !important;
        background-color: #C8D9E6;
        padding: 10px
    }
    .sidebar a {
        color: #00527F;
        text-decoration: none;
    }
    .sidebar a:hover {
        font-weight: bold;
        border-radius: 3px;
        color: #00527F !important;
        background-color: #C8D9E6;
        padding: 10px
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
        background-color: #E9ECEF;
        color: #0066FF;
    }
    .changebtn:hover, .changebtn:focus, .changebtn.active {
        background-color: #0066FF;
        color: white;
    }
 </style>

</head>
<body>
    <!-- Header -->
    <header>
        <div class="top-text"><h1>ALL PRODUCTS ARE AVAILABLE FOR PICK-UP ONLY AT VILLAGERS MONTESSORI COLLEGE</h1></div>
        <div class="top-container">
            <ul>
                <li><a href="basket.php"><img src="admin/images/Home Page/basket-nav.png" alt="Basket"></a></li>  
                <li><a href="favorites.php"><img src="admin/images/Home Page/heart-nav.png"></a></li>
                <li><a href="profile.php"> <img src="admin/images/Home Page/profile-user-nav.png" alt="profile"></a></li>
            </ul>
        </div>
    </header>

    <!-- Navbar -->
    <div class="navbar shadow-sm mb-4">
        <div class="logo ms-4">
            <a href="index.php"><img src="admin/images/Admin Nav/VMS-LOGO-Alternative-03.png" alt="logo"></a>
            <h2>VMC Basket</h2>
        </div>
        <nav>
            <ul>
                <li><a href="index.php" >Home</a></li>
                <li><a href="shop.php">Shop</a></li>
                <li><a href="contact.php">Contact us</a></li>
            </ul>
        </nav>
        <div class="search" style="display: flex;  align-items: center; justify-content: space-between; width: auto;">
            <div class="search-container me-4">
                <input type="text" class="form-control" placeholder="">
                <button><img src="admin/images/search-icon.png" alt="Search"></button>
            </div>
        </div>
    </div>

    <!-- My Profile Side-bar-->
    <div class="d-flex">
        <div class="sidebar ms-5 ">
            <!-- USER PROFILE PIC AND NAME-->
            <h5 style="font-size: 16px;">
            <!-- Display the Profile Image -->
                <img src= "admin/uploads/<?= $profilePic ?>" class="rounded-circle mb-3" width="120" id="profileImage" name="profileImage" alt="profile">

                <br>    
                <?php echo htmlspecialchars($fullName); ?>
               
                
            </h5>


             <!-- SIDE-BAR-->
            <div class="mt-4 mb-4">
                <button class="btn w-100 text-start btn-active d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#accountMenu" aria-expanded="true">
                    <img src="admin/images/profile_pic.png" alt="profile" width="20" class="me-2">
                    My profile
                </button>
                <div id="accountMenu" class="collapse show mb-3 mt-1">
                    <a href="forgot_pass.php" class="d-block text-decoration-none ps-3 text-muted mb-3"><img src="admin/images/locked.png" alt="profile" width="20" class="me-2">Change Password</a>
                </div>
                <a href="purchase_history.php" class="d-block text-decoration-none mb-3 mt-3"><img src="admin/images/bill.png" alt="profile" width="20" class="me-2">My Purchase</a>
                <a href="logout.php" class="d-block text-decoration-none"><img src="admin/images/logout.png" alt="profile" width="20" class="me-2">Log out</a>
            </div>
        </div>
        
       <!-- Profile Information -->
       <div class="profile-container flex-grow-1 p-4">
    <h3>My Profile</h3>
    <p class="mb-3">Manage and protect your account</p>
    <hr>
    

    <!-- Editable Form -->
    <form class="mt-4" method="POST" enctype="multipart/form-data">
            <div class="text-center">
                <!-- Dynamic Profile Picture -->
               
                <img src="admin/uploads/<?=$profilePic ?>" class="rounded-circle mb-3" width="120" id="profileImage">
                <br>
                <input type="file" id="imageUpload" accept="image/*" name="profile_pic" style="display: none;" onchange="previewImage(event)">
                <button type="button" class="btn btn-primary mt-2 w-25 mb-3" onclick="document.getElementById('imageUpload').click();">Select Image</button>
                <hr>
            </div>
        <div class="row mb-3">
            <div class="col-md-6 mb-2">
                <label class="form-label">Student Number</label>
                <input type="text" name="student_no" class="form-control" value="<?php echo $user['student_no']; ?>" readonly>
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label">Full Name</label>
                <input type="text" name="full_name" class="form-control" value="<?php echo $fullName; ?>" readonly>
            </div>
        </div>

        <div class="row mb-3 mb-2">
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <div class="input-group">
                <input type="email" name="email" class="form-control" value="<?php echo $user['email']; ?>" required>
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
        <div class="text-center mt-5">
            <button type="submit" class="btn btn-sm btn-primary w-25" id="saveBtn" disabled>Save</button>
        </div>
    </form>
</div>

     </div>
</div>

<!-- Footer -->
<footer>
    <div class="footer-container">
        <div class="footer-logo">
            <img src="admin/images/Footer/VMS-LOGO-Official-01.png" alt="logo">
            <div class="logo-text">
                <h2>VMC Basket</h2>
                <h4>Villagers Montesorri College E-commerce Website</h4>
            </div>
        </div>

        <div class="footer-links mt-5">
            <div class="about">
                <p>your one-stop destination for all university merchandise needs! Discover a vast collection of high-quality uniforms, organizational shirts, and accessories tailored to showcase your university pride.</p>
            </div>
            <div class="footer-nav">
                <h4>Links</h4>
                <ul>
                    <li><a href="index.html">Home</a></li>
                    <li><a href="shop.html">Shop</a></li>
                    <li><a href="contact.html">Contact us</a></li>
                </ul>
            </div>
            <div class="services">
                <h4>Customer Services</h4>
                <ul>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Size Guide</a></li>
                    <li><a href="#">Exchange & Returns</a></li>
                </ul>
            </div>
            <div class="myAccount">
                <h4>My Account</h4>
                <ul>
                    <li><a href="#">Submit Feedback</a></li>
                    <li><a href="#">Favorites</a></li>
                    <li><a href="#">Shopping cart</a></li>
                </ul>
            </div>
        </div>

        <div class="socials mt-4">
            <div class="footer-acknowledgement">
                <div class="policy">
                    <ul>
                        <li><a href="#">About |</a></li>
                        <li><a href="#">Privacy Policy |</a></li>
                        <li><a href="#">Terms of Services</a></li>
                    </ul>
                </div>
                <div class="copy">
                    <h4>©2024 Villagers Montesorri College. All rights reserved.</h4>
                </div>
            </div>
            
            <div class="footer-social mt-4">
                <a href="#"><img src="admin/images/Footer/www.png" alt="Website"></a> 
                <a href="facebook.com"><img src="admin/images/Footer/facebook-footer.png" alt="facebook"></a>
                <a href="#"><img src="admin/images/Footer/instagram.png" alt="instagram"></a>
                <a href="#"><img src="admin/images/Footer/youtube.png" alt="youtube"></a> 
            </div>
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


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>