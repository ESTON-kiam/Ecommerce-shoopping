<?php
session_name('customer_session');
session_start([
    'cookie_lifetime' => 1800, 
    'cookie_path' => '/',
    'cookie_secure' => false, 
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
]);

function redirectToLogin() {
    session_unset();
    session_destroy();
    
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    header("Location: index.php");
    exit();
}
if (!isset($_SESSION['customers']) || 
    !isset($_SESSION['customers']['id']) || 
    empty($_SESSION['customers']['first_name']) || 
    empty($_SESSION['customers']['email'])) {
    redirectToLogin();
}

$isLoggedIn=true;
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";

    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
?>