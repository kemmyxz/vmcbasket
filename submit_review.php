<?php
session_start();
require 'admin/inc/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    // Validate required fields
    $required_fields = ['product_id', 'order_id', 'rating', 'review_text'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }

    $product_id = (int)$_POST['product_id'];
    $order_id = (int)$_POST['order_id'];
    $user_id = (int)$_SESSION['user_id'];
    $rating = (int)$_POST['rating'];
    $review_text = trim($_POST['review_text']);
    $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;

    // Validate rating
    if ($rating < 1 || $rating > 5) {
        throw new Exception('Invalid rating value');
    }

    $conn->begin_transaction();

    // Insert review
    $sql = "INSERT INTO product_reviews (product_id, order_id, user_id, rating, review_text, is_anonymous) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiisi", $product_id, $order_id, $user_id, $rating, $review_text, $is_anonymous);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to insert review');
    }
    
    $review_id = $conn->insert_id;

    // Handle image uploads
    if (isset($_FILES['review_images']) && !empty($_FILES['review_images']['name'][0])) {
        $upload_dir = "admin/uploads/reviews/";
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        foreach ($_FILES['review_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['review_images']['error'][$key] === 0) {
                $filename = uniqid() . '_' . $_FILES['review_images']['name'][$key];
                $filepath = $upload_dir . $filename;
                
                if (move_uploaded_file($tmp_name, $filepath)) {
                    $sql = "INSERT INTO review_images (review_id, image_path) VALUES (?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("is", $review_id, $filepath);
                    
                    if (!$stmt->execute()) {
                        throw new Exception('Failed to save image record');
                    }
                }
            }
        }
    }

    // Update product average rating
    $rating_sql = "SELECT AVG(rating) as avg_rating FROM product_reviews WHERE product_id = ?";
    $stmt = $conn->prepare($rating_sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $avg_rating = $result->fetch_assoc()['avg_rating'];

    $update_sql = "UPDATE products SET rating = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("di", $avg_rating, $product_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update product rating');
    }

    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>