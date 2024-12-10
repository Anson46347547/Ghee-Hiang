<?php
    session_start(); // Start the session

    // Database connection parameters
    $servername = "localhost";
    $username = "root";
    $password = ""; // Update with your database password if any
    $dbname = "gheehiang";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Sync cart quantities with the latest stock levels from the database
    function syncCartWithStock($conn) {
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $product_id => &$item) {
                $sql = "SELECT stock_quantity FROM products WHERE product_id = $product_id";
                $result = $conn->query($sql);
                
                if ($result && $result->num_rows > 0) {
                    $product = $result->fetch_assoc();
                    $latest_stock = $product['stock_quantity'];
                    
                    // Update the stock quantity in the session
                    $item['stock_quantity'] = $latest_stock;

                    // If cart quantity exceeds the latest stock, adjust it
                    if ($item['quantity'] > $latest_stock) {
                        $item['quantity'] = $latest_stock;
                    }

                    // Remove item if stock is zero
                    if ($latest_stock <= 0) {
                        unset($_SESSION['cart'][$product_id]);
                    }
                } else {
                    // Product no longer exists, remove from cart
                    unset($_SESSION['cart'][$product_id]);
                }
            }
        }
    }

    // Call the sync function
    syncCartWithStock($conn);

    // Handle quantity updates in the cart
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Adding a new product to the cart
        if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
            $product_id = intval($_POST['product_id']);
            $quantity = intval($_POST['quantity']);
            
            // Fetch the product details from the database
            $sql = "SELECT * FROM products WHERE product_id = $product_id";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $product = $result->fetch_assoc();

                // Check if the cart exists, otherwise create it
                if (!isset($_SESSION['cart'])) {
                    $_SESSION['cart'] = array();
                }

                // Add the product to the cart or update the quantity if it already exists
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id]['quantity'] += $quantity;
                } else {
                    $_SESSION['cart'][$product_id] = array(
                        'product_name' => $product['product_name'],
                        'price' => $product['item_price'],
                        'quantity' => $quantity,
                        'image' => $product['product_image'],
                        'stock_quantity' => $product['stock_quantity']
                    );
                }
            }
        }

        if (isset($_POST['new_quantity'])) {
            foreach ($_POST['new_quantity'] as $product_id => $new_quantity) {
                if (isset($_SESSION['cart'][$product_id])) {
                    // Ensure the new quantity does not exceed the available stock
                    $new_quantity = intval($new_quantity);
                    if ($new_quantity > 0 && $new_quantity <= $_SESSION['cart'][$product_id]['stock_quantity']) {
                        $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
                    } else {
                        // Remove the item if quantity is invalid or zero
                        unset($_SESSION['cart'][$product_id]);
                    }
                }
            }
        }

        // Handle item delete
        if (isset($_POST['delete_product_id'])) {
            $delete_product_id = intval($_POST['delete_product_id']);
            if (isset($_SESSION['cart'][$delete_product_id])) {
                unset($_SESSION['cart'][$delete_product_id]);
            }
        }

        // Handle delivery/pickup selection
        if (isset($_POST['delivery_method'])) {
            $_SESSION['delivery_method'] = $_POST['delivery_method'];
        }
    }

    // Calculate total amount function with additional RM5 charge for delivery
    function calculateTotalAmount() {
        $total = 0;
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $total += $item['price'] * $item['quantity'];
            }
        }

        // Add RM5 for delivery option
        if (isset($_SESSION['delivery_method']) && $_SESSION['delivery_method'] === 'delivery') {
            $total += 8; // Delivery charge
        }

        return $total;
    }

    // Fetch user address if logged in
    $user_address = '';
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT address FROM users WHERE user_id = $user_id";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $user_address = $result->fetch_assoc()['address'];
        }
    }

        // Get current time
        $current_time = new DateTime();

    ?>  

    <!DOCTYPE html>
    <html>
    <link rel="stylesheet" href="carts.css">
    <head>
        <script>
    function updateQuantity(product_id, change, maxQuantity) {
    let quantityInput = document.getElementById(`quantity-${product_id}`);
    let quantity = parseInt(quantityInput.value) + change;

    if (quantity < 0) {
        quantity = 0;
    }

    if (quantity === 0) {
        if (!confirm("Are you sure you want to remove this item from your cart?")) {
            quantity = 1; // Reset to 1 if not confirmed
        }
    }

    if (quantity > maxQuantity) {
        alert("The quantity cannot exceed the available stock (" + maxQuantity + ").");
        quantity = maxQuantity;
    }

    quantityInput.value = quantity;

    // Submit the form to update the cart
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'Totalcart.php';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = `new_quantity[${product_id}]`;
    input.value = quantity;

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();

    function validateCheckout() {
    // Check if delivery method is set
    const deliveryMethod = <?php echo json_encode($_SESSION['delivery_method'] ?? null); ?>;

    if (!deliveryMethod) {
        alert("Please choose a delivery method.");
        return false; // Prevent form submission
    }

    return true; // Allow form submission
    }
}   

        </script>
    </head>
    
    <body>
    <div class="cart-container">
    <h1>Cart (<?php echo isset($_SESSION['cart']) && is_array($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?> items)</h1>

        <?php if (!empty($_SESSION['cart'])): ?>
            <?php foreach ($_SESSION['cart'] as $id => $item): ?>
                <div class="cart-item">
                    <!-- Product Image -->
                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">

                    <!-- Product Name -->
                    <p><?php echo htmlspecialchars($item['product_name']); ?></p>

                    <!-- Product Price -->
                    <p>RM<?php echo number_format($item['price'], 2); ?></p>

                <!-- Product Quantity and Stock -->
    <div>
        <p>Stock: <?php echo $item['stock_quantity']; ?></p>
        <div style="display: flex; align-items: center;">
            <!-- Minus Button -->
            <button onclick="updateQuantity(<?php echo $id; ?>, -1, <?php echo $item['stock_quantity']; ?>)" style="padding: 5px 10px;">-</button>
            
            <!-- Quantity Input (Now editable) -->
            <input type="number" id="quantity-<?php echo $id; ?>" value="<?php echo $item['quantity']; ?>" min="0" max="<?php echo $item['stock_quantity']; ?>" style="width: 50px; text-align: center;">

            <!-- Plus Button -->
            <button onclick="updateQuantity(<?php echo $id; ?>, 1, <?php echo $item['stock_quantity']; ?>)" style="padding: 5px 10px;">+</button>
        </div>
    </div>

                    <!-- Delete Button -->
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete_product_id" value="<?php echo $id; ?>">
                        <button type="submit" class="delete-btn">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
            

            <!-- Delivery or Pickup selection -->
            <div class="buttonDP">
            <form method="POST">              
                <br>
                <button type="submit" name="delivery_method" value="delivery" class="btn <?php echo isset($_SESSION['delivery_method']) && $_SESSION['delivery_method'] === 'delivery' ? 'active' : ''; ?>">Delivery</button>
                <button type="submit" name="delivery_method" value="pickup" class="btn <?php echo isset($_SESSION['delivery_method']) && $_SESSION['delivery_method'] === 'pickup' ? 'active' : ''; ?>">Pickup</button>
            </form>
            </div>

        <!-- Delivery Section -->
    <?php if (isset($_SESSION['delivery_method']) && $_SESSION['delivery_method'] === 'delivery'): ?>
        <form method="POST">
            <p>Delivery Fee Charged RM8</p>
        </form>
    <?php endif; ?>

    <!-- Pickup Section -->
    <?php if (isset($_SESSION['delivery_method']) && $_SESSION['delivery_method'] === 'pickup'): ?>
        <form method="POST">
            <!-- Pickup Branch -->
            <br>
        <a href="FindUs.html" target="_blank" class="btn btn-info" style="text-decoration: none;">Find Us Here</a>         
        </form>
    <?php endif; ?>


            <!-- Total Amount -->
            <div class="total-container">
                <h2>Total Amount: RM<?php echo number_format(calculateTotalAmount(), 2); ?></h2>
            </div>

            <!-- Buttons -->
            <div class="buttons-container">
                <a href="shopwithgheehiang.php" class="btn btn-primary">Continue Shopping</a>
                <a href="checkout.php" class="btn btn-secondary" id="checkout-btn" onclick="return validateCheckout();">Checkout</a>
            </div>

        <?php else: ?>
            <p>Your cart is empty.</p>
            <a href="shopwithgheehiang.php" class="btn btn-secondary">Continue Shopping</a>
        <?php endif; ?>
    </div>
    </body>
    </html>

    <?php
    // Close the database connection
    $conn->close();
    ?>