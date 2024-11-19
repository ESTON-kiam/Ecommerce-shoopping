<?php
session_name('admin_session');
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";

// Create connection using try-catch for better error handling
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Check admin session
    if (!isset($_SESSION['admin_id'])) {
        header('Location: index.php');
        exit();
    }
    
    // Validate and get customer data
    if (isset($_GET['customer_id'])) {
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $_GET['customer_id']);
        $stmt->execute();
        $customer_result = $stmt->get_result();
        
        if ($customer_result->num_rows > 0) {
            $customer = $customer_result->fetch_assoc();
        } else {
            throw new Exception("Customer not found.");
        }
        
        $stmt->close();
    } else {
        throw new Exception("No customer ID provided.");
    }
    
} catch (Exception $e) {
    
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: customers.php');
    exit();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Customer - Admin Panel</title>
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="assets/img/cart.jpg" rel="apple-touch-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admindash.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="addproducts.php"><i class="fas fa-box"></i>Add Products</a></li>
                <li><a href="productview.php"><i class="fas fa-box"></i>Products</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        
        <div class="main-content">
            <h1>Customer Details</h1>
            
            <?php if (isset($customer)): ?>
                <div class="customer-details">
                    <table>
                        <tr>
                            <th>First Name</th>
                            <td><?php echo htmlspecialchars($customer['first_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Last Name</th>
                            <td><?php echo htmlspecialchars($customer['last_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Phone Number</th>
                            <td><?php echo htmlspecialchars($customer['phone_number']); ?></td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td><?php echo htmlspecialchars($customer['address']); ?></td>
                        </tr>
                        <tr>
                            <th>Registration Date</th>
                            <td><?php echo date('M d, Y', strtotime($customer['registration_date'])); ?></td>
                        </tr>
                    </table>
                </div>
            <?php endif; ?>
            
            <a href="customers.php" class="btn btn-primary">Back to Customers</a>
        </div>
    </div>
</body>
</html>