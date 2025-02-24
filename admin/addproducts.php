<?php
require 'include/db_connection.php';

$categories = [
    'Official Stores',
    'Phones & Tablets',
    'TVs & Audio',
    'Appliances',
    'Health & Beauty',
    'Home & Office',
    'Fashion',
    'Computing',
    'Supermarket',
    'Baby Products',
    'Sporting Goods'
];

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $conn->real_escape_string($_POST['product_name']);
    $category = $conn->real_escape_string($_POST['category']);
    $description = $conn->real_escape_string($_POST['description']);
    $stock_quantity = intval($_POST['stock_quantity']);
    $price = floatval($_POST['price']);
    $sku = $conn->real_escape_string($_POST['sku']);
    
    $target_dir = "Products/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $image_name = '';
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (in_array($_FILES['product_image']['type'], $allowed_types)) {
            $file_extension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $image_name;
            
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
                
                $query = "INSERT INTO products (name, category, description, stock_quantity, price, image, sku) 
                         VALUES ('$product_name', '$category', '$description', $stock_quantity, $price, '$image_name', '$sku')";
                
                if ($conn->query($query)) {
                    $message = '<div class="alert alert-success">Product added successfully!</div>';
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $message = '<div class="alert alert-danger">Error adding product: ' . $db_conn->error . '</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Error uploading image!</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">Invalid file type! Please upload JPEG, JPG or PNG files only.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Please select an image!</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Ecommerce vendor</title>
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="assets/img/cart.jpg" rel="apple-touch-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="assets/css/addprod.css" rel="stylesheet">
</head>
<body>
<?php include('include/sidebar.php'); ?>

        <div class="main-content">
            <h1>Add New Product</h1>
            <?php echo $message; ?>
            
            <div class="card">
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="product_name">Product Name *</label>
                        <input type="text" id="product_name" name="product_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="sku">SKU *</label>
                        <input type="text" id="sku" name="sku" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>">
                                    <?php echo htmlspecialchars($category); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea id="description" name="description" class="form-control" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="stock_quantity">Stock Quantity *</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Price *</label>
                        <input type="number" id="price" name="price" class="form-control" step="0.01" required>
                    </div>
                    <div class="form-group">
                        <label for="product_image">Product Image *</label>
                        <input type="file" id="product_image" name="product_image" class="form-control" accept="image/*" required onchange="previewImage(this)">
                        <img id="preview" class="preview-image">
                    </div>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </form>
            </div>
        </div>
    </div>
</body>
<script src="assets/js/add.js"></script>
</html>
<?php
$conn->close();
?>