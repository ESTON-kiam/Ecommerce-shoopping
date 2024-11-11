<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashedPassword);
        $stmt->fetch();
        if (password_verify($password, $hashedPassword)) {
            $_SESSION['admin_id'] = $id;
            header("Location: Admindashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with that username.";
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
    <title>Admin Login</title>
    <style>
        /* Global Styling */
        body {
            font-family: 'Roboto', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #5cb85c, #2e8b57);
        }

        /* Container Styling */
        .login-container {
            background-color: #fff;
            padding: 30px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            text-align: center;
        }

        /* Header Styling */
        h2 {
            margin-bottom: 20px;
            color: #333;
            font-weight: 700;
        }

        /* Form and Input Styling */
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            display: none;
        }
        input[type="text"], input[type="password"] {
            padding: 12px;
            margin-bottom: 15px;
            font-size: 16px;
            border-radius: 6px;
            border: 1px solid #ccc;
            transition: 0.3s;
        }
        input:focus {
            border-color: #5cb85c;
            box-shadow: 0 0 8px rgba(92, 184, 92, 0.3);
        }

        /* Button Styling */
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

        /* Error Message Styling */
        .error {
            color: #ff4d4d;
            font-weight: 500;
            margin-bottom: 20px;
        }

        /* Register Link Styling */
        .register-link {
            display: block;
            margin-top: 15px;
            color: #5cb85c;
            text-decoration: none;
            transition: color 0.3s;
        }
        .register-link:hover {
            color: #4cae4c;
        }

        /* Media Query for Small Screens */
        @media (max-width: 400px) {
            .login-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="" method="POST" onsubmit="return validateForm()">
            <input type="text" id="username" name="username" placeholder="Username" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <a href="adminregistration.php" class="register-link">Register as Admin</a>
        </form>
    </div>

    <script>
        function validateForm() {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();

            if (username === "" || password === "") {
                alert("Both fields are required.");
                return false; // Prevent form submission
            }
            
            return true; // Allow form submission
        }
    </script>
</body>
</html>
