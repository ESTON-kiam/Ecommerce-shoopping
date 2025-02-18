<?php
session_name('admin_session');
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $reenterpassword = $_POST['reenterpassword'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    if (empty($username) || empty($password) || empty($reenterpassword) || empty($phone) || empty($email)) {
        $error = "All fields are required.";
    } elseif ($password !== $reenterpassword) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already exists. Please use a different email.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt->close();

            $stmt = $conn->prepare("INSERT INTO admins (username, password, phone, email) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $hashedPassword, $phone, $email);

            if ($stmt->execute()) {
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Registration failed: " . $stmt->error;
            }
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
    <title>Vendor Registration</title>
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="assets/img/cart.jpg" rel="apple-touch-icon">
    <link rel="stylesheet" href="assets/css/adminreg.css">
</head>
<body>

    <div class="form-container">
        <h2>Vendor Registration</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="" method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <label for="reenterpassword">Re-enter Password</label>
            <input type="password" id="reenterpassword" name="reenterpassword" required>

            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <button type="submit">Register</button>
        </form>
    </div>
    <script>
document.addEventListener("contextmenu", function (e) {
  e.preventDefault(); 
});

document.addEventListener("keydown", function (e) {
  if (e.ctrlKey && (e.key === "u" || e.key === "U")) {
    e.preventDefault(); 
  }
  if (e.key === "F12") {
    e.preventDefault();
  }
  if (e.ctrlKey && e.shiftKey && (e.key === "I" || e.key === "J")) {
    e.preventDefault(); 
  }
});
</script>
</body>
</html>