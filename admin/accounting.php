<?php
require('inc/config.php');

// Fetch all products
$sql = "SELECT p.*, pv.size, pv.stock, p.image 
        FROM products p 
        LEFT JOIN product_variants pv ON p.id = pv.product_id";
$result = $conn->query($sql);

$products = [];
while ($row = $result->fetch_assoc()) {
    if (!isset($products[$row['id']])) {
        $products[$row['id']] = [
            'id' => $row['id'],
            'name' => $row['product_name'],
            'price' => $row['price'],
            'type' => $row['type'],
            'image' => $row['image'],
            'sizes' => []
        ];
    }
    if ($row['size']) {
        $products[$row['id']]['sizes'][] = $row['size'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VMC Basket - Admin/Receipt Form</title>
    <?php include 'links.php'; ?>
    <style>
         .bottom-label{
            background-color: #26387D;
            color: white;
            font-style: "Kulim Park", sans-serif;
            padding: 0px;
        }

        .bottom-label h2 {
            color: white;
            font-weight: 600;
        }
        /* Match Bootstrap's form-control height and padding */
        .select2-container--default .select2-selection--single {
            height: 38px;
            padding: 0.375rem 0.75rem;
            border: 1px solid #00527F;
            border-radius: 0.375rem;
            font-size: 1rem;
            font-family: inherit;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 26px; /* vertically center text */
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px;
            right: 10px;
        }

        /* Match Bootstrap focus style */
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #045A83;
            box-shadow: 0 0 0 0.25rem rgba(4, 90, 131, 0.25);
            outline: 0;
        }

        /* Remove default arrow spacing to align with Bootstrap */
        .select2-selection__arrow b {
            margin-top: 4px;
        }

        /* Style for the Select2 search box inside dropdown */
        .select2-container--default .select2-search--dropdown .select2-search__field {
            padding-left: 30px;
            background-image: url('images/search-icon.png'); /* Make sure the path is correct */
            background-repeat: no-repeat;
            background-position: 8px 50%;
            background-size: 14px 14px;
        }

    </style>

</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Top Navbar (visible only on small devices) -->
            <nav class="navbar navbar-light bg-light d-md-none">
                <div class="container-fluid d-flex justify-content-between align-items-center">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
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
                    <a href="cus.php" class="nav-link">
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
                    <a href="accounting.php" class="nav-link active">
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
            <main class="col-md-9 ms-sm-auto col-lg-10 content p-5">
                
                <!-- Content -->
                <div class="container p-5 overflow-hidden position-relative">
                    <div class="bottom-label text-start p-3 position-absolute top-0 start-0 w-100">
                        <h2>Receipt Form</h2>
                        <p>Receipt for Walk-in Students</p>
                    </div>
                    <div style="margin-top: 100px;"></div><!-- Add space for the label height -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="customerName" class="form-label">Customer Name</label>
                                <input type="text" class="form-control" id="customerName" placeholder="Enter Name">
                            </div>
                        </div>
                        
                        <div id="product-group">
                            <div class="row mb-3 product-item align-items-end">
                                <div class="col-md-4">
                                    <label for="productName" class="form-label">Product Name</label>
                                    <select class="form-select product-select with-search-icon" name="product[]">
                                        <option value="" disabled selected>Select Product</option>
                                        <?php foreach ($products as $product): ?>
                                            <option value="<?= $product['id'] ?>" 
                                                    data-type="<?= $product['type'] ?>"
                                                    data-price="<?= $product['price'] ?>"
                                                    data-sizes='<?= json_encode($product['sizes']) ?>'>
                                                <?= htmlspecialchars($product['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Size</label>
                                    <select class="form-select" name="size[]">
                                        <option value="" disabled selected>Select Size</option>
                                        <option value="Small">S</option>
                                        <option value="Medium">M</option>
                                        <option value="Large">L</option>
                                        <option value="XL">XL</option>
                                    </select>
                                </div>

                                <div class="col-md-3 d-flex align-items-end">
                                    <div class="w-100">
                                        <label class="form-label">Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" name="price[]" min="0" step="0.01" placeholder="0.00" readonly>
                                        </div>
                                    </div>
                                </div>

                            <div class="col-md-3 d-flex align-items-end">
                                <div class="w-100">
                                    <label class="form-label">Quantity</label>
                                    <div class="input-group">
                                    <input type="number" class="form-control" name="quantity[]" value="1" min="1">
                                    <button type="button" class="btn btn-outline-danger remove-item d-none">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                    <!-- Add More Button -->
                    <div class="mb-4 text-end">
                        <button type="button" class="btn btn-outline-secondary" id="addItemBtn">+ Add Item</button>
                    </div>


                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="paymentMode" class="form-label">Mode of Payment</label>
                            <select class="form-select" id="paymentMode">
                                <option selected disabled>Select Mode</option>
                                <option value="Cash (Pay at the Counter)">Cash</option>
                                <option value="Send Online Receipt">GCash</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block fw-bold">Total:</label>
                            <div class="form-control bg-light fw-bold" id="totalAmount">₱ 0.00</div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="admin-btn" id="submitBtn">Submit</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content p-4">
                <div class="modal-header">
                    <h5 class="modal-title" id="receiptModalLabel">VMC Basket - Receipt Preview</h5>
                    <button type="button" class="btn-close" id="resetFormBtn" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="receiptContent">
                    <div class="text-center mb-4">
                    <img src="images/vmc_basket_logo.png" alt="VMC Logo" style="max-width: 100px;">
                    <h4 class="mt-2 LogoName mb-0">VMC Basket</h4>
                    <p class="mb-2">Official Receipt</p>
                    <p class="text-muted mb-0" id="currentDateTime"></p>
                    <hr>
                    <div id="receiptDetails">
                    <!-- Receipt content will be injected here by JS -->
                    </div>
                    <!-- QR Code Placeholder -->
                    <div id="qrCodePlaceholder" class="text-center my-3">
                        <!-- QR code will be rendered here -->
                        <div style="display:inline-block; width:120px; height:120px; background:#eee; border:2px dashed #bbb; border-radius:8px; line-height:120px; color:#bbb; font-size:18px; font-family:monospace;">
                            QR Code
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-end">
                    <button type="button" class="btn custom-navy-btn btn-lg" id="downloadBtn" onclick="downloadReceipt()">Print</button>
                </div>
            </div>
        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>


<script>
    
    $(document).ready(function () {
    // Generate Receipt ID
    function generateReceiptId() {
        const date = new Date();
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = String(date.getSeconds()).padStart(2, '0');
        const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        
        return `VMC-${year}${month}${day}-${hours}${minutes}${seconds}-${random}`;
    }

    // Initialize Select2 with dynamic product loading and disable already selected products
    function initializeProductSelect(select) {
        $(select).select2({
            placeholder: "Select Product",
            allowClear: true,
            width: '100%'
        }).on('change', function() {
            const selectedOption = $(this).find(':selected');
            const type = selectedOption.data('type');
            const price = selectedOption.data('price');
            const sizes = selectedOption.data('sizes');
            const row = $(this).closest('.product-item');
            
            // Update price
            row.find('input[name="price[]"]').val(price);
            
            // Handle size field
            const sizeSelect = row.find('select[name="size[]"]');
            if (type === 'Supplies') {
                sizeSelect.prop('disabled', true).val('');
                sizeSelect.closest('.col-md-2').hide();
            } else {
                sizeSelect.prop('disabled', false).empty();
                sizeSelect.closest('.col-md-2').show();
                
                // Add size options
                sizeSelect.append('<option value="" disabled selected>Select Size</option>');
                sizes.forEach(size => {
                    sizeSelect.append(`<option value="${size}">${size}</option>`);
                });
            }

            // Update all selects to disable already selected products
            updateProductSelectOptions();

            // Update total
            calculateTotal();
        });
    }

    // Disable already selected products in all selects
    function updateProductSelectOptions() {
        // Get all selected product IDs
        const selectedIds = [];
        $('.product-select').each(function() {
            const val = $(this).val();
            if (val) selectedIds.push(val);
        });

        $('.product-select').each(function() {
            const currentVal = $(this).val();
            $(this).find('option').each(function() {
                const optionVal = $(this).attr('value');
                // Don't disable the currently selected value in its own select
                if (optionVal && selectedIds.includes(optionVal) && optionVal !== currentVal) {
                    $(this).prop('disabled', true);
                } else {
                    $(this).prop('disabled', false);
                }
            });
            // Refresh select2 to reflect disabled options
            $(this).trigger('change.select2');
        });
    }

    // Calculate total amount
    function calculateTotal() {
        let total = 0;
        $('.product-item').each(function() {
            const price = parseFloat($(this).find('input[name="price[]"]').val()) || 0;
            const quantity = parseInt($(this).find('input[name="quantity[]"]').val()) || 0;
            total += price * quantity;
        });
        $('#totalAmount').text(`₱ ${total.toFixed(2)}`);
    }

    // Initialize first product select
    initializeProductSelect('.product-select');
    updateProductSelectOptions();

    // Add item button handler
    $('#addItemBtn').on('click', function() {
        const products = <?= json_encode($products) ?>;
        const newItem = `
            <div class="row mb-3 product-item align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Product Name</label>
                    <select class="form-select product-select" name="product[]">
                        <option value="" disabled selected>Select Product</option>
                        ${Object.values(products).map(product => `
                            <option value="${product.id}" 
                                    data-type="${product.type}"
                                    data-price="${product.price}"
                                    data-sizes='${JSON.stringify(product.sizes)}'>
                                ${product.name}
                            </option>
                        `).join('')}
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Size</label>
                    <select class="form-select" name="size[]">
                        <option value="" disabled selected>Select Size</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="w-100">
                        <label class="form-label">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" class="form-control" name="price[]" min="0" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="w-100">
                        <label class="form-label">Quantity</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="quantity[]" value="1" min="1">
                            <button type="button" class="btn btn-outline-danger remove-item">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#product-group').append(newItem);
        const $newSelect = $('#product-group .product-select').last();
        initializeProductSelect($newSelect);
        updateProductSelectOptions();
    });

    // Removing an Item
    $(document).on('click', '.remove-item', function () {
        $(this).closest('.product-item').remove();
        // After removing, update disabled options so the removed product becomes available again
        updateProductSelectOptions();
        calculateTotal();
        // Hide remove buttons if only one item left
        if ($('.product-item').length === 1) {
            $('.remove-item').addClass('d-none');
        }
    });

    // Show remove button if more than one product-item
    $(document).on('DOMNodeInserted', '#product-group', function () {
        if ($('.product-item').length > 1) {
            $('.remove-item').removeClass('d-none');
        }
    });

    // When a product is removed, update the options
    $(document).on('click', '.remove-item', function () {
        $(this).closest('.product-item').remove();
        calculateTotal();
        updateProductSelectOptions();
        // Hide remove buttons if only one item left
        if ($('.product-item').length === 1) {
            $('.remove-item').addClass('d-none');
        }
    });

    // Also update options if user clears a select2 selection
    $(document).on('change', '.product-select', function() {
        updateProductSelectOptions();
    });

    // Submit button handler
    $('#submitBtn').on('click', function() {
        const receiptId = generateReceiptId();
        let receiptHTML = '';
        let isValid = true;

        // Get values
        const customerName = $('#customerName').val().trim();
        const paymentMode = $('#paymentMode').val();

        // Validations
        if (!customerName) {
            alert("Please enter the customer's name.");
            return;
        }

        if (!paymentMode || paymentMode === "Select Mode") {
            alert("Please select a mode of payment.");
            return;
        }

        // Build modal receipt content
        receiptHTML += `<p><strong>Receipt ID:</strong> ${receiptId}</p>`;
        receiptHTML += `<p><strong>Customer Name:</strong> ${customerName}</p>`;
        receiptHTML += `<hr>`;
        receiptHTML += `<table class="table">
            <thead>
            <tr>
                <th>Product</th>
                <th>Size</th>
                <th>Price</th>
                <th class="text-end">Quantity</th>
                <th class="text-end">Subtotal</th>
            </tr>
            </thead>
            <tbody>`;

        let computedTotal = 0;

        $('.product-item').each(function () {
            const productSelect = $(this).find('.product-select option:selected');
            const productName = productSelect.text();
            const size = $(this).find('select[name="size[]"]').val() || 'N/A';
            const price = parseFloat($(this).find('input[name="price[]"]').val());
            const quantity = parseInt($(this).find('input[name="quantity[]"]').val(), 10);
            const type = productSelect.data('type');

            if (!productName || isNaN(price) || isNaN(quantity) || quantity < 1) {
                isValid = false;
            }

            const subtotal = price * quantity;
            const priceDisplay = isNaN(price) ? '₱ 0.00' : `₱ ${price.toFixed(2)}`;
            const subtotalDisplay = isNaN(subtotal) ? '₱ 0.00' : `₱ ${subtotal.toFixed(2)}`;

            receiptHTML += `
            <tr>
                <td>${productName}</td>
                <td>${type === 'Supplies' ? 'N/A' : size}</td>
                <td class="text-end">${priceDisplay}</td>
                <td class="text-end">${quantity}</td>
                <td class="text-end">${subtotalDisplay}</td>
            </tr>`;

            if (!isNaN(price) && !isNaN(quantity)) {
                computedTotal += subtotal;
            }
        });

        receiptHTML += `</tbody></table>`;
        receiptHTML += `<hr>`;
        receiptHTML += `<div class="row">
            <div class="col-md-6">
                <p><strong>Mode of Payment:</strong> ${paymentMode}</p>
            </div>
            <div class="col-md-6 text-end">
                <h4><strong>Total Amount: ₱ ${computedTotal.toFixed(2)}</strong></h4>
            </div>
        </div>`;

        if (!isValid) {
            alert('Please complete all product and quantity fields.');
            return;
        }

        // Show in modal
        $('#receiptDetails').html(receiptHTML);
        $('#receiptModal').modal('show');
    });

    // Input change handlers for calculation
    $(document).on('change', 'input[name="price[]"], input[name="quantity[]"]', calculateTotal);
});
// Download Receipt as PDF
async function downloadReceipt() {
    const receiptId = $('#receiptDetails').find('p:first').text().split(':')[1].trim();
    const customerName = $('#customerName').val().trim();
    const paymentMethod = $('#paymentMode').val();
    const products = [];

    try {
        // Collect product data from the receipt table and the original form
        $('#receiptDetails table tbody tr').each(function(index) {
            const columns = $(this).find('td');
            const productId = $('.product-item').eq(index).find('.product-select').val();
            const productSelect = $('.product-item').eq(index).find('.product-select option:selected');
            const productName = productSelect.text();
            
            products.push({
                id: productId,
                name: productName,
                size: columns.eq(1).text(),
                price: parseFloat(columns.eq(2).text().replace('₱', '').trim()),
                quantity: parseInt(columns.eq(3).text()),
                subtotal: parseFloat(columns.eq(4).text().replace('₱', '').trim())
            });
        });

        // Create request payload with all required data
        const payload = {
            receipt_id: receiptId,
            customer_name: customerName,
            payment_method: paymentMethod,
            products: products
        };

        const response = await fetch('save_receipt.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.error || 'Failed to save receipt');
        }

        // Only convert the modal body to PDF, excluding modal-footer
        const modalBody = document.querySelector('#receiptModal .modal-body');
        // Clone modalBody and remove modal-footer if present
        const clone = modalBody.cloneNode(true);
        // Remove modal-footer if it exists in the clone (shouldn't, but for safety)
        const modalFooter = clone.querySelector('.modal-footer');
        if (modalFooter) {
            modalFooter.parentNode.removeChild(modalFooter);
        }

        const opt = {
            margin: 0.5,
            filename: `receipt-${receiptId}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        };

        await html2pdf().from(clone).set(opt).save();
        
        // Close modal and reset form
        $('#receiptModal').modal('hide');
        location.reload();

    } catch (error) {
        console.error('Error:', error);
        alert('Error: ' + error.message);
    }
}

function updateDateTime() {
    const now = new Date();
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        hour12: true
    };
    document.getElementById('currentDateTime').textContent = now.toLocaleString('en-US', options);
}

// Update immediately and then every second
updateDateTime();
setInterval(updateDateTime, 1000);
</script>
</body>
</html>

