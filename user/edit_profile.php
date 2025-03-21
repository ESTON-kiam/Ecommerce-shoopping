<?php
require 'include/db_connection.php';

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

   
    if (!isset($_SESSION['customers']['id'])) {
        header('Location: index.php');
        exit();
    }

    $customer_id = $_SESSION['customers']['id'];
    $error = '';
    $success = '';
   
    $stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        
        $first_name = filter_input(INPUT_POST, 'first_name');
        $last_name = filter_input(INPUT_POST, 'last_name');
        $email = filter_input(INPUT_POST, 'email');
        $phone = filter_input(INPUT_POST, 'phone');
        $address_line1 = filter_input(INPUT_POST, 'address_line1');
        $address_line2 = filter_input(INPUT_POST, 'address_line2');
        $city = filter_input(INPUT_POST, 'city');
        $state = filter_input(INPUT_POST, 'state');
        $postal_code = filter_input(INPUT_POST, 'postal_code');
        $country = filter_input(INPUT_POST, 'country');

        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } else {
            
            $stmt = $conn->prepare("UPDATE customers SET 
                first_name = ?, 
                last_name = ?, 
                email = ?, 
                phone = ?, 
                address_line1 = ?, 
                address_line2 = ?, 
                city = ?, 
                state = ?, 
                postal_code = ?, 
                country = ?
                WHERE customer_id = ?");
            
            $stmt->bind_param("ssssssssssi", 
                $first_name, $last_name, $email, $phone, 
                $address_line1, $address_line2, $city, $state, 
                $postal_code, $country, $customer_id);
            
            if ($stmt->execute()) {
                $success = "Profile updated successfully";
                
                
                $_SESSION['customers']['first_name'] = $first_name;
                $_SESSION['customers']['email'] = $email;
                
                
                $user = [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'phone' => $phone,
                    'address_line1' => $address_line1,
                    'address_line2' => $address_line2,
                    'city' => $city,
                    'state' => $state,
                    'postal_code' => $postal_code,
                    'country' => $country
                ];
            } else {
                $error = "Error updating profile: " . $stmt->error;
            }
        }
    }
} catch (Exception $e) {
    $error = "An error occurred: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="assets/img/cart.jpg" rel="apple-touch-icon">
    <link href="assets/css/editprofile.css" rel="stylesheet">
</head>
<body>
    <div class="profile-container">
        <h2>Edit Profile</h2>

        <?php if ($error): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <p class="success-message"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-container">
                <div class="form-group">
                    <label for="first_name">First Name:</label>
                    <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name:</label>
                    <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="address_line1">Address Line 1:</label>
                    <input type="text" name="address_line1" id="address_line1" value="<?php echo htmlspecialchars($user['address_line1'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="address_line2">Address Line 2:</label>
                    <input type="text" name="address_line2" id="address_line2" value="<?php echo htmlspecialchars($user['address_line2'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="city">City:</label>
                    <input type="text" name="city" id="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="state">State:</label>
                    <input type="text" name="state" id="state" value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="postal_code">Postal Code:</label>
                    <input type="text" name="postal_code" id="postal_code" value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="country">Country:</label>
                    <input type="text" name="country" id="country" value="<?php echo htmlspecialchars($user['country'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-actions">
                <input type="submit" value="Update Profile" class="btn-submit">
                <a href="view_profile.php" class="btn-cancel">View Profile</a>
            </div>
        </form>
    </div>
</body>
</html>