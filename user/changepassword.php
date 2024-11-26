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
    $error = '';
    $success = '';

    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $current_password = filter_input(INPUT_POST, 'current_password');
        $new_password = filter_input(INPUT_POST, 'new_password');
        $confirm_password = filter_input(INPUT_POST, 'confirm_password');

    
        $stmt = $conn->prepare("SELECT password FROM customers WHERE customer_id = ?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!password_verify($current_password, $user['password'])) {
            $error = "Current password is incorrect.";
        } elseif ($new_password !== $confirm_password) {
            $error = "New password and confirm password do not match.";
        } elseif (strlen($new_password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } else {
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE customers SET password = ? WHERE customer_id = ?");
            $stmt->bind_param("si", $hashed_password, $customer_id);

            if ($stmt->execute()) {
                $success = "Password changed successfully.";
            } else {
                $error = "Error changing password: " . $stmt->error;
            }
        }
    }
} catch (Exception $e) {
    $error = "An error occurred: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="assets/img/cart.jpg" rel="apple-touch-icon">
    <link rel="stylesheet" href="assets/css/profile.css">
</head>
<body>
    <div class="profile-container">
        <h2>Change Password</h2>

        <?php if ($error): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="success-message"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Current Password:</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-group">
                <label>New Password:</label>
                <input type="password" name="new_password" required>
            </div>
            <div class="form-group">
                <label>Confirm New Password:</label>
                <input type="password" name="confirm_password" required>
            </div>
            <div class="form-actions">
                <input type="submit" value="Change Password" class="btn-submit">
                <a href="view_profile.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>