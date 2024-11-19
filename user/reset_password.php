<?php
session_name('customer_session');
session_start();

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

        .reset-password-container {
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

        input[type="password"] {
            padding: 12px;
            margin-bottom: 15px;
            font-size: 16px;
            border-radius: 6px;
            border: 1px solid #ccc;
            transition: 0.3s;
        }

        input[type="password"]:focus {
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

        .login-link {
            display: block;
            margin-top: 15px;
            color: #5cb85c;
            text-decoration: none;
            transition: color 0.3s;
        }

        .login-link:hover {
            color: #4cae4c;
        }

        @media (max-width: 400px) {
            .reset-password-container {
                padding: 20px;
            }
        }
    </style>
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
