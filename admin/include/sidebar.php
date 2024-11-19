<head>
    <style>/* Global styles for all elements */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* Body styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
        }
        
        /* Container layout */
        .container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar styles */
        .sidebar {
            width: 250px;
            background-color: #343a40; /* Dark background for the sidebar */
            color: white; /* White text */
            padding: 20px;
        }
        
        /* Sidebar title styles */
        .sidebar h2 {
            margin-bottom: 30px;
            text-align: center;
            font-size: 24px;
        }
        
        /* Sidebar navigation list styles */
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        
        /* Sidebar list items styles */
        .sidebar ul li {
            margin-bottom: 15px;
        }
        
        /* Sidebar links styles */
        .sidebar ul li a {
            color: white; /* White text for links */
            text-decoration: none; /* Remove underline */
            display: block; /* Make the link a block element for easy padding */
            padding: 10px;
            border-radius: 5px; /* Rounded corners for the links */
            font-size: 16px;
        }
        
        /* Hover effect for sidebar links */
        .sidebar ul li a:hover {
            background-color: #495057; /* Darker background when hovered */
        }
        
        /* Active link styles (optional, you can add this if needed) */
        .sidebar ul li a.active {
            background-color: #007bff; /* Active link background */
        }
        </style>
</head>
<div class="container">
    <div class="sidebar">
        <h2>Admin Panel</h2>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="addproducts.php"><i class="fas fa-box"></i>Add Products</a></li>
            <li><a href="productview.php"><i class="fas fa-box"></i>Products</a></li>
            <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="customers.php"><i class="fas fa-users"></i> Customers</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>

