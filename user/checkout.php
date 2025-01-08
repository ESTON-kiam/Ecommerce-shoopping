<?php
session_name('customer_session');
session_start([
  'cookie_lifetime' => 1800,
  'cookie_path' => '/',
  'cookie_secure' => false,
  'cookie_httponly' => true,
  'cookie_samesite' => 'Strict'
]);

if (!isset($_SESSION['customers'], $_SESSION['customers']['id'], $_SESSION['cart']) || empty($_SESSION['cart'])) {
  header("Location: cart.php");
  exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";

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
      $query = "SELECT id, name, price, stock_quantity FROM products WHERE id IN ($placeholders)";
      $stmt = $conn->prepare($query);

      $types = str_repeat('i', count($productIds));
      $stmt->bind_param($types, ...$productIds);
      $stmt->execute();

      $result = $stmt->get_result();
      while ($row = $result->fetch_assoc()) {
          $productId = $row['id'];
          $quantity = isset($cartItems[$productId]) ? max(1, intval($cartItems[$productId])) : 0;
          
          
          $itemPrice = floatval($row['price']);
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
              'price' => $itemPrice,
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
    <title>Checkout - ModernCart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        .checkout-form {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .order-summary {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: fit-content;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .error-message {
            color: #ff0000;
            margin-bottom: 15px;
        }
        .checkout-btn {
            background: #ff6600;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        .checkout-btn:hover {
            background: #ff4500;
        }
        .order-item {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .order-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            margin-right: 15px;
        }
        .total-price {
            font-size: 1.2em;
            font-weight: bold;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
    </style>
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
                    <p>Price: Ksh <?php echo number_format($product['price'] * $cartItems[$product['id']], 2); ?></p>
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