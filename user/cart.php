<?php
session_name('customer_session');
session_start([
    'cookie_lifetime' => 1800, 
    'cookie_path' => '/',
    'cookie_secure' => false, 
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
]);


function redirectToLogin() {
    session_unset();
    session_destroy();
    
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    header("Location: index.php");
    exit();
}


if (!isset($_SESSION['customers']) || !isset($_SESSION['customers']['id'])) {
    redirectToLogin();
}


$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";

try {
  
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
   
    $cartItems = $_SESSION['cart'];
    $products = [];
    $totalPrice = 0;
    
    if (!empty($cartItems)) {
        
        $productIds = array_keys($cartItems);
        $productIds = array_filter($productIds, function($id) {
            return is_numeric($id) && $id > 0;
        });
        
        if (!empty($productIds)) {
            
            $placeholders = str_repeat('?,', count($productIds) - 1) . '?';
            $query = "SELECT id, name, price, image FROM products WHERE id IN ($placeholders)";
            
            $stmt = $conn->prepare($query);
            if ($stmt) {
                
                $types = str_repeat('i', count($productIds));
                $stmt->bind_param($types, ...$productIds);
                
                
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result) {
                    $products = $result->fetch_all(MYSQLI_ASSOC);
                    
                   
                    foreach ($products as $product) {
                        if (isset($cartItems[$product['id']])) {
                            $quantity = max(1, intval($cartItems[$product['id']]));
                            $totalPrice += $product['price'] * $quantity;
                        }
                    }
                }
                
                $stmt->close();
            }
        }
    }
    $isLoggedIn = true;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
    
    
    $baseQuery = "SELECT id, category, name, description, price, image FROM products WHERE 1=1";
    $params = [];
    $types = "";
    
    
    if ($search) {
        $baseQuery .= " AND (name LIKE ? OR description LIKE ? OR category LIKE ?)";
        $searchParam = "%{$search}%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        $types .= "sss";
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update':
                    if (isset($_POST['product_id'], $_POST['quantity'])) {
                        $productId = intval($_POST['product_id']);
                        $quantity = max(1, intval($_POST['quantity']));
                        
                        if ($quantity > 0) {
                            $_SESSION['cart'][$productId] = $quantity;
                        } else {
                            unset($_SESSION['cart'][$productId]);
                        }
                    }
                    break;
                    
                case 'remove':
                    if (isset($_POST['product_id'])) {
                        $productId = intval($_POST['product_id']);
                        unset($_SESSION['cart'][$productId]);
                    }
                    break;
                    
                case 'clear':
                    $_SESSION['cart'] = array();
                    break;
            }
            
        
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
    
} catch (Exception $e) {
    error_log("Cart error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while processing your cart. Please try again.";
    header("Location: /error.php");
    exit();
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        
    }
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
            <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>" />
        </form>
        <div class="header-icons">
            <div class="account-dropdown">
                <a href="<?php echo $isLoggedIn ? 'account.php' : 'customerlogin.php'; ?>" class="account-link">
                    <i class="fas fa-user"></i>
                    <span>
                        <?php 
                        if ($isLoggedIn) {
                            echo htmlspecialchars($_SESSION['customers']['first_name']);
                        } else {
                            echo 'Account';
                        }
                        ?>
                    </span>
                </a>
                <?php if ($isLoggedIn): ?>
                    <div class="dropdown-content">
                        <a href="myaccount.php">My Account</a>
                        <a href="orders.php">Orders</a>
                        <a href="saveditems.php">Saved Items</a>
                        <a href="logout.php">Sign Out</a>
                    </div>
                <?php else: ?>
                    <div class="dropdown-content">
                        <a href="customerlogin.php">Sign In</a>
                    </div>
                <?php endif; ?>
            </div>
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
               <img src="/admin/Products/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy">
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
   <button class="go-shopping-btn" style="
    background-color: #ff6600; 
    color: #fff; 
    padding: 15px 30px; 
    border: none; 
    border-radius: 8px; 
    font-size: 18px; 
    font-weight: bold; 
    text-transform: uppercase; 
    cursor: pointer; 
    transition: background-color 0.3s, transform 0.2s; 
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
" 
    onmouseover="this.style.backgroundColor='#ff4500'; this.style.transform='scale(1.05)';" 
    onmouseout="this.style.backgroundColor='#ff6600'; this.style.transform='scale(1)';">
    <a href="dashboard.php" style="color: #fff; text-decoration: none;">Go Shopping</a>
</button>

   <?php endif; ?>
</main>

<div id="toast" class="toast"></div>

<script>

function showToast(message) {
   const toast = document.getElementById('toast');
   toast.textContent = message;
   toast.classList.add('show');
   setTimeout(() => { toast.classList.remove('show'); }, 3000);
}


function updateQuantity(productId, change) {
   const quantityInput = document.getElementById(`quantity-${productId}`);
   let newQuantity = parseInt(quantityInput.value) + change;

   if (newQuantity <= 0) return;

   quantityInput.value = newQuantity;

   
   updateTotalPrice();

  
   fetch('update-cart.php', {
       method: 'POST',
       headers: { 'Content-Type': 'application/json' },
       body: JSON.stringify({ productId, quantity: newQuantity })
   })
   .then(response => response.json())
   .then(data => showToast(data.message))
   .catch(error => console.error('Error:', error));
}


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