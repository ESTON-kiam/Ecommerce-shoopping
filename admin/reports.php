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


$order_query = "SELECT orders.id, customers.first_name, customers.last_name, orders.created_at, orders.status, orders.total_amount 
                FROM orders
                JOIN customers ON orders.customer_id = customers.customer_id";
$order_result = $conn->query($order_query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Report - Admin Panel</title>
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
                <li><a href="addproducts.php"><i class="fas fa-box"></i> Add Products</a></li>
                <li><a href="productview.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1>Order Report</h1>

            <?php if ($order_result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Name</th>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = $order_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                        <td><?php echo htmlspecialchars($order['first_name']) . ' ' . htmlspecialchars($order['last_name']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        <td>
                            <span class="status <?php echo strtolower($order['status']); ?>"><?php echo htmlspecialchars(ucwords($order['status'])); ?></span>
                        </td>
                        <td>KSH<?php echo number_format($order['total_amount'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p>No orders found.</p>
            <?php endif; ?>

        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
