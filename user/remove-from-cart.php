<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (isset($data['productId'])) {
        $productId = intval($data['productId']);

        
        unset($_SESSION['cart'][$productId]);
        
        echo json_encode(['message' => 'Item removed from cart!']);
        
    } else {
      echo json_encode(['message' => 'Invalid request!']);
    }
}
?>