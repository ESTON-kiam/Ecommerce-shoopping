<?php
session_name('admin_session');
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";


try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    
    if (!isset($_SESSION['admin_id'])) {
        header('Location: index.php');
        exit();
    }
    
    
    if (isset($_GET['customer_id'])) {
        
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
    <title>View Customer - Vendor Panel</title>
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="assets/img/cart.jpg" rel="apple-touch-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admindash.css">
</head>
<body>
<?php include('include/sidebar.php'); ?>        
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