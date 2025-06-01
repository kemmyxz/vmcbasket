<?php
require 'inc/config.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['review_id'])) {
        throw new Exception('Review ID is required');
    }

    $review_id = mysqli_real_escape_string($conn, $_POST['review_id']);

    // Start transaction
    mysqli_begin_transaction($conn);

    // Delete review images
    $delete_images = "DELETE FROM review_images WHERE review_id = ?";
    $stmt = mysqli_prepare($conn, $delete_images);
    mysqli_stmt_bind_param($stmt, "i", $review_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error deleting review images: " . mysqli_error($conn));
    }

    // Delete the review
    $delete_review = "DELETE FROM product_reviews WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_review);
    mysqli_stmt_bind_param($stmt, "i", $review_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error deleting review: " . mysqli_error($conn));
    }

    // Commit transaction
    mysqli_commit($conn);
    echo json_encode(['status' => 'success']);

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn)) {
        mysqli_rollback($conn);
    }
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

// Close connection
mysqli_close($conn);