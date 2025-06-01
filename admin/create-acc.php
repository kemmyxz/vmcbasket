<?php
session_start();
require 'inc/config.php';
require 'inc/essentials.php'; 


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Student Account</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        
        .container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 40%;
            padding: 20px;  
        }

        .container header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .container header img {
            width: auto;
            height: 40px;
            margin-right: 10px;
        }

        .container header h1 {
            font-size: 1.5rem;
            color: #2b5797;
            margin: 0;
        }

        .note {
            background: #eaf2f8;
            color: #2b5797;
            font-size: 0.9rem;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 20px;
        }
        .form{
            width: 80%;
        }
        .form-group {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 15px;
        }

        .form-group label {
            display: flex;
            font-size: 0.9rem;
            color: #555;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            font-size: 0.9rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .form-group input[type="file"] {
            padding: 3px;
        }

        .form-group .gender {
            display: flex;
            align-items: center;
            justify-content: space-around;
        }

        .gender label {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
            color: #555;
        }

        .actions {
            display: flex;
            justify-content: space-between;
        }

        .actions button {
            border: none;
            padding: 10px 20px;
            font-size: 0.9rem;
            border-radius: 4px;
            cursor: pointer;
        }

        .actions .cancel {
            background: #ffdddd;
            color: #d9534f;
        }

        .actions .add {
            background: #007bff;
            color: #fff;
        }

        .actions .add:hover {
            background: #0056b3;
        }

        .actions .cancel:hover {
            background: #f5c6cb;
        }
    </style>
</head>
<body>

    <div class="container" >
        <!-- <header>
       <img src="" alt="add user icon" style="height: 100px;"> 
        <img src="./images/Admin Nav/VMS-LOGO-Alternative-03.png" alt="VMC Logo">
            <h1>Create a Student Account</h1>
        </header> -->
        <div class="note">
            Note: Your student number must match with your school ID for verification.
        </div>
        <form action="" method="POST">
            <div class="student_name" style="display: flex; justify-content: space-between;">
            <div class="form-group">
                <label for="Fname">First Name</label>
                <input type="text" id="name" name="student_fname" placeholder="Enter your First name" required>
            </div>
            <div class="form-group" style="margin-left:2%;">
                <label for="name">Middle Name</label>
                <input type="text" id="name" name="student_mname" placeholder="Enter your Middle name" required>
            </div>
            <div class="form-group" style="margin-left:2%;">
                <label for="name">Last Name</label>
                <input type="text" id="name" name="student_lname" placeholder="Enter your Last name" required>
            </div>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" placeholder="Enter your Email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name= "phone_number" placeholder="Enter your phone number"required>
            </div>
            <div class="form-group">
                <label for="student-number">Student Number</label>
                <input type="text" id="student-number" name="student_no" placeholder="Enter your student number" required>
            </div>
            <div class="form-group">
                <label for="course">Year-Level/Course</label>
                <select id="course" name="year_level" required>
                    <option value="">Select Year-Level/Course</option >
                    <option value="1">Pre-School</option>
                    <option value="2">Elementary Grade 1</option>
                    <option value="3">Elementary Grade 2</option>
                    <option value="4">Elementary Grade 3</option>
                    <option value="5">Elementary Grade 4</option>
                    <option value="6">Elementary Grade 5</option>
                    <option value="7">Elementary Grade 6</option>
                    <option value="8">Junior High School Grade 7</option>
                    <option value="9">Junior High School Grade 8</option>
                    <option value="10">Junior High School Grade 9</option>
                    <option value="11">Junior High School Grade 10</option>
                    <option value="12">Senior High School Grade 11</option>
                    <option value="13">Senior High School Grade 12</option>
                    <option value="14">Bachelor of Science in Information System </option>
                    <option value="15">Bachelor of Science in Business Administration</option>
                    <option value="16">Bachelor of Science in Elementary Education Major Pre-School and Special Education</option>
                    <option value="17">Bachelor of Science in Management and Tourism</option>
                    <option value="18">Bachelor of Science in Criminology</option>
                    <option value="19">Bachelor of Science in Hotel and Restaurant Management</option>
                    <option value="20">Bachelor of Science in Secondary Education Major in English and Mathematics</option>
                </select>
            </div>
            <div class="form-group">
                <label for="dob">Date of Birthday</label>
                <input type="date" id="dob" name="birthday" required pattern="\d{4}-\d{2}-\d{2}">
            </div>
            <div class="form-group">
                <label for="picture">Picture</label>
                <input type="file" id="picture" name="photo">
            </div>
           
            
            <div class="actions">
                <a href="customers.php"><button type="button" class="cancel">Cancel</button></a>
                <button type="submit" class="add">Create Account</button>
            </div>
        </form>
    </div>
</body>
 <script>
    document.getElementById('dob').addEventListener('dob', function () {
        let inputDate = new Date(this.value);
        let currentYear = new Date().getFullYear();
        let minYear = currentYear - 2; // Must be at least 2 years earlier

        if (inputDate.getFullYear() > minYear) {
            alert("Error: Birthdate must be at least 2 years before the current date!");
            this.value = ""; // Clear invalid input
        }
    });

 </script>

</html>

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch form data safely
    $student_no = $_POST['student_no'] ?? '';
    $student_fname = $_POST['student_fname'] ?? '';
    $student_mname = $_POST['student_mname'] ?? '';
    $student_lname = $_POST['student_lname'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone_number = $_POST['phone_number'] ?? '';
    $birthday = $_POST['birthday'] ?? '';
    $year_level = $_POST['year_level'] ?? '';

    // Validate correct date format (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)) {
            echo "<script>alert('Invalid date format! Please enter a valid birthday.'); window.history.back();</script>";
            exit();
        }

        // Ensure the year is at least 2 years before the current year
        $birth_year = date('Y', strtotime($birthday));
        $current_year = date('Y');
        if ($birth_year > $current_year - 2) {
            echo "<script>alert('Birthdate must be at least 2 years before the current date!'); window.history.back();</script>";
            exit();
        }
    // Format the birthday (YYYYMMDD)
    $formatted_birthday = str_replace("-", "", $birthday);
    
    // Generate default password
    $student_pass = strtolower($student_mname) . $formatted_birthday;

    // Hash password securely
    $hashed_password = password_hash($student_pass, PASSWORD_BCRYPT);

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

    // Validate Birthday (must be at least 2 years before the current date)
    $min_birthdate = date('Y-m-d', strtotime('-2 years'));
    if ($birthday > $min_birthdate) {
        echo "<script>alert('Error: Birthday must be at least 2 years before the current date!'); window.history.back();</script>";
        exit();
    }

    // Check if the Student ID already exists
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

    // Handle file upload
    $photo = "default.png"; // Default image
    if (!empty($_FILES['photo']['name'])) {
        $target_dir = "uploads/";
        $photo = basename($_FILES["photo"]["name"]);
        $target_file = $target_dir . $photo;
        move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file);
    }

    // Insert into database
    $sql = "INSERT INTO users (student_no, student_pass, created_at, updated_at, student_fname, student_mname, student_lname, email,phone_number, birthday, year_level, photo) 
            VALUES (?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssssssss", $student_no, $hashed_password, $student_fname, $student_mname, $student_lname, $email, $phone_number, $birthday, $year_level, $photo);
        
        if ($stmt->execute()) {
            echo "<script>alert('User added successfully! Default Password: $student_pass'); window.location.href='customer.php';</script>";
            exit();
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Error preparing statement: " . $conn->error . "');</script>";
    }
    $conn->close();
}
?>



