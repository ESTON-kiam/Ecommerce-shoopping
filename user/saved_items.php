<?php
session_name('customer_session');
session_start();

if (!isset($_SESSION['customers']) || !isset($_SESSION['customers']['id'])) {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $customer_id = $_SESSION['customers']['id'];

    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'update_quantity':
                    $product_id = intval($_POST['product_id']);
                    $quantity = max(1, intval($_POST['quantity']));
                    
                    $stmt = $conn->prepare("UPDATE saved_items SET quantity = ? WHERE customer_id = ? AND product_id = ?");
                    $stmt->bind_param("iii", $quantity, $customer_id, $product_id);
                    $stmt->execute();
                    $stmt->close();
                    
                    
                    $total_cost = calculateTotalCost($conn, $customer_id);
                    
                    echo json_encode([
                        'status' => 'success', 
                        'message' => 'Quantity updated',
                        'total_cost' => $total_cost
                    ]);
                    exit();

                case 'remove_saved_item':
                    $product_id = intval($_POST['product_id']);
                    
                    $stmt = $conn->prepare("DELETE FROM saved_items WHERE customer_id = ? AND product_id = ?");
                    $stmt->bind_param("ii", $customer_id, $product_id);
                    $stmt->execute();
                    $stmt->close();
                    
                 $total_cost = calculateTotalCost($conn, $customer_id);
                    
                    echo json_encode([
                        'status' => 'success', 
                        'message' => 'Item removed from saved items',
                        'total_cost' => $total_cost
                    ]);
                    exit();

                case 'place_order':

                    $query = "
                        SELECT si.product_id, si.quantity, p.name, p.price 
                        FROM saved_items si 
                        JOIN products p ON si.product_id = p.id 
                        WHERE si.customer_id = ?
                    ";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $customer_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $savedItems = $result->fetch_all(MYSQLI_ASSOC);
                    $stmt->close();


                    $conn->begin_transaction();

                    try {

                        $order_total = 0;
                        $insert_order = $conn->prepare("INSERT INTO orders (customer_id, total_amount, order_date) VALUES (?, ?, NOW())");
                        $insert_order->bind_param("id", $customer_id, $order_total);
                        $insert_order->execute();
                        $order_id = $conn->insert_id;
                        $insert_order->close();

                        $insert_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                        
                        foreach ($savedItems as $item) {
                            $total_item_price = $item['price'] * $item['quantity'];
                            $order_total += $total_item_price;
                            
                            $insert_item->bind_param("iid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                            $insert_item->execute();
                        }
                        $insert_item->close();

                        $update_order = $conn->prepare("UPDATE orders SET total_amount = ? WHERE id = ?");
                        $update_order->bind_param("di", $order_total, $order_id);
                        $update_order->execute();
                        $update_order->close();

                        
                        $clear_saved = $conn->prepare("DELETE FROM saved_items WHERE customer_id = ?");
                        $clear_saved->bind_param("i", $customer_id);
                        $clear_saved->execute();
                        $clear_saved->close();

                        $conn->commit();

                        echo json_encode([
                            'status' => 'success', 
                            'message' => 'Order placed successfully',
                            'order_id' => $order_id
                        ]);
                        exit();

                    } catch (Exception $e) {
                        $conn->rollback();
                        throw $e;
                    }
            }
        }
    }

    
    function calculateTotalCost($conn, $customer_id) {
        $query = "
            SELECT SUM(p.price * si.quantity) as total_cost
            FROM saved_items si 
            JOIN products p ON si.product_id = p.id 
            WHERE si.customer_id = ?
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $total = $result->fetch_assoc()['total_cost'];
        $stmt->close();
        return $total ?? 0;
    }

    $query = "
        SELECT si.product_id, si.quantity, p.name, p.price, p.image 
        FROM saved_items si 
        JOIN products p ON si.product_id = p.id 
        WHERE si.customer_id = ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $savedItems = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $totalCost = calculateTotalCost($conn, $customer_id);

} catch (Exception $e) {
    error_log("Saved Items Error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred while processing saved items.";
    header("Location: /error.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Items - ModernCart</title>
    <link href="assets/img/cart.jpg" rel="icon">
    <link href="assets/img/cart.jpg" rel="apple-touch-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <style>
        .saved-items {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .saved-item {
            display: flex;
            align-items: center;
            border: 1px solid #e0e0e0;
            padding: 15px;
            border-radius: 8px;
        }
        .saved-item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-right: 15px;
        }
        .saved-item-details {
            flex-grow: 1;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
        }
        .quantity-btn {
            background-color: #f0f0f0;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
        .quantity-input {
            width: 50px;
            text-align: center;
            margin: 0 10px;
        }
        #total-cost {
            font-weight: bold;
            font-size: 1.2em;
            margin-top: 15px;
        }
        .place-order-btn {
            background-color: #ff6600;
            color: #fff;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <main class="container">
        <h1>Saved Items</h1>
        
        <?php if (count($savedItems) > 0): ?>
            <div class="saved-items">
                <?php foreach ($savedItems as $item): ?>
                    <div class="saved-item" data-product-id="<?php echo $item['product_id']; ?>">
                        <img src="/admin/Products/<?php echo htmlspecialchars($item['image']); ?>" 
                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                             class="saved-item-image" loading="lazy">
                        
                        <div class="saved-item-details">
                            <h2><?php echo htmlspecialchars($item['name']); ?></h2>
                            <p>Price: Ksh <?php echo number_format($item['price'], 2); ?></p>
                            <p>Item Total: Ksh <span class="item-total" data-price="<?php echo $item['price']; ?>"><?php echo number_format($item['price'] * $item['quantity'], 2); ?></span></p>
                            
                            <div class="quantity-controls">
                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, -1)">-</button>
                                <input type="number" 
                                       class="quantity-input" 
                                       id="quantity-<?php echo $item['product_id']; ?>" 
                                       value="<?php echo $item['quantity']; ?>" 
                                       min="1">
                                <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['product_id']; ?>, +1)">+</button>
                                <button onclick="removeFromSavedItems(<?php echo $item['product_id']; ?>)">Remove</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="total-cost">
                Total Cost: Ksh <?php echo number_format($totalCost, 2); ?>
            </div>

            <button class="place-order-btn" onclick="placeOrder()">Place Order</button>
        <?php else: ?>
            <p>No saved items. <a href="dashboard.php">Continue Shopping</a></p>
        <?php endif; ?>
    </main>

    <script>
function showToast(message) {
    const toast = document.getElementById('toast') || createToastElement();
    toast.textContent = message;
    toast.classList.add('show');
    setTimeout(() => { toast.classList.remove('show'); }, 3000);
}

function createToastElement() {
    const toast = document.createElement('div');
    toast.id = 'toast';
    toast.className = 'toast';
    document.body.appendChild(toast);
    return toast;
}

function updateQuantity(productId, change) {
    const quantityInput = document.getElementById(`quantity-${productId}`);
    let newQuantity = parseInt(quantityInput.value) + change;

    if (newQuantity < 1) {
        showToast('Quantity must be at least 1');
        return;
    }

    fetch('saveditems.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=update_quantity&product_id=${productId}&quantity=${newQuantity}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Update quantity input
            quantityInput.value = newQuantity;

            // Update item total
            const itemTotalSpan = document.querySelector(`.saved-item[data-product-id="${productId}"] .item-total`);
            const price = parseFloat(itemTotalSpan.dataset.price);
            const itemTotal = (price * newQuantity).toFixed(2);
            itemTotalSpan.textContent = itemTotal;

            // Update total cost
            updateTotalCost(data.total_cost);
            showToast('Quantity updated successfully');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to update quantity');
    });
}

function removeFromSavedItems(productId) {
    const savedItem = document.querySelector(`.saved-item[data-product-id="${productId}"]`);
    
    fetch('saveditems.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `action=remove_saved_item&product_id=${productId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Remove the item from the DOM
            savedItem.remove();
            
            // Update total cost
            updateTotalCost(data.total_cost);

            showToast('Item removed from saved items');
            
            // Reload if no items left
            if (document.querySelectorAll('.saved-item').length === 0) {
                location.reload();
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Failed to remove item');
    });
}

function updateTotalCost(totalCost) {
    const totalCostElement = document.getElementById('total-cost');
    totalCostElement.textContent = `Total Cost: Ksh ${parseFloat(totalCost).toFixed(2)}`;
}

function placeOrder() {
    fetch('saveditems.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'action=place_order'
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showToast('Order placed successfully');
            setTimeout(() => {
                window.location.href = `order_confirmation.php?order_id=${data.order_id}`;
            }, 2000);
        } else {
            showToast('Failed to place order');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred while placing the order');
    });
}
</script>
</body>
</html>

<?php
$conn->close();
?>
