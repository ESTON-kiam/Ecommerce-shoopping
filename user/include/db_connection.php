<?php
session_name('customer_session');
session_start([
    'cookie_lifetime' => 1800,
    'cookie_path' => '/',
    'cookie_secure' => false,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
]);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";


    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    if (!isset($_SESSION['customers']['id'])) {
        header('Location: index.php');
        exit();
    }

    ?>