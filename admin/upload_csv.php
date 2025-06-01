<?php
session_start();
require 'inc/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv-file'])) {
    $fileTmpPath = $_FILES['csv-file']['tmp_name'];
    $fileName = $_FILES['csv-file']['name'];
    $fileSize = $_FILES['csv-file']['size'];
    $fileType = $_FILES['csv-file']['type'];

    // Check if the file is a CSV
    if ($fileType !== 'text/csv') {
        echo 'Please upload a CSV file.';
        exit;
    }

    // Open the CSV file and read its contents
    if (($handle = fopen($fileTmpPath, 'r')) !== false) {
        // Skip the header if there is one
        fgetcsv($handle);

        // Prepare SQL query using prepared statements
        $query = "INSERT INTO users (student_no, student_pass, created_at, updated_at, student_fname, student_mname, student_lname, phone_number, email, birthday, year_level, photo, active_status, last_activity)
                  VALUES (?, ?, NOW(), NOW(), ?, ?, ?, ?, ?, ?, ?, ?, 'Active', NOW())";

        // Prepare the statement
        $stmt = $conn->prepare($query);

        // Loop through the file and insert the data into your table
        while (($data = fgetcsv($handle)) !== false) {
            
            $student_no = $data[0];
            $student_fname = $data[1];
            $student_lname = $data[2];
            $student_mname = $data[3];
            $email = $data[4];
            $phone_number = $data[5];
            $year_level = $data[6];
            $birthday = $data[7];
            $photo = ''; // Set to empty string or provide a default if needed


             // Photo upload handling
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
            
            // Format birthday (YYYYMMDD) for default password generation
            $formatted_birthday = str_replace("-", "", $birthday);
            
            // Generate default password: lowercased middle name plus formatted birthday
            $student_pass = strtolower($student_mname) . $formatted_birthday;

            // Hash password securely
            $hashed_password = password_hash($student_pass, PASSWORD_BCRYPT);

            // Bind parameters to the prepared statement
            $stmt->bind_param("ssssssssss", $student_no, $hashed_password, $student_fname, $student_mname, $student_lname, $phone_number, $email, $birthday, $year_level, $photo);

            // Execute the prepared statement
            if ($stmt->execute() !== true) {
                echo 'Error: ' . $stmt->error;
                exit;
            }
        }

        fclose($handle);
        echo 'CSV data inserted successfully!';
    } else {
        echo 'Error opening the CSV file.';
    }
}
?>
