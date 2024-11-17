<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (isset($data['productId']) && isset($data['quantity'])) {
        $productId = intval($data['productId']);
        $quantity = intval($data['quantity']);

        
        if ($quantity > 0) {
           $_SESSION['cart'][$productId] = $quantity; 
           echo json_encode(['message' => 'Cart updated successfully!']);
        } else {
           unset($_SESSION['cart'][$productId]); 
           echo json_encode(['message' => 'Item removed from cart!']);
        }
        
    } else {
      echo json_encode(['message' => 'Invalid request!']);
    }
}
?>