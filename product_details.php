<?php

require('admin/inc/config.php');



if (isset($_GET['id'])) {
  $product_id = $_GET['id'];
} else {
  // Redirect to the shop page or show an error if no ID is provided
  header("Location: shop.php");
  exit();
}
$userId = $_SESSION['user_id'] ?? 1; // Force user_id = 1 if no login yet
$productID = (int) $_GET['id']; // Use the correct variable name here

// 2. Fetch product basic info from the database
$query = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $productID); // Use the correct variable name here
$stmt->execute();
$product_result = $stmt->get_result();
$product1 = $product_result->fetch_assoc();

if (!$product1) {
  // If no product is found, display a message or redirect
  die('Product not found.');
}

// 4. Fetch product rating details and review count
$rating = $product1['rating'] ? $product1['rating'] : '—';

// Get total number of reviews
$review_count_sql = "SELECT COUNT(*) as count FROM product_reviews WHERE product_id = ?";
$stmt = $conn->prepare($review_count_sql);
$stmt->bind_param("i", $productID);
$stmt->execute();
$review_count = $stmt->get_result()->fetch_assoc()['count'];

// Replace the existing variants query with:

// Fetch product variants with sizes and genders
$query = "SELECT DISTINCT pv.size, pv.stock, pv.gender 
         FROM product_variants pv 
         WHERE pv.product_id = ?
         ORDER BY FIELD(pv.size, 'XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL')";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $productID);
$stmt->execute();
$variants_result = $stmt->get_result();

$variants = [];
$available_sizes = [];
$available_genders = [];

while ($variant = $variants_result->fetch_assoc()) {
    $variants[$variant['size']] = [
        'stock' => $variant['stock'],
        'gender' => $variant['gender']
    ];
    $available_sizes[] = $variant['size'];
    if (!in_array($variant['gender'], $available_genders)) {
        $available_genders[] = $variant['gender'];
    }
}

// 3. Fetch the product image (if exists)
$image_url = $product1['image'] ? 'admin/' . $product1['image'] : 'admin/images/gps.jpg';

// Fetch sizes and stock from product_variants
$query = "SELECT size, stock FROM product_variants WHERE product_id = ? ORDER BY FIELD(size, 'XXS', 'XS', 'S', 'M', 'L', 'XL', 'XXL')";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $productID);
$stmt->execute();
$variants_result = $stmt->get_result();

$sizes = [];
$stock_quantity = 0;

while ($variant = $variants_result->fetch_assoc()) {
    $sizes[] = $variant['size'];
    $stock_quantity += $variant['stock']; // Sum up total stock across all sizes
}

// Add before the reviews query
$reviews_per_page = 5;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $reviews_per_page;

// After fetching product details, add this query to get reviews
$reviews_sql = "SELECT pr.*, 
                u.student_fname, 
                u.student_lname, 
                u.photo as user_photo, 
                DATE_FORMAT(pr.created_at, '%d %b %Y') as review_date 
                FROM product_reviews pr 
                LEFT JOIN users u ON pr.user_id = u.id 
                WHERE pr.product_id = ? 
                ORDER BY pr.created_at DESC 
                LIMIT ? OFFSET ?";
$stmt = $conn->prepare($reviews_sql);
$stmt->bind_param("iii", $productID, $reviews_per_page, $offset);
$stmt->execute();
$reviews_result = $stmt->get_result();

// Add after the reviews loop
$total_reviews_sql = "SELECT COUNT(*) as total FROM product_reviews WHERE product_id = ?";
$stmt = $conn->prepare($total_reviews_sql);
$stmt->bind_param("i", $productID);
$stmt->execute();
$total_reviews = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_reviews / $reviews_per_page);

// 6. Render HTML with PHP variables

// At the top of your product_details.php file
$is_uniform = stripos($product1['type'], 'Uniform') !== false;
$is_supplies = stripos($product1['type'], 'Supplies') !== false;
?>

<?php


// Only allow POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $size = trim($_POST['size'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 1);

    // Modified validation to handle supplies (no size required)
    $product_type_query = "SELECT type FROM products WHERE id = ?";
    $stmt = $conn->prepare($product_type_query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $type_result = $stmt->get_result();
    $product_type = $type_result->fetch_assoc();

    $is_uniform = stripos($product_type['type'], 'Uniform') !== false;

    // Only validate size for uniforms
    if ($product_id <= 0 || $quantity <= 0 || ($is_uniform && empty($size))) {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
        exit;
    }

    // Get product details
    $stmt = $conn->prepare("SELECT product_name, price, image FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        // Insert into basket
        $stmt = $conn->prepare("INSERT INTO basket (user_id, product_id, product_name, price, image, size, quantity)
                                    VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisissi", $userId, $product_id, $product['product_name'], $product['price'], $product['image'], $size, $quantity);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
            exit;
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to insert basket']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Product not found']);
        exit;
    }
}

// Assuming the $product is already fetched from the database
$is_supplies = stripos($product1['type'], 'Supplies') !== false;  // Check if product type is 'supplies'
?>





<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VMC Basket-Product Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css"
    rel="stylesheet">
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900&family=Ubuntu:wght@400;500;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="CSS/style.css">

  <style>
    .product-details-title {
      font-family: 'Ubuntu', sans-serif;
      font-weight: bold;
      color: #00527F;

    }

    .carousel-bg {
      background-color: #E8EDEF;
    }

    .product-infobg {
      background-color: #C8D9E6;
    }

    /* Make carousel and product info same height */
    .row.align-items-stretch>[class*='col-'] {
      display: flex;
      flex-direction: column;
    }

    /* Carousel image resizing */
    .uniform-image {
      max-height: 400px;
      width: auto;
      height: auto;
      object-fit: contain;
    }

    /* Carousel indicators (dots) */
    .carousel-indicators [data-bs-target] {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background-color: #ccc;
    }

    .carousel-indicators .active {
      background-color: #0d6efd;
    }

    /* Buttons and Quantity */
    .btn-sm {
      min-width: 40px;
    }

    .input-group .btn {
      padding: 0.375rem 0.75rem;
    }

    .form-control {
      height: auto;
    }

    /* Responsive Image Container */
    .carousel-inner {
      min-height: 400px;
      display: flex;
      align-items: center;
    }

    /* Active Size Button Style */
    .btn-outline-secondary.active {
      background-color: #0d6efd;
      color: white;
      border-color: #0d6efd;
    }

    /* Gap fix for buttons */
    .gap-2>* {
      margin-right: 0.5rem;
    }

    .product-info-box {
      background-color: #d6e7f4;
      /* similar to the light blue in your screenshot */
      border-radius: 10px;
    }

    .rating-number {
      font-size: 1.2rem;
      color: #000;

    }

    .fav-btn {
      background: transparent;
      border: none;
      padding: 0;
      cursor: pointer;
      margin-top: -10px;
      /* Optional: adjust to center it better vertically */
    }

    .fav-btn img {
      width: 24px;
      height: 24px;
    }

    .custom-btn {
      background-color: #e6f0f9;
      border: 1px solid #a5c8e2;
      border-radius: 6px;
      padding: 6px 12px;
      font-weight: 500;
      color: #000;
      cursor: pointer;
      transition: 0.2s;
    }

    .custom-btn:hover,
    .custom-btn.selected {
      background-color: #004c97;
      color: #fff;
      border-color: #004c97;
    }

    .quantity-value {
      min-width: 32px;
      text-align: center;
      font-weight: bold;
    }

    /* Remove spinners in Chrome, Safari, Edge */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    /* Remove spinner in Firefox */
    input[type=number] {
      -moz-appearance: textfield;
    }


    .product-extra-info {
      background-color: #f8fbfd;
    }

    .info-box {
      background-color: #f8fbfd;
    }

    .size-box {
      background-color: #FFFFFF;
      font-size: 0.9rem;
    }

    .size-box strong {
      display: block;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    ul {
      list-style-type: disc;
    }

    .product-ratings {
      background-color: #f8fbfd;
    }

    .bg-light-yellow {
      background-color: #fffbe6;
    }

    .btn-rating-filter {
      margin: 0.4rem;
      border: 1px solid #ccc;
      background-color: #f9f9f9;
      font-size: 0.875rem;
      padding: 0.4rem 0.75rem;
      border-radius: 0.5rem;
      color: #333;
    }

    .btn-rating-filter.active {
      background-color: #0056d2;
      color: #fff;
      border-color: #0056d2;
    }

    .review-card img.img-thumbnail {
      border-radius: 0.5rem;
      object-fit: cover;
    }
  </style>
</head>

<body>
  <!-- Header -->
  <header>
    <div class="top-text">
      <h1>ALL PRODUCTS ARE AVAILABLE FOR PICK-UP ONLY AT VILLAGERS MONTESSORI COLLEGE</h1>
    </div>
    <div class="top-container">
      <ul>
        <li><a href="basket.php"><img src="admin/images/Home Page/basket-nav.png" alt="Basket"></a></li>
        <li><a href="favorites.php"><img src="admin/images/Home Page/heart-nav.png"></a></li>
        <li><a href="profile.php"> <img src="admin/images/Home Page/profile-user-nav.png" alt="profile"></a></li>
      </ul>
    </div>
  </header>

  <!-- Navbar -->
  <div class="navbar shadow-sm">
    <div class="logo ms-4">
      <a href="index.php"><img src="admin/images/Admin Nav/VMS-LOGO-Alternative-03.png" alt="logo"></a>
      <h2>VMC Basket</h2>
    </div>
    <nav>
      <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="shop.php" class="active">Shop</a></li>
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

  <div class="container my-4">
    <h4 class=" mb-3 product-details-title">Product Details</h4>
    <div class="row align-items-stretch g-3">
      <!-- Carousel -->
      <div class="col-md-6">
        <div id="productCarousel" class="carousel slide rounded p-4" data-bs-ride="carousel">
          <div class="carousel-inner text-center">
            <div class="carousel-item active">
              <img src="<?= htmlspecialchars($image_url) ?>" class="d-block mx-auto uniform-image" alt="Product Image">
            </div>
          </div>
        </div>
      </div>

      <!-- Product Info -->

      <div class="col-md-6">
        <div class="product-infobg rounded p-4 product-info-box h-100 d-flex flex-column justify-content-between">
          <div class="d-flex justify-content-between align-items-start">
            <h4 class="fw-bold"><?= htmlspecialchars($product1['product_name']) ?></h4>



            <button class="fav-btn" onclick="toggleFavorite(event, this, <?= $product1['id'] ?>)">
              <img src="./admin/images/heart-outline.png" alt="Favorite">
            </button>

            <script>
              function toggleFavorite(event, btn, productId) {
                event.stopPropagation(); // Prevents redirection

                let heartImg = btn.querySelector("img");
                let isFavorited = heartImg.src.includes("heart.png") ? 1 : 0;
                let newStatus = isFavorited ? 0 : 1; // Toggle the current status

                // Change heart icon immediately
                heartImg.src = newStatus ? "./admin/images/heart.png" : "./admin/images/heart-outline.png";

                // Prepare the body data for AJAX
                const data = `product_id=${productId}&favorite=${newStatus}`;
                console.log('Sending data:', data);  // Debugging: check what's being sent

                // Send AJAX request
                fetch('update_favorites.php', {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                  },
                  body: data
                })
                  .then(response => response.json())
                  .then(data => {
                    console.log('Server response:', data);  // For debugging
                    if (data.success) {
                      console.log('Favorite updated successfully');
                    } else {
                      console.error('Failed to update favorite:', data.error);
                    }
                  })
                  .catch(error => {
                    console.error('Error:', error);
                  });
              }


            </script>
          </div>


          <h4 class="fw-bold text-dark"> ₱ <?= number_format($product1['price'], 2) ?></h4>

          <div class="d-flex align-items-center mb-3">
            <span class="fs-2 fw-bold me-2">
                <?php 
                if (is_null($rating) || $rating === '—' || !is_numeric($rating)) {
                    echo '—';
                } else {
                    echo number_format(floatval($rating), 1);
                }
                ?>
            </span>
            <small class="text-muted">out of 5</small>
            <div class="ms-3">
                <?php
                if (is_null($rating) || $rating === '—' || !is_numeric($rating)) {
                    // Display empty stars if no rating
                    for ($i = 0; $i < 5; $i++) {
                        echo '<i class="bi bi-star text-warning"></i>';
                    }
                } else {
                    $rating = floatval($rating);
                    // Calculate full and half stars
                    $fullStars = floor($rating);
                    $hasHalfStar = ($rating - $fullStars) >= 0.5;
                    
                    // Output full stars
                    for ($i = 0; $i < $fullStars; $i++) {
                        echo '<i class="bi bi-star-fill text-warning"></i>';
                    }

                    // Output half star if applicable
                    if ($hasHalfStar) {
                        echo '<i class="bi bi-star-half text-warning"></i>';
                    }

                    // Output empty stars
                    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                    for ($i = 0; $i < $emptyStars; $i++) {
                        echo '<i class="bi bi-star text-warning"></i>';
                    }
                }
                ?>
            </div>
            <span class="ms-2 text-muted">
                (<?= (is_null($rating) || $rating === '—' || !is_numeric($rating)) ? '0' : $review_count ?> ratings)
            </span>
          </div>

          <!-- Gender Display -->
          <div class="mt-2">
            <p class="mb-1 fw-semibold"></p>
            <p class="mb-2"><?= implode(', ', $available_genders) ?></p>
          </div>

          <?php if ($is_uniform): ?>
            <div class="mb-3">
              <p class="mb-1 fw-semibold">Size:</p>
              <div class="d-flex gap-2 flex-wrap">
                <?php foreach ($available_sizes as $size): 
                    $stock = $variants[$size]['stock'] ?? 0;
                    $disabled = $stock <= 0 ? 'disabled' : '';
                ?>
                    <button class="custom-btn" data-size="<?= $size ?>" 
                            data-stock="<?= $stock ?>" 
                            data-gender="<?= $variants[$size]['gender'] ?>"
                            <?= $disabled ?>>
                        <?= $size ?>
                    </button>
                <?php endforeach; ?>
              </div>
              <p class="mt-2 mb-0" id="stock-display">Available Stock: -</p>
              <p class="mt-1 mb-0" id="gender-display">-</p>
            </div>
          <?php else: ?>
            <div class="mb-3">
              <?php
              // For supplies, get total stock from variants table
              $supplies_stock_query = "SELECT SUM(stock) as total_stock FROM product_variants WHERE product_id = ?";
              $stmt = $conn->prepare($supplies_stock_query);
              $stmt->bind_param("i", $productID);
              $stmt->execute();
              $total_stock = $stmt->get_result()->fetch_assoc()['total_stock'] ?? 0;
              ?>
              <p class="mb-1 fw-semibold">Stock Available:</p>
              <p class="mt-2 mb-0" id="stock-display">
                  <?= $total_stock ?> pieces
              </p>
            </div>
          <?php endif; ?>

          <script>
const sizeButtons = document.querySelectorAll('.custom-btn[data-size]');
const stockDisplay = document.getElementById('stock-display');
const genderDisplay = document.getElementById('gender-display');
let selectedSize = null;

sizeButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        // Remove selected class from all buttons
        sizeButtons.forEach(b => b.classList.remove('selected'));
        
        // Add selected class to clicked button
        btn.classList.add('selected');
        selectedSize = btn.dataset.size;
        
        // Update stock and gender display
        const stock = btn.dataset.stock;
        const gender = btn.dataset.gender;
        stockDisplay.textContent = `Available Stock: ${stock}`;
        genderDisplay.textContent = ` ${gender}`;
        
        // Update quantity input max value
        const quantityInput = document.querySelector('.quantity-value');
        quantityInput.max = stock;
        if (parseInt(quantityInput.value) > parseInt(stock)) {
            quantityInput.value = stock;
        }
    });
});
</script>

          <div class="mb-2">
            <p class="mb-1 fw-semibold">Quantity:</p>
            <div class="d-flex align-items-center gap-1" id="quantity-control">
              <button class="custom-btn" data-action="decrease">−</button>
              <input type="number" class="form-control quantity-value" value="1" min="1"
                style="width: 60px; text-align: center; -moz-appearance: textfield; background-color: #e6f0f9; border: 1px solid #a5c8e2; border-radius: 6px; font-weight: 500; color: #000;" />
              <button class="custom-btn" data-action="increase">+</button>
            </div>
          </div>


          
          <script>
const quantityContainer = document.getElementById('quantity-control');
const quantityInput = quantityContainer.querySelector('.quantity-value');
const isSupplies = <?= json_encode($is_supplies); ?>;
const totalStock = <?= $is_supplies ? $total_stock : 0 ?>;

quantityContainer.addEventListener('click', (e) => {
    const btn = e.target.closest('button');
    if (!btn) return;

    const action = btn.getAttribute('data-action');
    let quantity = parseInt(quantityInput.value) || 1;
    
    // For uniforms, check selected size stock. For supplies, use total stock
    const selectedButton = document.querySelector('.custom-btn.selected');
    const maxStock = isSupplies ? totalStock : (selectedButton ? parseInt(selectedButton.dataset.stock) : 0);

    if (action === 'decrease' && quantity > 1) {
        quantity--;
    } else if (action === 'increase' && quantity < maxStock) {
        quantity++;
    }

    quantityInput.value = quantity;
});

// Restrict manual input
quantityInput.addEventListener('input', () => {
    const selectedButton = document.querySelector('.custom-btn.selected');
    const maxStock = isSupplies ? totalStock : (selectedButton ? parseInt(selectedButton.dataset.stock) : 0);
    let value = parseInt(quantityInput.value.replace(/\D/g, '')) || 1;

    if (value < 1) value = 1;
    if (value > maxStock) value = maxStock;

    quantityInput.value = value;
});
</script>


          <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary w-100" onclick="addToBasket()">Place in Basket</button>

            <script>
              function addToBasket() {
                  // For uniforms, require size selection
                  if (!isSupplies) {
                      const selectedButton = document.querySelector('.custom-btn.selected');
                      if (!selectedButton) {
                          alert('Please select a size first.');
                          return;
                      }

                      const stock = parseInt(selectedButton.dataset.stock);
                      if (stock <= 0) {
                          alert('This size is out of stock.');
                          return;
                      }
                  }

                  const productId = <?= $product1['id']; ?>;
                  const selectedSize = isSupplies ? 'N/A' : document.querySelector('.custom-btn.selected').dataset.size;
                  const quantity = parseInt(document.querySelector('.quantity-value').value) || 1;

                  // For uniforms, check stock
                  if (!isSupplies) {
                      const selectedButton = document.querySelector('.custom-btn.selected');
                      const stock = parseInt(selectedButton.dataset.stock);
                      if (quantity > stock) {
                          alert('Requested quantity exceeds available stock.');
                          return;
                      }
                  }

                  // Proceed with the fetch request...
                  fetch('basket.php', {
                      method: 'POST',
                      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                      body: `product_id=${productId}&size=${encodeURIComponent(selectedSize)}&quantity=${quantity}`
                  })
                  .then(response => response.json())
                  .then(data => {
                      if (data.success) {
                          alert('Added to basket!');
                          window.location.href = 'basket.php';
                      } else {
                          alert('Failed to add to basket: ' + (data.error || 'Unknown error.'));
                      }
                  })
                  .catch(error => {
                      console.error('Error:', error);
                      alert('Something went wrong.');
                  });
              }
            </script>


            <button class="btn btn-primary w-100" onclick="placeOrder()">Order Now</button>

            <script>
            const isUniform = <?= json_encode($is_uniform); ?>;
            

            function placeOrder() {
                // Collect order details first
                const productId = <?= $product1['id']; ?>;
                const selectedSize = isUniform ? 
                    (document.querySelector('.custom-btn.selected')?.textContent.trim() || '') : 
                    'N/A'; // Use N/A for supplies
                const quantity = document.querySelector('.quantity-value')?.value || 1;
                const price = <?php echo floatval($product1['price']); ?>;

                // Size validation - only check for uniforms
                if (isUniform && !selectedSize) {
                    alert('Please select a size first.');
                    return;
                }

                // Create form data with all required fields
                const formData = new URLSearchParams();
                formData.append('product_id', productId);
                formData.append('size', selectedSize);
                formData.append('quantity', quantity);
                formData.append('product_name', '<?php echo addslashes($product1['product_name']); ?>');
                formData.append('image', '<?php echo addslashes($product1['image']); ?>');
                formData.append('price', price);

                // Send order details via fetch
                fetch('order_details.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData.toString()
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        window.location.href = 'order_details.php';
                    } else {
                        alert('Failed to place the order: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Something went wrong while placing the order. Please try again.');
                });
            }
            </script>






          </div>
        </div>
      </div>


    </div>
  </div>

  <?php if ($is_uniform): ?>

    <!-- Description & Sizes Table -->
    <div class="container my-4">
      <div class="p-4 rounded shadow-sm">
        <div class="row">
          <div class="col-md-4">
            <div class="info-box p-3 w-100 rounded border">
              <div class="d-flex align-items-center mb-2">
                <i class="bi bi-info-circle me-2"></i>
                <h6 class="mb-0 fw-bold">Description:</h6>
              </div>
              <p class="mb-1">This is a high-quality uniform designed for comfort and style.</p>
              <p class="mb-0">Material: cotton</p>
            </div>
          </div>
          <div class="col-md-8">
            <div class="info-box p-3 rounded border">
              <div class="d-flex align-items-center mb-3">
                <i class="bi bi-rulers me-2"></i>
                <h6 class="mb-0 fw-bold">Sizes:</h6>
              </div>
              <div class="row g-2">
                <!-- Size XS -->
                <div class="col-6 col-md-4">
                  <div class="size-box rounded p-2 w-100">
                    <strong>XS</strong>
                    <ul class="mb-0 ps-3">
                      <li>Bust: 30–32 inches</li>
                      <li>Waistline: 24–25 inches</li>
                      <li>Length: 23 inches</li>
                    </ul>
                  </div>
                </div>
                <!-- Size S -->
                <div class="col-6 col-md-4">
                  <div class="size-box rounded p-2 w-100">
                    <strong>S</strong>
                    <ul class="mb-0 ps-3">
                      <li>Bust: 32–34 inches</li>
                      <li>Waistline: 25–27 inches</li>
                      <li>Length: 23.5 inches</li>
                    </ul>
                  </div>
                </div>
                <!-- Size M -->
                <div class="col-6 col-md-4">
                  <div class="size-box rounded p-2 w-100">
                    <strong>M</strong>
                    <ul class="mb-0 ps-3">
                      <li>Bust: 34–36 inches</li>
                      <li>Waistline: 27–29 inches</li>
                      <li>Length: 24 inches</li>
                    </ul>
                  </div>
                </div>
                <!-- Size L -->
                <div class="col-6 col-md-4">
                  <div class="size-box rounded p-2 w-100">
                    <strong>L</strong>
                    <ul class="mb-0 ps-3">
                      <li>Bust: 34–36 inches</li>
                      <li>Waistline: 27–29 inches</li>
                      <li>Length: 24 inches</li>
                    </ul>
                  </div>
                </div>
                <!-- Size XL -->
                <div class="col-6 col-md-4">
                  <div class="size-box rounded p-2 w-100">
                    <strong>XL</strong>
                    <ul class="mb-0 ps-3">
                      <li>Bust: 34–36 inches</li>
                      <li>Waistline: 27–29 inches</li>
                      <li>Length: 24 inches</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Reviews Section -->
  <div class="container my-5">
    <h4>Product Ratings</h4>
    <div class="p-4 rounded shadow-sm">
        <div class="d-flex align-items-center mb-3">
            <span class="fs-2 fw-bold me-2">
                <?php 
                if ($rating === '—' || !is_numeric($rating)) {
                    echo '—';
                } else {
                    echo number_format((float)$rating, 1);
                }
                ?>
            </span>
            <small class="text-muted">out of 5</small>
            <div class="ms-3">
                <?php
                if ($rating === '—' || !is_numeric($rating)) {
                    // Display empty stars if no rating
                    for ($i = 0; $i < 5; $i++) {
                        echo '<i class="bi bi-star text-warning"></i>';
                    }
                } else {
                    // Calculate full and half stars
                    $fullStars = floor($rating);
                    $hasHalfStar = ($rating - $fullStars) >= 0.5;
                    
                    // Output full stars
                    for ($i = 0; $i < $fullStars; $i++) {
                        echo '<i class="bi bi-star-fill text-warning"></i>';
                    }

                    // Output half star if applicable
                    if ($hasHalfStar) {
                        echo '<i class="bi bi-star-half text-warning"></i>';
                    }

                    // Output empty stars
                    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                    for ($i = 0; $i < $emptyStars; $i++) {
                        echo '<i class="bi bi-star text-warning"></i>';
                    }
                }
                ?>
            </div>
        </div>
        <div class="text-end mt-3">
            <?php
            // Get total number of reviews
            $review_count_sql = "SELECT COUNT(*) as count FROM product_reviews WHERE product_id = ?";
            $stmt = $conn->prepare($review_count_sql);
            $stmt->bind_param("i", $productID);
            $stmt->execute();
            $review_count = $stmt->get_result()->fetch_assoc()['count'];
            ?>
              
            <?php while ($review = $reviews_result->fetch_assoc()): ?>
                <div class="review-card border-bottom py-3">
                    <div class="d-flex align-items-start mb-2">
                        <?php
                        $userPhoto = $review['user_photo'] 
                            ? 'admin/uploads/' . $review['user_photo'] 
                            : 'admin/images/profile_pic.png';
                        ?>
                        <img src="<?= htmlspecialchars($userPhoto) ?>" 
                             class="rounded-circle me-3" 
                             alt="user" 
                             width="48" 
                             height="48"
                             style="object-fit: cover;">
                        <div>
                            <strong class="d-block">
                                <?= $review['is_anonymous'] ? 'Anonymous' : 
                                    htmlspecialchars($review['student_fname'] . ' ' . $review['student_lname']) ?>
                            </strong>
                            <div class="text-warning">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi <?= $i <= $review['rating'] ? 'bi-star-fill' : 'bi-star' ?> text-warning"></i>
                                <?php endfor; ?>
                            </div>
                            <small class="text-muted"><?= $review['review_date'] ?></small>
                        </div>
                    </div>
                    <p><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                    
                    <?php
                    // Fetch review images
                    $images_sql = "SELECT image_path FROM review_images WHERE review_id = ?";
                    $img_stmt = $conn->prepare($images_sql);
                    $img_stmt->bind_param("i", $review['id']);
                    $img_stmt->execute();
                    $images_result = $img_stmt->get_result();
                    
                    if ($images_result->num_rows > 0): ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php while ($image = $images_result->fetch_assoc()): ?>
                                <img src="<?= htmlspecialchars($image['image_path']) ?>" 
                                     class="img-thumbnail" 
                                     alt="review" 
                                     width="100"
                                     style="height: 100px; object-fit: cover;">
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>

            <a href="#" class="text-primary">
                See all reviews (<?= $review_count ?>)
            </a>
        </div>
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Review pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?id=<?= $productID ?>&page=<?= $i ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>





</body>

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
        <p>your one-stop destination for all university merchandise needs! Discover a vast collection of high-quality
          uniforms, organizational shirts, and accessories tailored to showcase your university pride.</p>
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

<script>


  // Size button logic



  // Filter button logic for product ratings
  const ratingFilterButtons = document.querySelectorAll('.btn-rating-filter');
  ratingFilterButtons.forEach(button => {
    button.addEventListener('click', () => {
      ratingFilterButtons.forEach(b => b.classList.remove('active'));
      button.classList.add('active');
    });
  });
</script>


</html>