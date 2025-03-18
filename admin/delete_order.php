<?php

if (!isset($_GET['id'])) {
    die("Order ID is required.");
}

$order_id = $_GET['id'];

$host = 'localhost';
$db   = 'ecommerce';
$user = 'root';
$pass = '';

try {
    
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    if ($stmt->execute([$order_id])) {
        
        header("Location: dashboard.php?message=" . urlencode("Order deleted successfully."));
        exit;
    } else {
        echo "Error: Order could not be deleted.";
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>