
<?php
session_start();
header('Content-Type: application/json');

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['payment_method'])) {
        throw new Exception('Payment method not specified');
    }

    $_SESSION['payment_method'] = $data['payment_method'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment method saved'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}