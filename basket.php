<?php
require('admin/inc/config.php');

session_start();

// Get user ID from session
$userId = $_SESSION['user_id'];

// Fetch basket items for this user
$basketSql = "SELECT * FROM basket WHERE user_id = ?";
$stmt = $conn->prepare($basketSql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$basketResult = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $size = trim($_POST['size'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 1);
  
    if ($product_id <= 0 || $quantity <= 0) {
      echo json_encode(['success' => false, 'error' => 'Invalid input']);
      exit;
    }

    $stmt = $conn->prepare("SELECT product_name, price, image FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
  
    if (!$product) {
      echo json_encode(['success' => false, 'error' => 'Product not found']);
      exit;
    }

    $stmt = $conn->prepare("SELECT id, quantity FROM basket WHERE user_id = ? AND product_id = ? AND size = ?");
    $stmt->bind_param("iis", $userId, $product_id, $size);
    $stmt->execute();
    $existingProduct = $stmt->get_result()->fetch_assoc();

    if ($existingProduct) {
        // Update the quantity if the product already exists
        $newQuantity = $existingProduct['quantity'] + $quantity;
        $stmt = $conn->prepare("UPDATE basket SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $newQuantity, $existingProduct['id']);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update basket']);
        }
    } else {
        // Insert new product into basket
        $stmt = $conn->prepare("INSERT INTO basket (user_id, product_id, product_name, price, image, size, quantity)
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisissi", $userId, $product_id, $product['product_name'], $product['price'], $product['image'], $size, $quantity);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to insert into basket']);
        }
    }
    exit;
}



// Prepare basket items for display if it's a GET request
$basketItems = [];
$totalItems = 0;
$subTotal = 0.00;


// Show basket page with items if it's a GET request





$conditions = [];

if (!empty($_GET['type'])) {
    $type = $conn->real_escape_string($_GET['type']);
    $conditions[] = "type = '$type'";
}

if (!empty($_GET['size'])) {
    $size = $conn->real_escape_string($_GET['size']);
    $conditions[] = "size = '$size'";
}

if (!empty($_GET['gender'])) {
    $gender = $conn->real_escape_string($_GET['gender']);
    $conditions[] = "gender = '$gender'";
}
$whereClause = "";
if (!empty($conditions)) {
    $whereClause = "WHERE " . implode(" AND ", $conditions);
}

$sql = "SELECT * FROM products $whereClause ORDER BY date_modified DESC";
$result = $conn->query($sql);
?>

<?php

$stmt = $conn->prepare("
  SELECT b.id AS basket_id,
         b.product_id,
         b.size,
         b.quantity,
         p.product_name,
         p.price,
         p.image
    FROM basket b
    JOIN products p ON p.id = b.product_id
   WHERE b.user_id = ?
");
$stmt->bind_param('i',$user_id);
$stmt->execute();
$basket_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch recommended products
$recommendations = "SELECT p.*, 
                   GROUP_CONCAT(DISTINCT pv.gender) as genders,
                   GROUP_CONCAT(DISTINCT pv.size) as sizes,
                   COALESCE(AVG(r.rating), 0) as rating,
                   COUNT(r.id) as rating_count
                   FROM products p
                   LEFT JOIN product_variants pv ON p.id = pv.product_id
                   LEFT JOIN product_reviews r ON p.id = r.product_id
                   WHERE p.type='uniform' OR p.type='supplies'
                   GROUP BY p.id, p.product_name, p.price, p.image, p.type";
$rec_result = $conn->query($recommendations);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket-My Basket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css"
        rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Ubuntu:wght@400;500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="CSS/style.css">

    <style>
        .fav-title {
            font-family: "Ubuntu", sans-serif;
            font-weight: bold;
            color: #00527F;
        }

        .price-text {
            font-weight: bold;
            color: #00527F;
        }


        .carousel-container {
            background-color: #E8EDEF;
            padding: 40px 20px;
        }

        .carousel-control-prev,
        .carousel-control-next {
            width: 40px;
            height: 40px;
            background: rgba(0, 0, 0, 0.5);
            /* Semi-transparent */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
        }

        .carousel-control-prev {
            left: 0px;
            /* Adjust this to move the button inside */
        }

        .carousel-control-next {
            right: 0px;
            /* Adjust this to move the button inside */
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            width: 15px;
            height: 15px;
        }

        .basket-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .product-img {
            width: 100px;
            height: auto;
            object-fit: contain;
        }

        .uniform-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }

        /* Product Card */
        .row>div {
            display: flex;
            /* Ensure all cards in a row are the same height */
        }

        .product-card {
            background-color: #C8D9E6;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;

            /* New for equal height & layout */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            width: 100%;
            min-height: 410px;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 2px 2px 15px rgba(0, 0, 0, 0.2);
        }

        .product-card img {
            width: 100%;
            height: 250px;
            /* or adjust as needed */
            object-fit: contain;
            margin-bottom: 10px;
        }

        .product-card:hover img {
            transform: scale(1.1);
        }


        .product-title {
            font-weight: medium;
            margin-left: 100px;
            margin-top: 50px;
            margin-bottom: 20px;
        }

        /* Heart Button */

        /* Product Info */
        .product-info {
            color: #000000;
            text-align: left;
            padding: 10px 5px;
        }

        .product-info h3 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .product-info p {
            font-size: 14px;
            margin-bottom: 2px;
        }

        .price {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }

        .product-line {
            border: 1px solid #000000;
        }

        /* Star Rating */
       .product-rating {
            color: #000000;
            font-size: 14px;
            display: flex;
            align-items: center;
        }

        .text-warning {
            color: #FFD700 !important;
        }

        .bi-star-fill.text-warning {
            color: #FFD700;
        }

        .bi-star-half.text-warning {
            color: #FFD700;
        }

        .bi-star.text-warning {
            color: #ccc;
        }

        .product-rating span {
            font-size: 12px;
            color: #666;
            margin-left: 5px;
        }

        .filter {
            background-color: white;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #00527F;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .filter-color {
            color: white;
            background-color: #00527F;
        }

        .filter-title {
            font-family: "Ubuntu", sans-serif;
            color: #00527F;
            font-weight: bold;
        }

        .form-select {
            appearance: none;
            /* Hides default arrow */
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="white"><path d="M1.5 5.5l6.5 6.5 6.5-6.5H1.5z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 16px;
        }

        /* Heart Button */
        .heart-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            border: none;
            background: none;
            font-size: 22px;
            color: black;
            cursor: pointer;
            z-index: 10;
        }

        .heart-btn img {
            width: 24px;
            /* Adjust size */
            height: auto;
        }

        .qty-btn {
            width: 30px;
        }

        .custom-checkbox {
            width: 20px;
            height: 20px;
            margin-right: 15px;
        }

        .order-summary {
            background-color: #fff;
            border-radius: 10px;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .pagination .page-link.active {
            background-color: #0d6efd;
            color: #fff;
        }

        .trash-btn {
            border: none;
            background: transparent;
            color: red;
            font-size: 1.2rem;
        }

        .footer-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
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
                <li><a href="profile.php"> <img src="admin/images/Home Page/profile-user-nav.png" alt="profile"></a>
                </li>
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

    <h2 class="text-start fav-title ms-4 mt-3 mb-4">My Basket</h2>
    <?php if ($basketResult->num_rows > 0): ?>
        <div class="container mt-4">
            <div class="row">
                <!-- Left Column: Basket Items -->
                <div class="col-md-8">
                    <div class="basket-items w-100">
                        <?php while ($row = $basketResult->fetch_assoc()): ?>
                            <div class="basket-card d-flex flex-column position-relative mb-3 p-3 border rounded shadow-sm"
                                style="min-height: 140px;">

                                <button class="trash-btn position-absolute top-0 end-0 m-2" onclick="removeItem(<?php echo $row['id']; ?>)">
                                      <i class="bi bi-trash"></i>
                                </button>

                                <script>
              function removeItem(itemId) {
                  if (confirm("Are you sure you want to remove this item from the basket?")) {
                      fetch('delete.php', {
                          method: 'POST',
                          headers: {
                              'Content-Type': 'application/x-www-form-urlencoded'
                          },
                          body: 'action=remove_item&id=' + itemId // Concatenate itemId correctly
                      })
                      .then(response => response.json())
                      .then(data => {
                          if (data.success) {
                              alert('Item removed from basket!');
                              window.location.reload();  // Reload the page to reflect changes
                          } else {
                              alert('Failed to remove item: ' + (data.error || 'Unknown error.'));
                          }
                      })
                      .catch(error => {
                          console.error('Error:', error);
                          alert('Something went wrong.');
                      });
                  }
              }
          </script>
    
                             <div class="d-flex w-100 mb-2">
                             <input
                                class="form-check-input custom-checkbox me-3 mt-2 basket-checkbox"
                                type="checkbox"
                                checked
                                data-id="<?php echo $row['id']; ?>"
                                data-size="<?php echo htmlspecialchars($row['size']); ?>"
                                data-quantity="<?php echo $row['quantity']; ?>"
                                data-price="<?php echo $row['price']; ?>">

                                    <img src="./admin/<?php echo $row['image']; ?>" class="product-img me-3 uniform-img"
                                        alt="<?php echo htmlspecialchars($row['product_name']); ?>">

                                    <div class="flex-grow-1 d-flex flex-column justify-content-between">
                                        <div><strong><?php echo htmlspecialchars($row['product_name']); ?></strong></div>
                                        <div class="text-muted">Size: <?php echo htmlspecialchars($row['size']); ?></div>
                                        <div class="price-text mt-2">₱ <?php echo number_format($row['price'], 2); ?></div>

                                        <div class="d-flex align-items-center mt-2">
                                            <button class="btn btn-outline-secondary btn-sm qty-btn minus"
                                                onclick="changeQty(<?php echo $row['id']; ?>, -1)">−</button>
                                                <span id="quantity-<?php echo $row['id']; ?>" class="mx-2 quantity"><?php echo $row['quantity']; ?></span>
                                            <button class="btn btn-outline-secondary btn-sm qty-btn plus"
                                                onclick="changeQty(<?php echo $row['id']; ?>, 1)">+</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                      <script>
                        function changeQty(itemId, increment) {
    const quantityElement = document.querySelector(`#quantity-${itemId}`);
    let currentQuantity = parseInt(quantityElement.textContent);
    const newQuantity = currentQuantity + increment;

    if (newQuantity <= 0) {
        alert("Quantity can't be less than 1.");
        return;
    }

    fetch('delete.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=update_qty&id=${itemId}&quantity=${newQuantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            quantityElement.textContent = newQuantity;  // Update the quantity on the page
        } else {
            alert('Failed to update quantity: ' + (data.error || 'Unknown error.'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Something went wrong.');
    });
}

                      </script>
                      

                        <!-- Footer Controls -->
                        <div class="footer-controls mt-3 mb-4 d-flex justify-content-between align-items-center">
                        <button id="clear-all-btn" class="btn btn-outline-danger mt-1" onclick="clearAllItems()">
                            <i class="bi bi-trash"></i> Clear All
                        </button>


                        <script>
                        function clearAllItems() {
                              if (confirm("Are you sure you want to clear all items from the basket?")) {
                                  fetch('delete.php', {
                                      method: 'POST',
                                      headers: {
                                          'Content-Type': 'application/x-www-form-urlencoded'
                                      },
                                      body: 'action=clear_all' // Send action to clear all items
                                  })
                                  .then(response => response.json())
                                  .then(data => {
                                      if (data.success) {
                                          alert('All items removed from basket!');
                                          window.location.reload();  // Reload the page to reflect changes
                                      } else {
                                          alert('Failed to clear basket: ' + (data.error || 'Unknown error.'));
                                      }
                                  })
                                  .catch(error => {
                                      console.error('Error:', error);
                                      alert('Something went wrong.');
                                  });
                              }
                          }
                          </script>

                            <!-- <div class="d-flex justify-content-end">
                                <ul class="pagination">
                                    <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                    <li class="page-item"><a class="page-link" href="">2</a></li>
                                    <li class="page-item"><a class="page-link" href="">3</a></li>
                                    <li class="page-item"><a class="page-link" href="#">Next</a></li>
                                </ul>
                            </div> -->
                        </div>
                    </div>
                </div>

                <!-- Right Column: Order Summary -->
                <div class="col-md-4">

                    <?php
                    while ($item = $basketResult->fetch_assoc()) {
                        $basketItems[] = $item; 
                        $totalItems += $item['quantity'];
                        $subTotal += $item['price'] * $item['quantity'];
                    }
                    ?>
                    <div class="order-summary p-4 border rounded shadow-sm w-100" style="height: 250px;">
                        <h5 class="mb-3">Order Summary</h5>
                        <div class="d-flex justify-content-between">
                            <span><strong>Sub-total (<span id="total-items"><?php echo $totalItems; ?></span>
                                    item<?php echo $totalItems > 1 ? 's' : ''; ?>)</strong></span>
                            <span>₱ <span id="subtotal-amount"><?php echo number_format($subTotal, 2); ?></span></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <h4><strong>Total</strong></h4>
                            <h4><strong>₱ <span id="total-amount"><?php echo number_format($subTotal, 2); ?></span></strong>
                            </h4>
                        </div>


                        <button class="btn btn-primary w-100" onclick="placeOrder()">Proceed to Order</button>
                    <script>
                    function placeOrder() {
                    if (!confirm('Are you sure you want to place the order?')) return;

                    // collect all checked items
                    const checked = Array.from(
                        document.querySelectorAll('.basket-checkbox:checked')
                    );
                    if (checked.length === 0) {
                        alert('Please select at least one item to order.');
                        return;
                        
                    }

                    const itemCount = checked.length;

                    

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'get_item_count.php';

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'item_count';
                    input.value = itemCount;

                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();


                    // build up POST params
                    const params = new URLSearchParams();
                    checked.forEach(box => {
                        const id   = box.dataset.id;
                        const size = box.dataset.size;
                        // read the live quantity from your span#quantity-{id}
                        const qty  = document.getElementById(`quantity-${id}`).textContent;
                        params.append('basket_ids[]', id);
                        params.append(`size[${id}]`, size);
                        params.append(`quantity[${id}]`, qty);
                    });

                
                    // send to your new place_orders.php
                    fetch('place_order.php', {
                        method: 'POST',
                        body: params
                    })
                    .then(r => r.json())
                    .then(js => {
                        if (js.success) {
                        window.location.href = 'proceed_order.php';
                        } else {
                        alert('Failed to place order: ' + (js.error || 'Unknown error'));
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Something went wrong when placing your order.');
                    });
                    }
                    </script>






                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <section class="text-center py-5">
            <div class="container">
                <img src="./admin/images/basket.png" alt="Empty Basket" style="max-width: 300px;">
                <h4 class="fav-title mt-1">Your Basket is empty.</h4>
                <p class="text-muted">Start shopping and find your new uniform.</p>
                <a href="shop.php" class="btn btn-secondary px-4 py-2 shadow-sm mt-2">Go to Shop</a>
            </div>
        </section>
    <?php endif; ?>


    <!-- RECOMMENDATIONS SECTION -->
    <div class="carousel-container">
        <h2 class="mb-4 fav-title">Buy your VMC Essentials</h2>

        <div class="container mb-5">
            <div class="row g-4">
                <?php
                if ($rec_result && $rec_result->num_rows > 0):
                    while ($rec_row = $rec_result->fetch_assoc()):
                ?>
                <div class="col-md-4 col-lg-3">
                    <div class="product-card" onclick="location.href='product_details.php?id=<?= $rec_row['id'] ?>'">
                        <button class="heart-btn" onclick="toggleFavorite(event, this, <?= $rec_row['id'] ?>)">
                            <img src="./admin/images/heart-outline.png" alt="Favorite">
                        </button>
                        <img src="admin/<?= htmlspecialchars($rec_row['image']) ?>" alt="<?= htmlspecialchars($rec_row['product_name']) ?>">
                        <div class="product-info">
                            <h3><?= htmlspecialchars($rec_row['product_name']) ?></h3>
                            <p> <?= htmlspecialchars($rec_row['genders'] ?? '') ?></p>
                            <hr class="product-line">
                            <div class="d-flex justify-content-between">
                                <h4 class="price">₱<?= number_format($rec_row['price'], 2) ?></h4>
                                <div class="product-rating">
                                    <?php
                                    $rating = $rec_row['rating'] ?? 0;
                                    $fullStars = floor($rating);
                                    $halfStar = round($rating - $fullStars, 1) >= 0.5;
                                    
                                    // Display full stars
                                    for ($i = 0; $i < $fullStars; $i++) {
                                        echo '<i class="bi bi-star-fill text-warning"></i>';
                                    }
                                    
                                    // Display half star if applicable
                                    if ($halfStar) {
                                        echo '<i class="bi bi-star-half text-warning"></i>';
                                        $i++;
                                    }
                                    
                                    // Display empty stars
                                    for (; $i < 5; $i++) {
                                        echo '<i class="bi bi-star text-warning"></i>';
                                    }
                                    
                                    echo '<span class="ms-1">(' . number_format($rating, 1) . ')</span>';
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; else: ?>
                    <p>No recommended products available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    // Toggle favorite status
    function toggleFavorite(event, btn, productId) {
        event.stopPropagation(); // Prevents redirection
        
        let heartImg = btn.querySelector("img");
        let isFavorited = heartImg.src.includes("heart.png") ? 1 : 0;
        let newStatus = isFavorited ? 0 : 1; // Toggle the current status

        // Change heart icon immediately
        heartImg.src = newStatus ? "./admin/images/heart.png" : "./admin/images/heart-outline.png";

        // Send AJAX request to update favorite status
        const data = `product_id=${productId}&favorite=${newStatus}`;
        fetch('update_favorites.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: data
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Failed to update favorite: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Something went wrong.');
        });
    }
    </script>


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
                    <p>your one-stop destination for all university merchandise needs! Discover a vast collection of
                        high-quality uniforms, organizational shirts, and accessories tailored to showcase your
                        university pride.</p>
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

   

    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
        crossorigin="anonymous"></script>
    <script>
        function recalculateTotal() {
            let totalItems = 0;
            let subtotal = 0;

            document.querySelectorAll('.basket-checkbox').forEach(checkbox => {
                if (checkbox.checked) {
                    const price = parseFloat(checkbox.dataset.price);
                    const quantity = parseInt(checkbox.dataset.quantity);
                    subtotal += price * quantity;
                    totalItems += quantity;
                }
            });

            // Update the Order Summary
            document.getElementById('total-items').textContent = totalItems;
            document.getElementById('subtotal-amount').textContent = subtotal.toFixed(2);
            document.getElementById('total-amount').textContent = subtotal.toFixed(2);
        }

        // Attach event listeners to all checkboxes
        document.querySelectorAll('.basket-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', recalculateTotal);
        });

        // Recalculate once at start just in case
        recalculateTotal();
    </script>
</body>

</html>