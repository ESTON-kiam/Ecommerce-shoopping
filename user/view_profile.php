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

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    if (!isset($_SESSION['customers']['id'])) {
        header('Location: index.php');
        exit();
    }

    $customer_id = $_SESSION['customers']['id'];

    $stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        throw new Exception("User not found");
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="assets/img/cart.jpg" rel="apple-touch-icon">
    <link rel="stylesheet" href="assets/css/profile.css">
</head>
<body>
    <div class="profile-container">
        <h2>My Profile</h2>
        
        <?php if (isset($error)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php else: ?>
            <div class="profile-details">
                <div class="profile-section">
                    <h3>Personal Information</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></p>
                </div>

                <div class="profile-section">
                    <h3>Contact Information</h3>
                    <?php 
                    $address_parts = array_filter([
                        $user['address_line1'],
                        $user['address_line2'],
                        $user['city'],
                        $user['state'],
                        $user['postal_code'],
                        $user['country']
                    ]);
                    ?>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars(implode(', ', $address_parts) ?: 'No address provided'); ?></p>
                </div>
                <div class="profile-section">
                    <h3>Account Details</h3>
                    <p><strong>Account Created:</strong> <?php echo htmlspecialchars($user['created_at']); ?></p>
                </div>
                <div class="profile-actions">
                    <a href="edit_profile.php" class="btn-edit">Edit Profile</a>
                    <a href="changepassword.php" class="btn-change-password">Change Password</a>
                    <a href="dashboard.php" class="btn-edit">Continue Shopping</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>