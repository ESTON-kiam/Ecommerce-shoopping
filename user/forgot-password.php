<?php
session_name('customer_session');
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

    $stmt = $conn->prepare("SELECT customer_id, first_name FROM customers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();

        
        $token = bin2hex(random_bytes(32));
        $tokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

        
        $updateStmt = $conn->prepare("UPDATE customers SET reset_token = ?, reset_token_expiry = ? WHERE customer_id = ?");
        $updateStmt->bind_param("ssi", $token, $tokenExpiry, $customer['customer_id']);

        if ($updateStmt->execute()) {
            
            $resetLink = "http://localhost:8000/user/reset_password.php?token=" . $token;

            
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
                    <p>Hello " . htmlspecialchars($customer['first_name']) . ",</p>
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
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #5cb85c, #2e8b57);
        }

        .forgot-password-container {
            background-color: #fff;
            padding: 30px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
            font-weight: 700;
        }

        .description {
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input[type="email"] {
            padding: 12px;
            margin-bottom: 15px;
            font-size: 16px;
            border-radius: 20px;
            border: 1px solid #ccc;
            transition: 0.3s;
        }

        input[type="email"]:focus {
            border-color: #5cb85c;
            box-shadow: 0 0 8px rgba(92, 184, 92, 0.3);
        }

        button {
            padding: 12px;
            font-size: 16px;
            font-weight: bold;
            color: #fff;
            background-color: #5cb85c;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #4cae4c;
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-weight: 500;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .back-link {
            display: block;
            margin-top: 15px;
            color: #5cb85c;
            text-decoration: none;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #4cae4c;
        }

        @media (max-width: 400px) {
            .forgot-password-container {
                padding: 20px;
            }
        }
    </style>
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