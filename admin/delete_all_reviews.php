<?php

require 'inc/config.php';

header('Content-Type: application/json');

try {
    // Start transaction
    mysqli_begin_transaction($conn);

    // Delete all review images first
    $delete_images = "DELETE FROM review_images";
    if (!mysqli_query($conn, $delete_images)) {
        throw new Exception("Error deleting review images: " . mysqli_error($conn));
    }

    // Delete all reviews
    $delete_reviews = "DELETE FROM product_reviews";
    if (!mysqli_query($conn, $delete_reviews)) {
        throw new Exception("Error deleting reviews: " . mysqli_error($conn));
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

mysqli_close($conn);