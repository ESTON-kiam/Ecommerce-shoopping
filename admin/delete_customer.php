<?php

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Customer ID is required.");
}
$customer_id = $_GET['id'];


$host = 'localhost';
$db   = 'ecommerce';
$user = 'root';
$pass = '';

try {
    
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $stmt = $pdo->prepare("DELETE FROM customers WHERE customer_id = ?");
    $stmt->execute([$customer_id]);

    
    header("Location: customers.php?message=" . urlencode("Customer deleted successfully."));
    exit;
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>