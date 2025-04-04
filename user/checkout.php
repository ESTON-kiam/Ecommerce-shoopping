<?php
require 'include/db_connection.php';

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    $customerId = $_SESSION['customers']['id'];
    $stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
    $stmt->bind_param("i", $customerId);
    $stmt->execute();
    $customer = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $cartItems = $_SESSION['cart'];
    $productIds = array_keys($cartItems);
    $totalPrice = 0.00;
    $productDetails = [];

    if (!empty($productIds)) {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        
        
        $query = "SELECT id, name, price, stock_quantity, percentage_discount 
                  FROM products WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($query);

        $types = str_repeat('i', count($productIds));
        $stmt->bind_param($types, ...$productIds);
        $stmt->execute();

        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $productId = $row['id'];
            $quantity = isset($cartItems[$productId]) ? max(1, intval($cartItems[$productId])) : 0;

           
            $originalPrice = floatval($row['price']);
            $discount = floatval($row['percentage_discount']);
            $discountedPrice = $originalPrice - ($originalPrice * $discount / 100);

           
            $itemPrice = ($discount > 0) ? $discountedPrice : $originalPrice;
            $itemTotal = round($itemPrice * $quantity, 2);
            $totalPrice += $itemTotal;

          
            $image_stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
            $image_stmt->bind_param("i", $productId);
            $image_stmt->execute();
            $image_result = $image_stmt->get_result();
            $imageData = $image_result->fetch_assoc();
            $image_stmt->close();

            $productDetails[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'price' => $originalPrice,
                'discounted_price' => $discountedPrice,
                'discount' => $discount,
                'quantity' => $quantity,
                'item_total' => $itemTotal,
                'stock_quantity' => $row['stock_quantity'],
                'image' => $imageData ? $imageData['image'] : 'default.jpg'
            ];
        }
        $stmt->close();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $errors = [];
        $requiredFields = ['shipping_address', 'city', 'phone', 'payment_method'];

        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }

        if (empty($errors)) {
            $conn->begin_transaction();
            try {
              
                $productDetailsJson = json_encode($productDetails);

               
                $stmt = $conn->prepare(
                    "INSERT INTO orders (
                        customer_id, 
                        total_amount, 
                        shipping_address, 
                        city, 
                        phone, 
                        payment_method, 
                        status, 
                        product_details
                    ) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)"
                );

                
                $stmt->bind_param(
                    "dssssss", 
                    $customerId, 
                    $totalPrice, 
                    $_POST['shipping_address'], 
                    $_POST['city'], 
                    $_POST['phone'], 
                    $_POST['payment_method'], 
                    $productDetailsJson
                );

                $stmt->execute();
                $orderId = $conn->insert_id;
                $stmt->close();
            
               
                foreach ($productDetails as $product) {
                    $newStockQuantity = $product['stock_quantity'] - $product['quantity'];
                    $stmt = $conn->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
                    $stmt->bind_param("ii", $newStockQuantity, $product['id']);
                    $stmt->execute();
                    $stmt->close();
                }

                $conn->commit();
                $_SESSION['cart'] = [];
                $_SESSION['order_success'] = true;

                header("Location: order-confirmation.php?order_id=" . $orderId);
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                $errors[] = "An error occurred while processing your order. Please try again.";
                error_log("Checkout error: " . $e->getMessage());
            }
        }
    }
} catch (Exception $e) {
    error_log("Checkout error: " . $e->getMessage());
    header("Location: error.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - MyDuka</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
</head>
<body>
<header class="header">
    <div class="header-content">
        <a href="/" class="logo">
            <i class="fas fa-shopping-bag"></i>
            MyDuka
        </a>
    </div>
</header>
<main class="checkout-container">
    <div class="checkout-form">
        <h1>Checkout</h1>
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="shipping_address">Shipping Address</label>
                <input type="text" id="shipping_address" name="shipping_address" 
                       value="<?php echo isset($_POST['shipping_address']) ? htmlspecialchars($_POST['shipping_address']) : htmlspecialchars($customer['address_line1']); ?>" required>
            </div>

            <div class="form-group">
                <label for="city">City</label>
                <input type="text" id="city" name="city" 
                       value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : htmlspecialchars($customer['city']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" 
                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : htmlspecialchars($customer['phone']); ?>" required>
            </div>

            <div class="form-group">
                <label for="payment_method">Payment Method</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="">Select Payment Method</option>
                    <option value="mpesa">M-Pesa</option>
                    <option value="card">Credit/Debit Card</option>
                    <option value="cash">Cash on Delivery</option>
                </select>
            </div>

            <button type="submit" class="checkout-btn">Place Order</button>
        </form>
    </div>

    <div class="order-summary">
        <h2>Order Summary</h2>
        <?php foreach ($productDetails as $product): ?>
            <div class="order-item">
                <img src="/admin/Products/<?php echo htmlspecialchars($product['image']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>">
                <div>
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p>Quantity: <?php echo $cartItems[$product['id']]; ?></p>
                    <?php if ($product['discount'] > 0): ?>
                        <p>
                            <span class="price-original">Ksh <?php echo number_format($product['price'], 2); ?></span>
                            <span class="price-discounted">Ksh <?php echo number_format($product['discounted_price'], 2); ?></span>
                        </p>
                    <?php else: ?>
                        <p>Price: Ksh <?php echo number_format($product['price'], 2); ?></p>
                    <?php endif; ?>
                    <p>Total: Ksh <?php echo number_format($product['item_total'], 2); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
        
        <div class="total-price">
            Total: Ksh <?php echo number_format($totalPrice, 2); ?>
        </div>
    </div>
</main>

<script>
document.getElementById('payment_method').addEventListener('change', function() {
    if (this.value === 'mpesa') {
        alert('You will receive an M-Pesa prompt to complete the payment after placing the order.');
    }
});
</script>

</body>
</html>
<?php
$conn->close();
?>