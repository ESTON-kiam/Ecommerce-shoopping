<?php
session_name('admin_session');
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/PHPMailer/src/Exception.php';
require 'PHPMailer/PHPMailer/src/PHPMailer.php';
require 'PHPMailer/PHPMailer/src/SMTP.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$messageType = "";
date_default_timezone_set('Africa/Nairobi'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT id, username FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        
        $token = bin2hex(random_bytes(32));
        $tokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $updateStmt = $conn->prepare("UPDATE admins SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
        $updateStmt->bind_param("ssi", $token, $tokenExpiry, $admin['id']);

        if ($updateStmt->execute()) {
            
            $resetLink = "http://localhost:8000/admin/reset_password.php?token=" . $token;
            
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'engestonbrandon@gmail.com'; 
                $mail->Password = 'dsth izzm npjl qebi';      
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('no-reply@gmail.com', 'ModernCart');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = "Password Reset Request";
                $mail->Body = "
                    <p>Hello " . htmlspecialchars($admin['username']) . ",</p>
                    <p>You requested a password reset. Click the link below to reset it:</p>
                    <p><a href='$resetLink'>$resetLink</a></p>
                    <p>This link will expire in 1 hour.</p>
                    <p>If you didn't request this, please ignore this email.</p>
                    <p>Best regards,<br>ModernCart Support Team</p>
                ";

                $mail->send();
                $message = "Password reset instructions have been sent to your email.";
                $messageType = "success";
            } catch (Exception $e) {
                $message = "Error sending email: " . $mail->ErrorInfo;
                $messageType = "error";
            }
        } else {
            $message = "Error processing request. Please try again.";
            $messageType = "error";
        }
        $updateStmt->close();
    } else {
        $message = "No account found with that email address.";
        $messageType = "error";
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="assets/img/cart.jpg" rel="apple-touch-icon">
    <link href="assets/css/forgotpassword.css" rel="stylesheet">
</head>
<body>
    <div class="forgot-password-container">
        <h2>Forgot Password</h2>
        <p class="description">Enter your email address below and we'll send you instructions to reset your password.</p>
        
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" onsubmit="return validateForm()">
            <input type="email" id="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Reset Password</button>
        </form>
        <a href="index.php" class="back-link">Back to Login</a>
    </div>

    <script>
        function validateForm() {
            const email = document.getElementById('email').value.trim();
            if (email === "") {
                alert("Email is required.");
                return false;
            }
            
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                alert("Please enter a valid email address.");
                return false;
            }
            
            return true;
        }
    </script>
</body>
</html>