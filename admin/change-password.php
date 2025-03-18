<?php
session_name('admin_session');
session_start();


if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$host = 'localhost';
$db   = 'ecommerce';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $old_password     = trim($_POST['old_password']);
    $new_password     = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    
    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $message = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $message = "New password and confirmation do not match.";
    } else {
        
        $admin_id = $_SESSION['admin_id'];
        $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            $message = "Admin not found.";
        } elseif (!password_verify($old_password, $admin['password'])) {
            $message = "Old password is incorrect.";
        } else {
          
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
           
            $updateStmt = $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?");
            if ($updateStmt->execute([$hashed_password, $admin_id])) {
                $message = "Password changed successfully.";
            } else {
                $message = "An error occurred. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="assets/img/cart.jpg" rel="apple-touch-icon">
    <title>Change Password -Vendor Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; }
        form { max-width: 400px; margin: 20px auto; }
        div { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 15px; }
        .message { text-align: center; color: red; }
    </style>
</head>
<body>
    <h2 style="text-align:center;">Change Password </h2>
    <?php if ($message): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form action="" method="post">
        <div>
            <label for="old_password">Old Password:</label>
            <input type="password" name="old_password" id="old_password" required>
        </div>
        <div>
            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" id="new_password" required>
        </div>
        <div>
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
        </div>
        <button type="submit">Change Password</button>
    </form>
    <form action="logout.php" method="post" style="text-align:center; margin-top:20px;">
    <button type="submit">Logout</button>
</form>
</body>
</html>