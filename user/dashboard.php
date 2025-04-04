<?php
require 'include/db_connection.php';

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    $isLoggedIn = true;
    
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';
    
    
    $baseQuery = "SELECT id, category, name, description, price, image, percentage_discount, 
                 (price - (price * percentage_discount / 100)) AS discounted_price 
                 FROM products WHERE stock_quantity > 0";
    $params = [];
    $types = "";
    
    if ($search) {
        $baseQuery .= " AND (name LIKE ? OR description LIKE ? OR category LIKE ?)";
        $searchParam = "%{$search}%";
        $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        $types .= "sss";
    }
    
    switch ($sort) {
        case 'price_asc':
            $baseQuery .= " ORDER BY discounted_price ASC";
            break;
        case 'price_desc':
            $baseQuery .= " ORDER BY discounted_price DESC";
            break;
        case 'name_asc':
            $baseQuery .= " ORDER BY name ASC";
            break;
        default:
            $baseQuery .= " ORDER BY id DESC";
    }
    
    $stmt = $conn->prepare($baseQuery);
    
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while loading the dashboard. Please try again.";
    header("Location: error.php");
    exit();
} finally {
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern eCommerce - Your Ultimate Shopping Destination</title>
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="assets/img/cart.jpg" rel="apple-touch-icon">
    <meta name="description" content="Discover amazing products at great prices on our modern eCommerce platform">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
    
</head>
<body>

<header class="header">
    <div class="header-content">
        <a href="dashboard.php" class="logo">
            <i class="fas fa-shopping-bag"></i>
            ModernCart
        </a>
        <form class="search-bar" method="GET">
            <input type="text" name="search" placeholder="Search products by Description or category..." value="<?php echo htmlspecialchars($search); ?>" />
        </form>
        <div class="header-icons">
            <div class="account-dropdown">
                <a href="<?php echo $isLoggedIn ? 'edit_profile.php' : 'http://localhost:8000/user/'; ?>" class="account-link">
                    <i class="fas fa-user"></i>
                    <span>
                        <?php 
                        if ($isLoggedIn) {
                            echo htmlspecialchars($_SESSION['customers']['first_name']);
                        } 
                        ?>
                    </span>
                </a>
                <div class="dropdown-content">
                    <a href="view_profile.php">My Account</a>
                    <a href="checkout.php">Orders</a>
                    <a href="saved_items.php">Saved Items</a>
                    <a href="logout.php">Sign Out</a>
                </div>
            </div>
            <a href="cart.php">
                <i class="fas fa-shopping-cart"></i>
                <span>Cart (<?php echo count($_SESSION['cart']); ?>)</span>
            </a>
        </div>
    </div>
</header>

<div class="filters">
    <div class="filters-content">
        <div class="results-count">
            <?php if ($search): ?>
                
            <?php endif; ?>
        </div>
        <div class="sort-options">
            <select name="sort" onchange="window.location.href='?sort='+this.value<?php echo $search ? '&search='.urlencode($search) : ''; ?>">
                <option value="default" <?php echo $sort === 'default' ? 'selected' : ''; ?>>Sort by</option>
                <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
            </select>
        </div>
    </div>
</div>


<div class="discount-banner">
    <div class="banner-content">
        <div class="banner-header">
            <h2>Flash Sale! <span class="highlight">Limited Time Offers</span></h2>
            <p>Grab these exclusive deals before they're gone</p>
        </div>
        <div class="banner-products">
            <?php
            
            $discountQuery = "SELECT id, name, image, price, percentage_discount, 
                             (price - (price * percentage_discount / 100)) AS discounted_price 
                             FROM products 
                             WHERE percentage_discount > 0 AND stock_quantity > 0 
                             ORDER BY percentage_discount DESC 
                             LIMIT 4";
            
            $discountStmt = $conn->prepare($discountQuery);
            $discountStmt->execute();
            $discountResult = $discountStmt->get_result();
            
            if ($discountResult->num_rows > 0) {
                while($discount = $discountResult->fetch_assoc()) {
                    ?>
                    <div class="banner-product">
                        <div class="banner-product-image">
                            <img src="/admin/Products/<?php echo htmlspecialchars($discount['image']); ?>" 
                                alt="<?php echo htmlspecialchars($discount['name']); ?>">
                            <div class="banner-discount-badge">-<?php echo $discount['percentage_discount']; ?>%</div>
                        </div>
                        <div class="banner-product-details">
                            <h3><?php echo htmlspecialchars($discount['name']); ?></h3>
                            <div class="banner-product-price">
                                <span class="banner-price-original">Ksh <?php echo number_format($discount['price'], 1); ?></span>
                                <span class="banner-price-discounted">Ksh <?php echo number_format($discount['discounted_price'], 1); ?></span>
                            </div>
                            <button class="banner-add-to-cart" onclick="addToCart(<?php echo $discount['id']; ?>)">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="no-discounts">No discounted products available at the moment.</div>';
            }
            
            $discountStmt->close();
            ?>
        </div>
        <div class="banner-cta">
            <p>More deals available! <a href="?sort=price_asc">Shop All Discounts</a></p>
        </div>
    </div>
</div>

<div class="products">
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="product-card" data-product-id="<?php echo $row['id']; ?>">
                <div class="product-image">
                    <img src="/admin/Products/<?php echo htmlspecialchars($row['image']); ?>" 
                         alt="<?php echo htmlspecialchars($row['name']); ?>"
                         loading="lazy">
                    <?php if(isset($row['percentage_discount']) && $row['percentage_discount'] > 0): ?>
                        <div class="discount-badge">-<?php echo $row['percentage_discount']; ?>%</div>
                    <?php endif; ?>
                </div>
                <div class="product-details">
                    <span class="product-category"><?php echo htmlspecialchars($row['category']); ?></span>
                    <h2 class="product-name"><?php echo htmlspecialchars($row['name']); ?></h2>
                    <p class="product-description"><?php echo htmlspecialchars($row['description']); ?></p>
                    <div class="product-price">
                        <?php if(isset($row['percentage_discount']) && $row['percentage_discount'] > 0): ?>
                            <span class="price-original">Ksh <?php echo number_format($row['price'], 1); ?></span>
                            <span class="price-discounted">Ksh <?php echo number_format($row['discounted_price'], 1); ?></span>
                        <?php else: ?>
                            <span>Ksh <?php echo number_format($row['price'], 1); ?></span>
                        <?php endif; ?>
                    </div>
                    <button class="add-to-cart" onclick="addToCart(<?php echo $row['id']; ?>)">
                        Add to Cart
                    </button>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h2>No products found</h2>
            <p>Try adjusting your search or filters to find what you're looking for.</p>
        </div>
    <?php endif; ?>
</div>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>About Us</h3>
            <ul>
                <li><a href="#">Our Story</a></li>
                <li><a href="#">Careers</a></li>
                <li><a href="#">Press</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Customer Service</h3>
            <ul>
                <li><a href="contact.html">Contact</a></li>
                <li><a href="#">Shipping Info</a></li>
                <li><a href="#">Returns</a></li>
                <li><a href="#">FAQ</a></li>
                <li><a href="#">Track Order</a></li>
               
            </ul>
        </div>
        <div class="footer-section">
            <h3>Shop</h3>
            <ul>
                <li><a href="#">New Arrivals</a></li>
                <li><a href="#">Best Sellers</a></li>
                <li><a href="#">Sale</a></li>
                <li><a href="#">Gift Cards</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h3>Connect</h3>
            <ul>
                <li><a href="#"><i class="fab fa-facebook"></i> Facebook</a></li>
                <li><a href="#"><i class="fab fa-twitter"></i> Twitter</a></li>
                <li><a href="#"><i class="fab fa-instagram"></i> Instagram</a></li>
                <li><a href="#"><i class="fab fa-pinterest"></i> Pinterest</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date("Y"); ?> ModernCart. All rights reserved.</p>
        <p>Designed with <i class="fas fa-heart" style="color: #ff6600;"></i> for our customers</p>
    </div>
</footer>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $productId = isset($input['productId']) ? (int)$input['productId'] : 0;
    
    if ($productId > 0) {
        $_SESSION['cart'][$productId] = 1; 
        
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
<script>
    function addToCart(productId) {
        fetch('<?php echo $_SERVER["SCRIPT_NAME"]; ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ action: 'add_to_cart', productId: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Product added to cart! Total items: ' + data.cartCount);
                updateCartCount(data.cartCount);
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function updateCartCount(count) {
        document.querySelector('.header-icons span').innerText = 'Cart (' + count + ')';
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', () => {
                const productId = button.closest('.product-card').dataset.productId;
                addToCart(productId);
            });
        });
    });
</script>
<script>
document.addEventListener("contextmenu", function (e) {
  e.preventDefault(); 
});

document.addEventListener("keydown", function (e) {
  if (e.ctrlKey && (e.key === "u" || e.key === "U")) {
    e.preventDefault(); 
  }
  if (e.key === "F12") {
    e.preventDefault();
  }
  if (e.ctrlKey && e.shiftKey && (e.key === "I" || e.key === "J")) {
    e.preventDefault(); 
  }
});
</script>