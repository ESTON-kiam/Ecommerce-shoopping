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

// Get cart items
$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();

// Fetch product details
$productIds = array_keys($cartItems);
$productQuery = "SELECT id, name, price, image FROM products WHERE id IN (" . implode(",", $productIds) . ")";
$productResult = $conn->query($productQuery);
$products = $productResult->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - ModernCart</title>
    <meta name="description" content="View and manage items in your shopping cart">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>

<header class="header">
    <div class="header-content">
        <a href="/" class="logo">
            <i class="fas fa-shopping-bag"></i>
            ModernCart
        </a>
        <form class="search-bar" method="GET" action="index.php">
            <input type="text" name="search" placeholder="Search products..." />
        </form>
        <div class="header-icons">
            <a href="account.php">
                <i class="fas fa-user"></i>
                <span>Account</span>
            </a>
            <a href="cart.php">
                <i class="fas fa-shopping-cart"></i>
                <span>Cart (<?php echo count($cartItems); ?>)</span>
            </a>
        </div>
    </div>
</header>

<main class="container">
    <h1>Your Cart</h1>

    <?php if (count($cartItems) > 0): ?>
        <div class="cart-items">
            <?php foreach ($products as $product): ?>
                <div class="cart-item">
                    <div class="product-image">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>"
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             loading="lazy">
                    </div>
                    <div class="product-details">
                        <h2 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h2>
                        <div class="product-price">Ksh <?php echo number_format($product['price'], 2); ?></div>
                        <div class="product-quantity">
                            <button class="quantity-btn" onclick="updateCartQuantity(<?php echo $product['id']; ?>, -1)">-</button>
                            <span class="quantity"><?php echo $cartItems[$product['id']]; ?></span>
                            <button class="quantity-btn" onclick="updateCartQuantity(<?php echo $product['id']; ?>, 1)">+</button>
                        </div>
                        <button class="remove-from-cart" onclick="removeFromCart(<?php echo $product['id']; ?>)">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-summary">
            <h2>Cart Summary</h2>
            <div class="summary-item">
                <span>Total Items:</span>
                <span><?php echo count($cartItems); ?></span>
            </div>
            <div class="summary-item">
                <span>Total:</span>
                <span>Ksh <?php echo number_format(array_sum(array_map(function($qty, $price) {
                    return $qty * $price;
                }, $cartItems, array_column($products, 'price'))), 2); ?></span>
            </div>
            <button class="checkout-btn">Proceed to Checkout</button>
        </div>
    <?php else: ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h2>Your cart is empty</h2>
            <p>Browse our products and add items to your cart.</p>
            <a href="/" class="continue-shopping">Continue Shopping</a>
        </div>
    <?php endif; ?>
</main>

<footer class="footer">
    <div class="footer-content">
        <!-- Footer content remains the same as in the index.php file -->
    </div>
</footer>

<script>
function updateCartQuantity(productId, change) {
    const currentQuantity = parseInt(document.querySelector(`[data-product-id="${productId}"] .quantity`).textContent);
    const newQuantity = currentQuantity + change;
    
    if (newQuantity <= 0) {
        removeFromCart(productId);
    } else {
        fetch('update-cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ productId, quantity: newQuantity })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`[data-product-id="${productId}"] .quantity`).textContent = newQuantity;
                updateCartCount(data.cartCount);
            } else {
                showToast('Failed to update cart', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        });
    }
}

function removeFromCart(productId) {
    fetch('remove-from-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector(`[data-product-id="${productId}"]`).remove();
            updateCartCount(data.cartCount);
            
            if (data.cartCount === 0) {
                document.querySelector('.cart-items').style.display = 'none';
                document.querySelector('.cart-summary').style.display = 'none';
                document.querySelector('.empty-cart').style.display = 'flex';
            }
        } else {
            showToast('Failed to remove item from cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred', 'error');
    });
}

function updateCartCount(count) {
    const cartCount = document.querySelector('.header-icons a:last-child span');
    cartCount.textContent = `Cart (${count})`;
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast show ${type}`;
    
    setTimeout(() => {
        toast.className = 'toast';
    }, 3000);
}
</script>

<div id="toast" class="toast"></div>

<?php
// Handle AJAX requests for update-cart and remove-from-cart functionality
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $productId = isset($input['productId']) ? (int)$input['productId'] : 0;
    $quantity = isset($input['quantity']) ? (int)$input['quantity'] : 0;
    
    if ($productId > 0) {
        if ($quantity > 0) {
            // Update cart quantity
            $_SESSION['cart'][$productId] = $quantity;
        } else {
            // Remove from cart
            unset($_SESSION['cart'][$productId]);
        }
        
        echo json_encode([
            'success' => true,
            'cartCount' => array_sum($_SESSION['cart'])
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid product ID'
        ]);
    }
    exit;
}

$conn->close();
?>