<?php
require 'include/db_connection.php';

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    
    $orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    
    if ($orderId === 0) {
        throw new Exception("Invalid order ID");
    }

    $orderQuery = "SELECT o.*, c.first_name, c.last_name, c.email 
                  FROM orders o 
                  JOIN customers c ON o.customer_id = c.customer_id 
                  WHERE o.id = ? AND o.customer_id = ?";
    $stmt = $conn->prepare($orderQuery);
    $customerId = $_SESSION['customers']['id'];
    $stmt->bind_param("ii", $orderId, $customerId);
    $stmt->execute();
    $orderResult = $stmt->get_result();
    $order = $orderResult->fetch_assoc();
    $stmt->close();

    if (!$order) {
        throw new Exception("Order not found");
    }

    
    $itemsQuery = "SELECT oi.*, p.name, p.image 
                   FROM order_items oi 
                   JOIN products p ON oi.product_id = p.id 
                   WHERE oi.order_id = ?";
    $stmt = $conn->prepare($itemsQuery);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $itemsResult = $stmt->get_result();
    $orderItems = $itemsResult->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

} catch (Exception $e) {
    error_log("Order confirmation error: " . $e->getMessage());
    header("Location: error.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - ModernCart</title>
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="assets/img/cart.jpg" rel="apple-touch-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/orderconfirmation.css"> 
</head>
<body>
<header class="header">
    <div class="header-content">
        <a href="/" class="logo">
            <i class="fas fa-shopping-bag"></i>
            ModernCart
        </a>
    </div>
</header>

<main class="confirmation-container">
    <div class="confirmation-box">
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <h1>Order Confirmed!</h1>
            <p>Thank you for your purchase. Your order has been successfully placed.</p>
        </div>
        <div class="order-details">
            <h2>Order Details</h2>
            <div class="detail-row">
                <span>Order Number:</span>
                <span>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div class="detail-row">
                <span>Order Date:</span>
                <span><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
            </div>
            <div class="detail-row">
                <span>Payment Method:</span>
                <span><?php echo ucfirst($order['payment_method']); ?></span>
            </div>
            <div class="detail-row">
                <span>Shipping Address:</span>
                <span><?php echo htmlspecialchars($order['shipping_address']); ?></span>
            </div>
            <div class="detail-row">
                <span>City:</span>
                <span><?php echo htmlspecialchars($order['city']); ?></span>
            </div>
            <div class="detail-row">
                <span>Phone:</span>
                <span><?php echo htmlspecialchars($order['phone']); ?></span>
            </div>
        </div>
        <div class="order-items">
            <h2>Order Items</h2>
            <?php foreach ($orderItems as $item): ?>
                <div class="item-card">
                    <img src="/admin/Products/<?php echo htmlspecialchars($item['image']); ?>" 
                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <div class="item-details">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p>Quantity: <?php echo $item['quantity']; ?></p>
                    </div>
                    <div class="item-price">
                        Ksh <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="total-amount">
                Total Amount: Ksh <?php echo number_format($order['total_amount'], 2); ?>
            </div>
        </div>

        <?php if ($order['payment_method'] === 'mpesa'): ?>
        <div class="payment-instructions">
            <h3>M-Pesa Payment Instructions</h3>
            <p>1. You will receive an M-Pesa prompt shortly on your registered phone number.</p>
            <p>2. Enter your M-Pesa PIN to complete the payment.</p>
            <p>3. You will receive a confirmation message once the payment is processed.</p>
            <button 
    style="background: none; 
           color: #FFA500; 
           border: none; 
           padding: 12px 24px; 
           text-decoration: none; 
           font-size: 18px; 
           cursor: pointer; 
           font-family: 'Arial', sans-serif; 
           border-radius: 8px; 
           transition: all 0.3s ease-in-out; 
           box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
           text-transform: uppercase; 
           letter-spacing: 1px; 
           display: inline-block;"
    onclick="window.location.href='mpesa.php';" 
    onmouseover="this.style.color='#ff7f00'; this.style.transform='scale(1.05)';" 
    onmouseout="this.style.color='#FFA500'; this.style.transform='scale(1)';"
>
    Make Payment
</button>
        </div>
        <?php endif; ?>

        <div class="no-print" style="text-align: center; margin-top: 30px;">
            <button onclick="window.print()" class="continue-shopping" style="margin-right: 10px;">
                <i class="fas fa-print"></i> Print Order
            </button>
            <a href="dashboard.php" class="continue-shopping">
                <i class="fas fa-shopping-cart"></i> Continue Shopping
            </a>
        </div>
    </div>
</main>

<script>

</script>

</body>
</html>

<?php
$conn->close();
?>