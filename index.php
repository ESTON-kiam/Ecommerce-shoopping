<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eCommerce Site - Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
   <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>

<!-- Header Section -->
<div class="header">
    <h1>eCommerce</h1>
    <input type="text" placeholder="Search products..." />
    <div class="header-icons">
        <a href="account.php"><i class="fas fa-user"></i> Account</a>
        <a href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
    </div>
</div>

<!-- Products Section -->
<div class="products">
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="product-card">
                <img src="<?php echo $row['image_url']; ?>" alt="<?php echo $row['product_name']; ?>">
                <h2><?php echo $row['product_name']; ?></h2>
                <p><?php echo $row['description']; ?></p>
                <div class="price">Ksh <?php echo $row['price']; ?></div>
                <button>Add to Cart</button>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No products found</p>
    <?php endif; ?>
</div>

<!-- Footer Section -->
<div class="footer">
    &copy; <?php echo date("Y"); ?> eCommerce Site. All rights reserved.
</div>

</body>
</html>

<?php
$conn->close();
?>
