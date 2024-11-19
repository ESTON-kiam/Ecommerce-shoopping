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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  
    $site_name = $_POST['site_name'];
    $site_email = $_POST['site_email'];
   
    $maintenance_mode = isset($_POST['maintenance_mode']) ? 1 : 0;

    
    $update_query = "UPDATE settings SET 
                    site_title = '$site_name', 
                    admin_email = '$site_email', 
                    
                    maintenance_mode = '$maintenance_mode' 
                    WHERE id = 1";

    if ($conn->query($update_query) === TRUE) {
        $message = "Settings updated successfully!";
    } else {
        $message = "Error updating settings: " . $conn->error;
    }
}


$settings_query = "SELECT * FROM settings WHERE id = 1";
$settings_result = $conn->query($settings_query);

if ($settings_result->num_rows > 0) {
    $settings = $settings_result->fetch_assoc();
} else {
    echo "Settings not found!";
    $settings = array(); 
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="assets/img/cart.jpg" rel="apple-touch-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admindash.css">
</head>
<body>
    <div class="container">
        
        <div class="sidebar">
            <h2>Admin Panel</h2>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="addproducts.php"><i class="fas fa-box"></i> Add Products</a></li>
                <li><a href="productview.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
                <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1>Settings</h1>
            
            <?php if (isset($message)): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form action="settings.php" method="POST">
                <label for="site_name">Site Name:</label>
                <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_title'] ?? ''); ?>" required><br>

                <label for="site_email">Site Email:</label>
                <input type="email" id="site_email" name="site_email" value="<?php echo htmlspecialchars($settings['admin_email'] ?? ''); ?>" required><br>

                
                <label for="maintenance_mode">Maintenance Mode:</label>
                <input type="checkbox" id="maintenance_mode" name="maintenance_mode" <?php echo isset($settings['maintenance_mode']) && $settings['maintenance_mode'] == 1 ? 'checked' : ''; ?>>
                <span>Enable Maintenance Mode</span><br>

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>
