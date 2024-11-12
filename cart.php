<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';


$query = "SELECT id, category, name, description, price, image FROM products WHERE 1=1";


if ($search) {
    $search = $conn->real_escape_string($search);
    $query .= " AND (name LIKE '%$search%' OR description LIKE '%$search%' OR category LIKE '%$search%')";
}

$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$productIds = array_keys($cartItems);

if (!empty($productIds)) {
   
    $productIds = array_filter($productIds, 'is_int');
    
   
    if (!empty($productIds)) {
        $productQuery = "SELECT id, name, price, image FROM products WHERE id IN (" . implode(",", $productIds) . ")";
        $productResult = $conn->query($productQuery);
        
       
        if ($productResult->num_rows > 0) {
            $products = $productResult->fetch_all(MYSQLI_ASSOC);
        } else {
            $products = [];
        }
    } else {
        $products = [];
    }
} else {
    $products = [];
}

$totalPrice = 0;
foreach ($products as $product) {
    $totalPrice += $product['price'] * $cartItems[$product['id']];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - ModernCart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/cart.css">
</head>
<body>

<header class="header">
    <div class="header-content">
        <a href="/" class="logo">
            <i class="fas fa-shopping-bag"></i>
            ModernCart
        </a>
        <form class="search-bar" method="GET">
            <input type="text" name="search" placeholder="Search products..." value="" />
        </form>
        <div class="header-icons">
            <a href="account.php">
                <i class="fas fa-user"></i>
                <span>Account</span>
            </a>
            <a href="cart.php">
                <i class="fas fa-shopping-cart"></i>
                <span>Cart (<?php echo count($_SESSION['cart']); ?>)</span>
            </a>
        </div>
    </div>
</header>

<main class="container">
    <h1>Your Cart</h1>
    <?php if (count($cartItems) > 0): ?>
    <div class="cart-items">
       <?php foreach ($products as $product): ?>
       <div class="cart-item" data-product-id="<?php echo $product['id']; ?>">
           <div class="product-image">
               <img src="Products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy">
           </div>
           <div class="product-details">
               <h2 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h2>
               <p class="product-price">Price Ksh <?php echo number_format($product['price'], 2); ?></p>
               <div class="product-quantity">
                   <button class="quantity-btn" onclick="updateQuantity(<?php echo $product['id']; ?>, -1)">-</button>
                   <input type="number" value="<?php echo $cartItems[$product['id']]; ?>" min="1" class="quantity-input" id="quantity-<?php echo $product['id']; ?>">
                   <button class="quantity-btn" onclick="updateQuantity(<?php echo $product['id']; ?>, +1)">+</button>
               </div>
               <button class="remove-from-cart" onclick="removeFromCart(<?php echo $product['id']; ?>)">Remove</button>
           </div>
       </div>
       <?php endforeach; ?>
    </div>

    <div class="cart-summary">
       <h3>Total Price:</h3>
       <p id="total-price">Ksh <?php echo number_format($totalPrice, 2); ?></p>
       <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
   </div>

   <?php else : ?>
   <p>Your cart is empty. Start shopping now!</p>
   <?php endif; ?>
</main>

<div id="toast" class="toast"></div>

<script>
// Show toast notification
function showToast(message) {
   const toast = document.getElementById('toast');
   toast.textContent = message;
   toast.classList.add('show');
   setTimeout(() => { toast.classList.remove('show'); }, 3000);
}

// Update quantity function
function updateQuantity(productId, change) {
   const quantityInput = document.getElementById(`quantity-${productId}`);
   let newQuantity = parseInt(quantityInput.value) + change;

   if (newQuantity <= 0) return;

   quantityInput.value = newQuantity;

   // Update total price
   updateTotalPrice();

   // Send AJAX request to update session
   fetch('update-cart.php', {
       method: 'POST',
       headers: { 'Content-Type': 'application/json' },
       body: JSON.stringify({ productId, quantity: newQuantity })
   })
   .then(response => response.json())
   .then(data => showToast(data.message))
   .catch(error => console.error('Error:', error));
}

// Remove from cart function
function removeFromCart(productId) {
   const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
   if (cartItem) {
       cartItem.remove();
       showToast('Item removed from cart!');

       fetch('remove-from-cart.php', {
           method: 'POST',
           headers: { 'Content-Type': 'application/json' },
           body: JSON.stringify({ productId })
       })
       .then(response => response.json())
       .then(data => updateTotalPrice())
       .catch(error => console.error('Error:', error));
   }
}


function updateTotalPrice() {
   let totalPrice = 0;

   document.querySelectorAll('.cart-item').forEach(item => {
       const productId = item.dataset.productId;
       const price = parseFloat(item.querySelector('.product-price').textContent.replace(/[^0-9.-]+/g,""));
       const quantity = parseInt(item.querySelector('.quantity-input').value);
       totalPrice += price * quantity;
   });

   document.getElementById('total-price').textContent = `Ksh ${totalPrice.toFixed(2)}`;
}
</script>

</body>
</html>

<?php
$conn->close();
?>