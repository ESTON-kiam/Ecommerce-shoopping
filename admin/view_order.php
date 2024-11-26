<?php
session_name('admin_session');
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";

function requireAdminLogin() {
    if (!isset($_SESSION['admin_id'])) {
        $_SESSION['error_message'] = "Please log in to access this page.";
        header('Location: login.php');
        exit();
    }
}

function logError($message) {
    error_log($message, 3, 'admin_errors.log');
}

function sanitizeInput($input) {
    return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
}

try {
    requireAdminLogin();

    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $order_id = isset($_GET['id']) ? sanitizeInput($_GET['id']) : 0;

    if ($order_id <= 0) {
        throw new Exception("Invalid Order ID");
    }

    $order_stmt = $pdo->prepare("
        SELECT 
            o.*, 
            c.first_name, 
            c.last_name, 
            c.email, 
            c.phone
        FROM 
            orders o
        JOIN 
            customers c ON o.customer_id = c.customer_id
        WHERE 
            o.id = :order_id
    ");
    $order_stmt->execute(['order_id' => $order_id]);
    $order = $order_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Order not found");
    }

   
    $product_details = json_decode($order['product_details'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error decoding product details");
    }

    
    $product_ids = array_column($product_details, 'id');
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    $product_stmt = $pdo->prepare("SELECT id, image FROM products WHERE id IN ($placeholders)");
    $product_stmt->execute($product_ids);
    $product_images = $product_stmt->fetchAll(PDO::FETCH_KEY_PAIR);

} catch (Exception $e) {
    logError($e->getMessage());
    die("An error occurred: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?> Details</title>
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="assets/img/cart.jpg" rel="apple-touch-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .order-status {
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 10px;
            border-radius: 4px;
        }
        .status-pending { background-color: #ffc107; color: #000; }
        .status-processing { background-color: #17a2b8; color: #fff; }
        .status-completed { background-color: #28a745; color: #fff; }
        .status-cancelled { background-color: #dc3545; color: #fff; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h1>Order #<?php echo $order_id; ?></h1>
                <span class="order-status status-<?php echo strtolower($order['status']); ?>">
                    <?php echo $order['status']; ?>
                </span>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h3>Customer Information</h3>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                        <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h3>Order Details</h3>
                        <p><strong>Order Date:</strong> <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></p>
                        <p><strong>Total Amount:</strong> KSH <?php echo number_format($order['total_amount'], 2); ?></p>
                    </div>
                </div>

                <h3 class="mt-4">Order Items</h3>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($product_details)): ?>
                                <?php foreach($product_details as $item): ?>
                                <tr>
                                    <td>
                                        <img src="Products/<?php echo htmlspecialchars($product_images[$item['id']] ?? 'default.jpg'); ?>" 
                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                             style="max-width: 50px; max-height: 50px;">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                    <td>KSH <?php echo number_format($item['price'], 2); ?></td>
                                    <td>KSH <?php echo number_format($item['item_total'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center">No items found for this order.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>