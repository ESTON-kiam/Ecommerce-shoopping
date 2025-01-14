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


if (isset($_GET['id'])) {
    $product_id = $conn->real_escape_string($_GET['id']);
    $query = "SELECT * FROM products WHERE id = '$product_id'";
    $result = $conn->query($query);
    $product = $result->fetch_assoc();

    if (!$product) {
        $_SESSION['error'] = "Product not found.";
        header('Location: productview.php');
        exit();
    }
} else {
    header('Location: productview.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sku = $conn->real_escape_string($_POST['sku']);
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = $conn->real_escape_string($_POST['price']);
    $stock_quantity = $conn->real_escape_string($_POST['stock_quantity']);
    $category = $conn->real_escape_string($_POST['category']);
    $image = $product['image']; 

    
    if (!empty($_FILES['image']['name'])) {
        $image = basename($_FILES['image']['name']);
        $target_dir = "Products/";
        $target_file = $target_dir . $image;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
    }

    
    $update_query = "UPDATE products 
                     SET sku='$sku', name='$name', description='$description', price='$price', stock_quantity='$stock_quantity', category='$category', image='$image' 
                     WHERE id='$product_id'";
    
    if ($conn->query($update_query)) {
        $_SESSION['message'] = "Product updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating product!";
    }
    header('Location: productview.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Vendor Panel</title>
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="assets/img/cart.jpg" rel="apple-touch-icon">
<link href="assets/css/editproduct.css" rel="stylesheet">
</head>
<body>
    
    <div class="container">
        <h2>Edit Product</h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="sku">SKU:</label>
                <input type="text" name="sku" value="<?php echo htmlspecialchars($product['sku']); ?>" required>
            </div>
            <div class="form-group">
                <label for="name">Product Name:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price (Ksh):</label>
                <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>
            </div>
            <div class="form-group">
                <label for="stock_quantity">Stock Quantity:</label>
                <input type="number" name="stock_quantity" value="<?php echo htmlspecialchars($product['stock_quantity']); ?>" required>
            </div>
            <div class="form-group">
                <label for="category">Category:</label>
                <input type="text" name="category" value="<?php echo htmlspecialchars($product['category']); ?>" required>
            </div>
            <div class="form-group">
                <label for="image">Image:</label>
                <input type="file" name="image">
                <p>Current image: <img src="Products/<?php echo htmlspecialchars($product['image']); ?>" width="100"></p>
            </div>
            <button type="submit" class="btn-submit">Update Product</button>
        </form>
    </div>
</body>
</html>

<?php $conn->close(); ?>