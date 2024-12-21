<?php
session_name('admin_session');
session_start();


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";


if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit();
}


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Invalid customer ID.";
    exit();
}

$customer_id = intval($_GET['id']);


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$stmt = $conn->prepare("
    SELECT 
        c.customer_id, 
        c.first_name, 
        c.last_name, 
        c.email, 
        c.phone, 
        c.created_at,
        c.address_line1,
        c.address_line2,
        c.city,
        c.state,
        c.postal_code,
        c.country,
        (SELECT COUNT(*) FROM orders WHERE customer_id = c.customer_id) as total_orders,
        (SELECT SUM(total_amount) FROM orders WHERE customer_id = c.customer_id) as total_spent
    FROM 
        customers c
    WHERE 
        c.customer_id = ?
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();


if ($result->num_rows === 0) {
    echo "Customer not found.";
    exit();
}

$customer = $result->fetch_assoc();


$orders_stmt = $conn->prepare("
    SELECT 
        id, 
        created_at, 
        total_amount, 
        status 
    FROM 
        orders 
    WHERE 
        customer_id = ? 
    ORDER BY 
        created_at DESC 
    LIMIT 5
");
$orders_stmt->bind_param("i", $customer_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Profile - Vendor Dashboard</title>
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admindash.css">
    <style>
        .profile-container {
            display: flex;
            gap: 20px;
            margin: 20px;
        }
        .profile-details, .orders-details {
            flex: 1;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }
        .profile-details table, .orders-details table {
            width: 100%;
        }
        .profile-details th, .orders-details th {
            text-align: left;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .profile-details td, .orders-details td {
            padding: 10px 0;
        }
        .order-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
        }
        .order-status.completed { background-color: #e6f3e6; color: green; }
        .order-status.pending { background-color: #fff5e6; color: orange; }
        .order-status.cancelled { background-color: #ffe6e6; color: red; }
    </style>
</head>
<body>
    <div class="container">
        <?php include('include/sidebar.php'); ?>

        <div class="main-content">
            <div class="profile-header">
                <h1>Customer Profile</h1>
                <div class="header-actions">
                    <a href="customers.php" class="btn btn-primary" style="text-decoration: none;">Back to Customers</a>
                    
                </div>
            </div>

            <div class="profile-container">
                <div class="profile-details">
                    <h2>Personal Information</h2>
                    <table>
                        <tr>
                            <th>Customer ID:</th>
                            <td><?php echo $customer['customer_id']; ?></td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                        </tr>
                        <tr>
                            <th>Registration Date:</th>
                            <td><?php echo date('F j, Y', strtotime($customer['created_at'])); ?></td>
                        </tr>
                    </table>

                    <h2>Address Information</h2>
                    <table>
                        <tr>
                            <th>Address:</th>
                            <td>
                                <?php 
                                echo htmlspecialchars($customer['address_line1']);
                                if (!empty($customer['address_line2'])) {
                                    echo ', ' . htmlspecialchars($customer['address_line2']);
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>City:</th>
                            <td><?php echo htmlspecialchars($customer['city']); ?></td>
                        </tr>
                        <tr>
                            <th>State/Province:</th>
                            <td><?php echo htmlspecialchars($customer['state']); ?></td>
                        </tr>
                        <tr>
                            <th>Postal Code:</th>
                            <td><?php echo htmlspecialchars($customer['postal_code']); ?></td>
                        </tr>
                        <tr>
                            <th>Country:</th>
                            <td><?php echo htmlspecialchars($customer['country']); ?></td>
                        </tr>
                    </table>
                </div>

                <div class="orders-details">
                    <h2>Order Summary</h2>
                    <table>
                        <tr>
                            <th>Total Orders:</th>
                            <td><?php echo $customer['total_orders']; ?></td>
                        </tr>
                        <tr>
                            <th>Total Spent:</th>
                            <td>Ksh<?php echo number_format($customer['total_spent'], 2); ?></td>
                        </tr>
                    </table>

                    <h2>Recent Orders</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($order = $orders_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $order['id']; ?></td>
                                <td><?php echo date('F j, Y', strtotime($order['created_at'])); ?></td>
                                <td>KSH<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="order-status <?php echo strtolower($order['status']); ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php if ($customer['total_orders'] > 5): ?>
                    <div class="view-all-orders">
                        <a href="orders.php?id=<?php echo $customer_id; ?>" class="btn btn-primary" style="text-decoration: none;">View All Orders</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php

$orders_stmt->close();
$conn->close();
?>