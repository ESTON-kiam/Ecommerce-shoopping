<?php
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

$admin_username = "Admin"; 
if (isset($_SESSION['admin_id'])) {
    $admin_id = $_SESSION['admin_id'];
    $query = "SELECT username FROM admins WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $stmt->bind_result($username);
    if ($stmt->fetch()) {
        $admin_username = $username;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f4f6f9;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 20px;
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar.collapsed {
            width: 80px;
            overflow: hidden;
        }

        .toggle-btn {
            position: absolute;
            right: 20px;
            top: 20px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: none;
            padding: 12px 15px;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .toggle-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            padding: 20px 0;
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header img {
            width: 40px;
            height: 40px;
            margin-right: 10px;
            border-radius: 8px;
        }

        .sidebar-header h2 {
            font-size: 22px;
            font-weight: 600;
            color: #fff;
        }

        .sidebar.collapsed .sidebar-header h2,
        .sidebar.collapsed .user-info,
        .sidebar.collapsed ul li a span {
            display: none;
        }

        .sidebar.collapsed .user-profile {
            padding: 10px;
        }

        .sidebar.collapsed ul li a {
            padding: 12px;
            justify-content: center;
        }

        .sidebar.collapsed ul li a i {
            margin: 0;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
        }

        .sidebar ul li {
            margin-bottom: 8px;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .sidebar ul li a i {
            margin-right: 10px;
            font-size: 20px;
            width: 25px;
            text-align: center;
        }

        .sidebar ul li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .sidebar ul li a.active {
            background: rgba(255, 255, 255, 0.2);
            font-weight: 600;
        }

        .user-profile {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 8px;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .user-info {
            flex: 1;
        }

        .user-info h4 {
            font-size: 16px;
            margin-bottom: 3px;
        }

        .user-info p {
            font-size: 12px;
            opacity: 0.7;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            transition: all 0.3s ease;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                z-index: 1000;
                height: 100vh;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <button class="toggle-btn">
                <i class="fas fa-bars"></i>
            </button>
            <div class="sidebar-header">
                <img src="assets/img/cart.jpg" alt="Logo">
                <h2>Vendor Panel</h2>
            </div>
            <ul>
                <li><a href="dashboard.php" class="active">
                    <i class="fas fa-home"></i><span>Dashboard</span>
                </a></li>
                <li><a href="registration.php">
                    <i class="fas fa-plus-circle"></i><span>Add Vendors</span>
                </a></li>
                <li><a href="addproducts.php">
                    <i class="fas fa-plus-circle"></i><span>Add Products</span>
                </a></li>
                
                <li><a href="productview.php">
                    <i class="fas fa-box"></i><span>Products</span>
                </a></li>
                <li><a href="orders.php">
                    <i class="fas fa-shopping-cart"></i><span>Orders</span>
                </a></li>
                <li><a href="customers.php">
                    <i class="fas fa-users"></i><span>Customers</span>
                </a></li>
                <li><a href="reports.php">
                    <i class="fas fa-chart-bar"></i><span>Reports</span>
                </a></li>
                <li><a href="settings.php">
                    <i class="fas fa-cog"></i><span>Settings</span>
                </a></li>
                <li><a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                </a></li>
            </ul>
            <div class="user-profile">
                <img src="assets/img/cart.jpg" alt="User Avatar">
                <div class="user-info">
                    <h4><?php echo htmlspecialchars($admin_username); ?></h4>
                    <p>Vendor</p>
                </div>
            </div>
        </div>

    <script>
        const toggleBtn = document.querySelector('.toggle-btn');
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            
            if (window.innerWidth > 768) {
                mainContent.style.marginLeft = sidebar.classList.contains('collapsed') ? '80px' : '280px';
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth <= 768) {
                mainContent.style.marginLeft = '0';
            } else {
                mainContent.style.marginLeft = sidebar.classList.contains('collapsed') ? '80px' : '280px';
            }
        });

        const currentLocation = location.href;
        const menuItems = document.querySelectorAll('.sidebar ul li a');
        menuItems.forEach(item => {
            if (item.href === currentLocation) {
                item.classList.add('active');
            }
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
</body>
</html>