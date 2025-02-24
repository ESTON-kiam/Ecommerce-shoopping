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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f0f2f5;
            color: #333;
        }

        .main-content {
            margin-left: 250px;
            padding: 2rem;
            max-width: 1200px;
        }

        h1 {
            color: #1a237e;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 0.5rem;
        }

        h1::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            height: 4px;
            width: 60px;
            background: linear-gradient(to right, #1a237e, #534bae);
            border-radius: 2px;
        }

        .settings-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            margin-top: 1rem;
            transition: transform 0.3s ease;
        }

        .settings-card:hover {
            transform: translateY(-5px);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            animation: slideIn 0.5s ease;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }

        form {
            display: grid;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        label {
            font-weight: 500;
            color: #444;
            font-size: 0.95rem;
        }

        input[type="text"],
        input[type="email"] {
            padding: 0.8rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus {
            border-color: #1a237e;
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 35, 126, 0.1);
        }

        .toggle-container {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .toggle {
            position: relative;
            width: 50px;
            height: 28px;
        }

        .toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #1a237e;
        }

        input:checked + .slider:before {
            transform: translateX(22px);
        }
        button {
            background: linear-gradient(45deg, #1a237e, #534bae);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26, 35, 126, 0.2);
        }

        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
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