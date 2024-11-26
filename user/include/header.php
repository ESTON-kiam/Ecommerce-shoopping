<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - ModernCart</title>
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="assets/img/cart.jpg" rel="apple-touch-icon">
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
                <a href="<?php echo $isLoggedIn ? 'edit_profile.php' : 'http://localhost:8000/user/'; ?>" class="account-link">
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
                        <a href="view_profile.php">My Account</a>
                        <a href="checkout.php">Orders</a>
                        <a href="saved_items.php">Saved Items</a>
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
</body></html>