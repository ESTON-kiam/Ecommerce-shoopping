<?php
require 'include/db_connection.php';
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
    <title>Settings - Vendor Panel</title>
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="assets/img/cart.jpg" rel="apple-touch-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
   <link href="assets/css/setting.css" rel="stylesheet">                       
</head>
<body>
    <?php include('include/sidebar.php'); ?>
    
    <div class="main-content">
        <h1>Settings</h1>
        
        <?php if (isset($message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="settings-card">
            <form action="settings.php" method="POST">
                <div class="form-group">
                    <label for="site_name">Site Name</label>
                    <input type="text" id="site_name" name="site_name" 
                           value="<?php echo htmlspecialchars($settings['site_title'] ?? ''); ?>" 
                           required 
                           placeholder="Enter site name">
                </div>
                
                <div class="form-group">
                    <label for="site_email">Site Email</label>
                    <input type="email" id="site_email" name="site_email" 
                           value="<?php echo htmlspecialchars($settings['admin_email'] ?? ''); ?>" 
                           required 
                           placeholder="Enter site email">
                </div>
                
                <div class="form-group">
                    <div class="toggle-container">
                        <label class="toggle">
                            <input type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                                   <?php echo isset($settings['maintenance_mode']) && $settings['maintenance_mode'] == 1 ? 'checked' : ''; ?>>
                            <span class="slider"></span>
                        </label>
                        <span>Enable Maintenance Mode</span>
                    </div>
                </div>
                
                <button type="submit">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
</body>
</html>