<?php
require 'include/db_connection.php';

if (isset($_POST['delete_id'])) {
    $delete_id = $conn->real_escape_string($_POST['delete_id']);
    
    $image_query = "SELECT image FROM products WHERE id = '$delete_id'";
    $image_result = $conn->query($image_query);
    if ($image_result->num_rows > 0) {
        $image_row = $image_result->fetch_assoc();
        $image_path = "Products/" . $image_row['image'];

        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    
    $delete_query = "DELETE FROM products WHERE id = '$delete_id'";
    if ($conn->query($delete_query)) {
        $_SESSION['message'] = "Product deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting product!";
    }

    header('Location: productview.php');
    exit();
}

$query = "SELECT * FROM products ORDER BY id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - Vendor Panel</title>
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="assets/img/cart.jpg" rel="apple-touch-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/adminviewproduct.css">
    <style>
        .price-original {
            text-decoration: line-through;
            color: #777;
            margin-right: 8px;
        }
        .price-discounted {
            color: #e63946;
            font-weight: bold;
        }
        .discount-badge {
            position: absolute;
            top: 0;
            right: 0;
            background-color: #e63946;
            color: white;
            padding: 2px 6px;
            font-size: 0.8em;
            font-weight: bold;
            border-radius: 3px;
        }
        .product-image-container {
            position: relative;
            width: 100px;
            height: 100px;
        }
    </style>
</head>
<body>
<?php include('include/sidebar.php'); ?>

    <div class="main-content">
        <div class="products-header">
            <h1>Products Management</h1>
            <a href="addproducts.php" class="add-product-btn">
                <i class="fas fa-plus"></i> Add New Product
            </a>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="products-table">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td>
                                <div class="product-image-container">
                                    <img src="Products/<?php echo $row['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($row['name']); ?>" 
                                         class="product-image">
                                    <?php if(isset($row['percentage_discount']) && $row['percentage_discount'] > 0): ?>
                                        <span class="discount-badge">-<?php echo $row['percentage_discount']; ?>%</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars(substr($row['description'], 0, 100)) . '...'; ?></td>
                            <td class="product-price">
                                <?php if(isset($row['percentage_discount']) && $row['percentage_discount'] > 0): ?>
                                    <span class="price-original">Ksh.<?php echo number_format($row['price'], 2); ?></span>
                                    <span class="price-discounted">Ksh.<?php 
                                        $discounted_price = $row['price'] - ($row['price'] * $row['percentage_discount'] / 100);
                                        echo number_format($discounted_price, 2); 
                                    ?></span>
                                <?php else: ?>
                                    Ksh.<?php echo number_format($row['price'], 2); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                    $stock_status = '';
                                    $stock_class = '';
                                    if ($row['stock_quantity'] > 20) {
                                        $stock_status = 'In Stock';
                                        $stock_class = 'in-stock';
                                    } elseif ($row['stock_quantity'] > 0) {
                                        $stock_status = 'Low Stock';
                                        $stock_class = 'low-stock';
                                    } else {
                                        $stock_status = 'Out of Stock';
                                        $stock_class = 'out-of-stock';
                                    }
                                ?>
                                <span class="stock-status <?php echo $stock_class; ?>">
                                    <?php echo $stock_status; ?> (<?php echo $row['stock_quantity']; ?>)
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['category']); ?></td>
                            <td class="action-buttons">
                                <a href="editproduct.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="" method="POST" style="display: inline;">
                                    <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-delete" 
                                            onclick="return confirm('Are you sure you want to delete this product?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>