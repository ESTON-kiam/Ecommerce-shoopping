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

$order_id = "";
$customer_id = "";
$total_amount = "";
$shipping_address = "";
$city = "";
$phone = "";
$payment_method = "";
$payment_status = "";
$status = "";
$transaction_id = "";
$product_details = "";
$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_order"])) {
   
    $order_id = $_POST["order_id"];
    
    $customer_id = filter_var($_POST["customer_id"], FILTER_SANITIZE_NUMBER_INT);
    $total_amount = filter_var($_POST["total_amount"], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $shipping_address = htmlspecialchars($_POST["shipping_address"]);
    $city = htmlspecialchars($_POST["city"]);
    $phone = htmlspecialchars($_POST["phone"]);
    $payment_method = $_POST["payment_method"];
    $payment_status = $_POST["payment_status"];
    $status = $_POST["status"];
    $transaction_id = htmlspecialchars($_POST["transaction_id"]);
    $product_details = $_POST["product_details"];
    
    // Fix: Added 'i' at the end of type string to match the order_id parameter
    $sql1 = "UPDATE orders SET 
            customer_id = ?, 
            total_amount = ?, 
            shipping_address = ?, 
            city = ?, 
            phone = ?, 
            payment_method = ?, 
            payment_status = ?, 
            status = ?, 
            transaction_id = ?
            WHERE id = ?";
    
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param(
        "idsssssssi", 
        $customer_id, 
        $total_amount,
        $shipping_address,
        $city,
        $phone,
        $payment_method,
        $payment_status,
        $status,
        $transaction_id,
        $order_id  
    );
    
    if ($stmt1->execute()) {
        
        $sql2 = "UPDATE orders SET product_details = ? WHERE id = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("si", $product_details, $order_id);
        
        if ($stmt2->execute()) {
            $success_message = "Order #$order_id updated successfully!";
        } else {
            $error_message = "Error updating product details: " . $conn->error;
        }
        $stmt2->close();
    } else {
        $error_message = "Error updating order: " . $stmt1->error;
    }
    
    $stmt1->close();
    
} elseif (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    
    $order_id = $_GET["id"];
    
    $sql = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        $customer_id = $order["customer_id"];
        $total_amount = $order["total_amount"];
        $shipping_address = $order["shipping_address"];
        $city = $order["city"];
        $phone = $order["phone"];
        $payment_method = $order["payment_method"];
        $payment_status = $order["payment_status"];
        $status = $order["status"];
        $transaction_id = $order["transaction_id"];
        $product_details = $order["product_details"];
    } else {
        $error_message = "Order not found.";
    }
    
    $stmt->close();
} else {
    $error_message = "No order ID specified.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .admin-container {
            max-width: 1000px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .full-width {
            grid-column: span 2;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            grid-column: span 2;
        }
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }
        .btn-primary {
            background-color: #4CAF50;
            color: white;
        }
        .btn-secondary {
            background-color: #f1f1f1;
            color: #333;
        }
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1>Edit Order #<?php echo $order_id; ?></h1>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($order_id) && empty($error_message)): ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="customer_id">Customer ID:</label>
                        <input type="number" id="customer_id" name="customer_id" value="<?php echo $customer_id; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="total_amount">Total Amount:</label>
                        <input type="number" id="total_amount" name="total_amount" step="0.01" value="<?php echo $total_amount; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="shipping_address">Shipping Address:</label>
                        <input type="text" id="shipping_address" name="shipping_address" value="<?php echo $shipping_address; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="city">City:</label>
                        <input type="text" id="city" name="city" value="<?php echo $city; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone:</label>
                        <input type="text" id="phone" name="phone" value="<?php echo $phone; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="transaction_id">Transaction ID:</label>
                        <input type="text" id="transaction_id" name="transaction_id" value="<?php echo $transaction_id; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_method">Payment Method:</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="mpesa" <?php echo ($payment_method == 'mpesa') ? 'selected' : ''; ?>>M-Pesa</option>
                            <option value="card" <?php echo ($payment_method == 'card') ? 'selected' : ''; ?>>Card</option>
                            <option value="cash" <?php echo ($payment_method == 'cash') ? 'selected' : ''; ?>>Cash</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="payment_status">Payment Status:</label>
                        <select id="payment_status" name="payment_status" required>
                            <option value="pending" <?php echo ($payment_status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="paid" <?php echo ($payment_status == 'paid') ? 'selected' : ''; ?>>Paid</option>
                            <option value="failed" <?php echo ($payment_status == 'failed') ? 'selected' : ''; ?>>Failed</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Order Status:</label>
                        <select id="status" name="status" required>
                            <option value="pending" <?php echo ($status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo ($status == 'processing') ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo ($status == 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo ($status == 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo ($status == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="product_details">Product Details:</label>
                        <textarea id="product_details" name="product_details" rows="6"><?php echo htmlspecialchars($product_details); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="update_order" class="btn btn-primary">Update Order</button>
                        <a href="dashboard.php" class="btn btn-secondary">Dashboard</a>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <p>Return to <a href="orders.php">Orders List</a></p>
        <?php endif; ?>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('form').addEventListener('submit', function(e) {
            const phoneInput = document.getElementById('phone');
            const phonePattern = /^\d{10,15}$/;
            
            if (!phonePattern.test(phoneInput.value)) {
                alert('Please enter a valid phone number (10-15 digits)');
                e.preventDefault();
                return false;
            }
        });
    });
    </script>
</body>
</html>

<?php
$conn->close();
?>