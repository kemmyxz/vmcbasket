<?php
session_start();
require 'admin/inc/config.php';
header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(['success'=>false,'error'=>'Not logged in']);
    exit;
}

// Fetch complete user details including email and phone
$stmtFetchUser = $conn->prepare("
    SELECT student_fname, 
           student_lname,
           student_no,
           email,
           phone_number 
    FROM users 
    WHERE id = ?
");
$stmtFetchUser->bind_param('i', $user_id);
$stmtFetchUser->execute();

$user = $stmtFetchUser->get_result()->fetch_assoc();
if (!$user) {
    echo json_encode(['success' => false, 'error' => 'User details not found']);
    exit;
}

// Prepare full name and other user details
$student_name = $user['student_fname'] . ' ' . $user['student_lname'];
$student_no = $user['student_no'];
$email = $user['email'];
$phone = $user['phone_number'];

$ids = $_POST['basket_ids'] ?? [];
$sizes = $_POST['size'] ?? [];
$quantities = $_POST['quantity'] ?? [];

if (empty($ids)) {
    echo json_encode(['success'=>false,'error'=>'No items selected']);
    exit;
}

$stmtFetch = $conn->prepare("
    SELECT b.product_id,
           b.size AS basket_size,
           b.quantity AS basket_qty,
           p.product_name,
           p.price AS unit_price,
           p.image
    FROM basket b
    JOIN products p ON p.id = b.product_id
    WHERE b.id = ? AND b.user_id = ?
");

$stmtInsert = $conn->prepare("
    INSERT INTO orders (
        product_name,
        quantity,
        price,
        user_id,
        product_id,
        size,
        total_price,
        image,
        customer_name,
        school_id,
        email,
        phone,
        status,
        payment_method
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,'Pending','Cash (Pay at the Counter)')
");

foreach ($ids as $bid) {
    $bid = (int)$bid;
    if (!isset($sizes[$bid], $quantities[$bid])) {
        continue;
    }

    $stmtFetch->bind_param('ii', $bid, $user_id);
    $stmtFetch->execute();
    $row = $stmtFetch->get_result()->fetch_assoc();
    if (!$row) {
        continue;
    }

    $qty = (int)$quantities[$bid];
    $unit_price = (float)$row['unit_price'];
    $total = $unit_price * $qty;

    $stmtInsert->bind_param(
        'sdiissdsssss',
        $row['product_name'],  // s (product_name)
        $qty,                  // d (quantity)
        $unit_price,           // i (price)
        $user_id,              // i (user_id)
        $row['product_id'],    // s (product_id)
        $sizes[$bid],          // s (size)
        $total,                // d (total_price)
        $row['image'],         // s (image)
        $student_name,         // s (customer_name)
        $student_no,           // s (school_id)
        $email,                // s (email)
        $phone                 // s (phone)
    );
    
    if (!$stmtInsert->execute()) {
        echo json_encode(['success' => false, 'error' => 'Failed to insert order: ' . $stmtInsert->error]);
        exit;
    }
}

// Remove items from basket after successful order
$in = implode(',', array_map('intval', $ids));
$conn->query("DELETE FROM basket WHERE id IN ($in) AND user_id = $user_id");

echo json_encode(['success' => true]);