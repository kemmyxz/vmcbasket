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
    <title>VMC Basket- My Basket</title>
    <link rel="icon" href="admin/images/vmc_basket_logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.5.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="style.css">

    <style>
        .basket-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.20);
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: 1px solid black;
        }

        .product-img {
            width: 130px;
            height: auto;
            object-fit: contain;
        }
        .product-title{
            font-size: 1.3rem;
        }
        .product-size{
            font-size: 1rem;
        }
        .product-price{
            font-size: 1.2rem;
        }
        .qty-btn {
            width: 30px;
        }

        .custom-checkbox {
            width: 20px;
            height: 20px;
            margin-right: 15px;
            border: 1px solid black;
        }

        .order-summary {
            background-color: #fff;
            border-radius: 10px;
            padding: 1rem 1.5rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.20);
            border: 1px solid black;
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
        .fav-icon{
            max-width: 300px;
            height: auto;
        }
   @media (max-width: 991.98px) {
        
        }
    @media (max-width: 575.98px) {
        .product-title{
            font-size: 1rem;
        }
        .product-size{
            font-size: 0.8rem;
        }
        .product-price{
            font-size: 1rem;
        }
        .product-img{
            width: 100px;
        }
        .highlight-blue {
            font-size: 1.2rem;
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

    <div class="container p-4" style="margin-top: 110px; margin-bottom: 50px;">
        <?php if ($basketResult->num_rows == 0): ?>
            <h2 class="mt-4 mb-5">
                <span class="highlight-blue">My Basket</span>
            </h2>
        <?php endif; ?>
        <?php if ($basketResult->num_rows > 0): ?>
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <h2 class="mt-4 mb-5">
                    <span class="highlight-blue">My Basket</span>
                </h2>
            <!-- Desktop: show p and pagination on the right -->
                <div class="d-none d-sm-flex flex-column justify-content-center align-items-end ms-auto">
                    <p class="mb-2">8 out of 100 items shows</p>
                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination custom-pagination justify-content-center">
                            <li class="page-item">
                                <a class="page-link" href="#" aria-label="Previous">
                                    <span aria-hidden="true">&lt;</span>
                                </a>
                            </li>
                            <li class="page-item"><a class="page-link" href="#">1</a></li>
                            <li class="page-item active"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#">4</a></li>
                            <li class="page-item"><a class="page-link" href="#">5</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#" aria-label="Next">
                                    <span aria-hidden="true">&gt;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>

            <!-- FOR MOBILE: show p and pagination below the heading -->
            <div class="d-block d-sm-none mb-2 w-100">
                <p class="mb-2">2 out of 100 items shows</p>
                <nav aria-label="Page navigation">
                    <ul class="pagination custom-pagination justify-content-center">
                        <li class="page-item">
                            <a class="page-link" href="#" aria-label="Previous">
                                <span aria-hidden="true">&lt;</span>
                            </a>
                        </li>
                        <li class="page-item"><a class="page-link" href="#">1</a></li>
                        <li class="page-item active"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">4</a></li>
                        <li class="page-item"><a class="page-link" href="#">5</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#" aria-label="Next">
                                <span aria-hidden="true">&gt;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
                <div class="row">
                    <!-- Left Column: Basket Items -->
                    <div class="col-md-8">
                        <div class="basket-items w-100">
                            <?php while ($row = $basketResult->fetch_assoc()): ?>
                                <div class="basket-card d-flex flex-column position-relative mb-3 p-3"
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

                <img src="./admin/<?php echo $row['image']; ?>" class="product-img me-3"
                    alt="<?php echo htmlspecialchars($row['product_name']); ?>">

                <div class="flex-grow-1 d-flex flex-column justify-content-between">
                    <div><h5 class="product-title"><?php echo htmlspecialchars($row['product_name']); ?></h5></div>
                    <?php if (strtoupper(trim($row['size'])) !== 'N/A'): ?>
                        <div class="text-muted product-size">Size: <?php echo htmlspecialchars($row['size']); ?></div>
                    <?php endif; ?>
                    <div class="fw-bold mt-2 mb-4 product-price">₱ <?php echo number_format($row['price'], 2); ?></div>

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
        <div class="order-summary p-4 rounded -100" style="height: 250px;">
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


            <button class="custom-navy-btn w-100" onclick="placeOrder()">Proceed to Order</button>

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
    <section class="text-center py-3 mb-5">
        <div class="container">
            <img src="./admin/images/basket.png" alt="Empty Basket" class="img-fluid mb-4 fav-icon">
            <h4 class="title-text fw-bold mt-1">Your Basket is empty.</h4>
            <p class="text-muted mb-5">Start shopping and find your new academic essentials.</p>
            <a href="shop_uniforms.php" class="custom-navy-btn text-decoration-none">Go to Shop</a>
        </div>
    </section>
<?php endif; ?>
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



</script>
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