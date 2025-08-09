<?php
require 'inc/config.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $dr_number = $_POST['dr_number'];
    $size = $_POST['size'];
    $gender = $_POST['gender'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $type = $_POST['type'];
    $admin_handled = $_POST['admin_handled'];
    $date_modified = date("Y-m-d"); // Auto-fill current date

    // Handle Image Upload
    $image_path = "";
    if (!empty($_FILES['product_image']['name'])) {
        $target_dir = "uploads/";
        $image_path = $target_dir . basename($_FILES["product_image"]["name"]);
        move_uploaded_file($_FILES["product_image"]["tmp_name"], $image_path);
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO products (product_name, dr_number, size, gender, price, stock, type, date_modified, admin_handled, image) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdissss", $product_name, $dr_number, $size, $gender, $price, $stock, $type, $date_modified, $admin_handled, $image_path);

    if ($stmt->execute()) {
        echo "<script>alert('Product added successfully!'); window.location.href='products.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <style>
    .container {
    width: 50%;
    margin: auto;
    padding: 20px;
    border-radius: 8px;
    background: #f8f8f8;
    }

    h2 {
        text-align: center;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        font-weight: bold;
    }

    input, select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .form-row {
        display: flex;
        gap: 15px;
    }

    button {
        padding: 10px 15px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
    }

    .btn-primary {
        background: #007bff;
        color: white;
    }

    .btn-danger {
        background: #dc3545;
        color: white;
    }

    #imagePreview img {
        border: 1px solid #ccc;
        border-radius: 5px;
        margin-top: 5px;
    }

    </style>
</head>
<body>

<div class="container">
    <h2>Add New Products</h2>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Product Name</label>
            <input type="text" name="product_name" required>
        </div>

        <div class="form-group">
            <label>Deliver Receipt Number</label>
            <input type="text" name="dr_number">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Size</label>
                <input type="text" name="size">
            </div>

            <div class="form-group">
                <label>Gender</label>
                <select name="gender">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Price</label>
                <input type="number" name="price" step="0.01" required>
            </div>

            <div class="form-group">
                <label>Stock</label>
                <input type="number" name="stock" required>
            </div>
        </div>

        <div class="form-group">
            <label>Type</label>
            <select name="type" required>
                <option value="Uniform">Uniform</option>
                <option value="Supplies">Supplies</option>
            </select>
        </div>

        <div class="form-group">
            <label>Admin Handled</label>
            <input type="text" name="admin_handled" required>
        </div>

        <!-- Image Upload -->
        <div class="form-group">
            <label>Add Image</label>
            <input type="file" name="product_image" id="imageUpload">
            <button type="button" onclick="previewImage()">Add Image</button>
            <div id="imagePreview"></div>
        </div>

        <!-- Buttons -->
        <div class="form-group">
            <button type="submit" class="btn-primary">Add Product</button>
            <button type="button" class="btn-danger" onclick="window.location.href='products.php'">Cancel</button>
        </div>
    </form>
</div>

<script>
function previewImage() {
    var file = document.getElementById("imageUpload").files[0];
    var reader = new FileReader();
    reader.onload = function(e) {
        var img = document.createElement("img");
        img.src = e.target.result;
        img.style.width = "100px";
        document.getElementById("imagePreview").innerHTML = "";
        document.getElementById("imagePreview").appendChild(img);
    };
    reader.readAsDataURL(file);
}
</script>

</body>
</html>
