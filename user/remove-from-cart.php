<?php
session_name('customer_session');
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}


$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['productId'])) {
    echo json_encode(['success' => false, 'message' => 'Product ID is required']);
    exit;
}

$productId = (int)$data['productId'];


if (isset($_SESSION['cart'][$productId])) {
    unset($_SESSION['cart'][$productId]);
    
   
    $cartCount = count($_SESSION['cart']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Item removed from cart successfully',
        'cartCount' => $cartCount
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Item not found in cart'
    ]);
}
?>