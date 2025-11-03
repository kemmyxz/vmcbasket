<?php
require 'inc/config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        if (empty($_POST['product_id']) || empty($_POST['dr_number']) || empty($_POST['updated_by'])) {
            throw new Exception('Missing required fields');
        }

        $product_id = $_POST['product_id'];
        $dr_number = $_POST['dr_number'];
        $updated_by = $_POST['updated_by'];
        $product_type = $_POST['product_type'];
        
        $conn->begin_transaction();
        
        // Update DR number and modification date
        $stmt = $conn->prepare("UPDATE products SET dr_number = ?, date_modified = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("si", $dr_number, $product_id);
        $stmt->execute();
        
        if ($product_type === 'Uniform') {
            // Validate variant stock data
            if (empty($_POST['variant_stock']) || !is_array($_POST['variant_stock'])) {
                throw new Exception('Invalid variant stock data');
            }

            // Handle uniform variants
            foreach ($_POST['variant_stock'] as $size => $genders) {
                foreach ($genders as $gender => $add_stock) {
                    $add_stock = (int)$add_stock;
                    if ($add_stock > 0) {
                        // Update specific variant stock
                        $stmt = $conn->prepare("
                            UPDATE product_variants 
                            SET stock = stock + ?
                            WHERE product_id = ? 
                            AND size = ? 
                            AND gender = ?
                        ");
                        $stmt->bind_param("iiss", $add_stock, $product_id, $size, $gender);
                        if (!$stmt->execute()) {
                            throw new Exception('Failed to update variant stock');
                        }
                        
                        // Record in restock history with variant details
                        $stmt = $conn->prepare("
                            INSERT INTO restock_history 
                            (product_id, dr_number, added_stock, updated_by, variant_details) 
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        $variant_details = "Size: $size, Gender: $gender";
                        $stmt->bind_param("isiss", $product_id, $dr_number, $add_stock, $updated_by, $variant_details);
                        if (!$stmt->execute()) {
                            throw new Exception('Failed to record restock history');
                        }
                    }
                }
            }
        } else {
            // Handle supplies (single stock)
            if (empty($_POST['add_stock'])) {
                throw new Exception('Stock quantity is required');
            }
            
            $add_stock = (int)$_POST['add_stock'];
            if ($add_stock <= 0) {
                throw new Exception('Invalid stock quantity');
            }
            
            // Update stock for supplies
            $stmt = $conn->prepare("
                UPDATE product_variants 
                SET stock = stock + ?
                WHERE product_id = ? 
                LIMIT 1
            ");
            $stmt->bind_param("ii", $add_stock, $product_id);
            if (!$stmt->execute()) {
                throw new Exception('Failed to update stock');
            }
            
            // Record in restock history
            $stmt = $conn->prepare("
                INSERT INTO restock_history 
                (product_id, dr_number, added_stock, updated_by) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("isis", $product_id, $dr_number, $add_stock, $updated_by);
            if (!$stmt->execute()) {
                throw new Exception('Failed to record restock history');
            }
        }
        
        $conn->commit();
        // Show a Bootstrap 5.3.3 styled alert and automatically navigate back to the previous page
        echo '<!doctype html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <title>Restock</title>
        </head>
        <body>
            <div class="container py-5">
            <div class="alert alert-success" role="alert">
                Stock updated successfully! Redirecting...
            </div>
            </div>

            <script>
            // After a short delay, go back to the previous page (or history.back if no referrer)
            setTimeout(function() {
                if (document.referrer && document.referrer !== window.location.href) {
                window.location.href = document.referrer;
                } else {
                window.history.back();
                }
            }, 1400); // 1.4 seconds to allow user to see the alert briefly
            </script>
        </body>
        </html>';
    } catch (Exception $e) {
        $conn->rollback();
        echo "<script>
            alert('Error: " . $e->getMessage() . "');
            window.location.href='prod.php';
        </script>";
    }
    exit;
}

// For invalid request method
echo "<script>
    alert('Invalid request method');
    window.location.href='prod.php';
</script>";