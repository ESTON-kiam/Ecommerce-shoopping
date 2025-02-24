<?php
require 'include/db_connection.php';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$messageType = "";
$validToken = false;
$token = "";

date_default_timezone_set('Africa/Nairobi'); 

if (isset($_GET['token'])) {
    $token = $_GET['token'];
     $stmt = $conn->prepare("SELECT customer_id, first_name, reset_token_expiry FROM customers WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        
        
        $currentTime = date('Y-m-d H:i:s');
        if ($customer['reset_token_expiry'] > $currentTime) {
            $validToken = true;
        } else {
            $message = "Invalid or expired reset link. Please request a new password reset.";
            $messageType = "error";
        }
    } else {
        $message = "Invalid or expired reset link. Please request a new password reset.";
        $messageType = "error";
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['token'])) {
    $token = $_POST['token'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if ($newPassword !== $confirmPassword) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } elseif (strlen($newPassword) < 8) {
        $message = "Password must be at least 8 characters long.";
        $messageType = "error";
    } else {
        
        $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE reset_token = ? AND reset_token_expiry > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $customer = $result->fetch_assoc();
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            
            $updateStmt = $conn->prepare("UPDATE customers SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE customer_id = ?");
            $updateStmt->bind_param("si", $hashedPassword, $customer['customer_id']);
            
            if ($updateStmt->execute()) {
                $message = "Password successfully reset. You can now login with your new password.";
                $messageType = "success";
                $validToken = false; 
            } else {
                $message = "Error updating password. Please try again.";
                $messageType = "error";
            }
            $updateStmt->close();
        } else {
            $message = "Invalid or expired reset link. Please request a new password reset.";
            $messageType = "error";
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="assets/img/cart.jpg" rel="apple-touch-icon">
   <link href="assets/css/reset.css" rel="stylesheet">
</head>
<body>
    <div class="reset-password-container">
        <h2>Reset Password</h2>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($validToken): ?>
            <p class="description">Please enter your new password below.</p>
            <form action="" method="POST" onsubmit="return validateForm()">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <input type="password" id="new_password" name="new_password" placeholder="New Password" required>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>
        
        <a href="index.php" class="login-link">Back to Login</a>
    </div>
    <script>
        function validateForm() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword.length < 8) {
                alert("Password must be at least 8 characters long.");
                return false;
            }
            
            if (newPassword !== confirmPassword) {
                alert("Passwords do not match.");
                return false;
            }  
            return true;
        }
    </script>
</body>
</html>