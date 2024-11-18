<?php
session_name('customer_session');
session_start([
    'cookie_lifetime' => 1800, 
    'cookie_path' => '/',
    'cookie_secure' => false, 
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
]);

// Check if the customer is logged in
if (!isset($_SESSION['customers']) || !isset($_SESSION['customers']['customer_id'])) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit();
}

$customerId = $_SESSION['customers']['customer_id']; // Get the customer ID from the session

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare the SQL query to fetch customer data
$query = "SELECT * FROM customers WHERE customer_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $customerId); // Bind the customer ID parameter
$stmt->execute(); // Execute the query
$result = $stmt->get_result(); // Get the result of the query

// Check if the customer data was found
if ($result->num_rows === 1) {
    $customer = $result->fetch_assoc(); // Fetch the customer data as an associative array
} else {
    echo "No customer information found."; // Error message if no data found
    exit();
}

$stmt->close(); // Close the statement
$conn->close(); // Close the database connection

// You can now display the customer information as needed
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/account.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            background-color: #f9f9f9;
        }
        .account-container {
            max-width: 800px;
            margin: 50px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .account-header {
            text-align: center;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .account-header h1 {
            font-size: 28px;
            margin: 0;
            color: #333333;
        }
        .account-header p {
            color: #777777;
            font-size: 16px;
        }
        .account-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 20px 0;
        }
        .account-info .info-box {
            flex: 1 1 calc(50% - 20px);
            background: #f7f7f7;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .info-box h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #333333;
        }
        .info-box p {
            font-size: 16px;
            color: #555555;
        }
        .account-footer {
            text-align: center;
            margin-top: 20px;
        }
        .account-footer a {
            text-decoration: none;
            color: #ffffff;
            background-color: #ff6600;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        .account-footer a:hover {
            background-color: #ff4500;
        }
    </style>
</head>
<body>

<div class="account-container">
    <div class="account-header">
        <h1>Welcome, <?php echo htmlspecialchars($customer['first_name']); ?>!</h1>
        <p>Your account details are listed below.</p>
    </div>

    <div class="account-info">
        <div class="info-box">
            <h3>First Name</h3>
            <p><?php echo htmlspecialchars($customer['first_name']); ?></p>
        </div>
        <div class="info-box">
            <h3>Last Name</h3>
            <p><?php echo htmlspecialchars($customer['last_name']); ?></p>
        </div>
        <div class="info-box">
            <h3>Email</h3>
            <p><?php echo htmlspecialchars($customer['email']); ?></p>
        </div>
        <div class="info-box">
            <h3>Phone</h3>
            <p><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></p>
        </div>
        <div class="info-box">
            <h3>Address</h3>
            <p><?php echo htmlspecialchars($customer['address_line1']); ?></p>
            <p><?php echo htmlspecialchars($customer['address_line2'] ?? ''); ?></p>
            <p><?php echo htmlspecialchars($customer['city']); ?></p>
            <p><?php echo htmlspecialchars($customer['state'] ?? ''); ?></p>
            <p><?php echo htmlspecialchars($customer['postal_code'] ?? ''); ?></p>
            <p><?php echo htmlspecialchars($customer['country']); ?></p>
        </div>
        <div class="info-box">
            <h3>Account Created</h3>
            <p><?php echo date("F j, Y", strtotime($customer['created_at'])); ?></p>
        </div>
    </div>

    <div class="account-footer">
        <a href="editaccount.php">Edit Account</a>
    </div>
</div>

</body>
</html>
