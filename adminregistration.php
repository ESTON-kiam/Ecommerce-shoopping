<?php
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
                header("Location: adminlogin.php");
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
    <title>Admin Registration</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }

        h2 {
            text-align: center;
            color: #ffffff;
            font-size: 2em;
            margin-bottom: 20px;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
        }

        label {
            display: block;
            font-size: 1em;
            color: #555;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="password"],
        input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="email"]:focus {
            border-color: #6a11cb;
            box-shadow: 0px 4px 8px rgba(106, 17, 203, 0.3);
        }

        button {
            width: 100%;
            background-color: #6a11cb;
            color: #ffffff;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-weight: bold;
        }

        button:hover {
            background-color: #2575fc;
        }

        .error {
            color: red;
            font-size: 0.9em;
            margin-bottom: 10px;
            text-align: center;
        }

        @media (max-width: 500px) {
            .form-container {
                padding: 20px;
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Admin Registration</h2>
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
</body>
</html>
