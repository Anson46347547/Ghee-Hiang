<?php
session_start();
    
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gheehiang";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Calculate total amount with additional RM5 delivery fee if applicable
function calculateTotalAmount() {
    $total = 0;
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
    }
    // Include RM5 delivery fee if delivery is selected
    if (isset($_SESSION['delivery_method']) && $_SESSION['delivery_method'] === 'delivery') {
        $total += 5;
    }
    return $total;
}

$totalAmount = calculateTotalAmount();

// Simulate Payment Process (replace with actual integration if needed)
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_now'])) {
    $accHolderName = trim($_POST['acc_holder_name']);
    $cardNumber = trim($_POST['card_number']);
    $expiryDate = trim($_POST['expiry_date']);
    $cvc = trim($_POST['cvc']);

    // Validate fields
    if (empty($accHolderName)) {
        $errors[] = "Account holder name is required.";
    }
    if (!preg_match('/^\d{4}\s\d{4}\s\d{4}\s\d{4}$/', $cardNumber)) {
        $errors[] = "Card number must be in the format XXXX XXXX XXXX XXXX.";
    } else {
        // Remove spaces for further validation if needed
        $cardNumber = str_replace(' ', '', $cardNumber);
    }
    
    if (!preg_match('/^(0[1-9]|1[0-2])\/\d{2}$/', $expiryDate)) {
        $errors[] = "Expiry date must be in MM/YY format.";
    }
    if (!preg_match('/^\d{3}$/', $cvc)) {
        $errors[] = "CVC must be 3 digits.";
    }

    // Proceed if no errors
    if (empty($errors)) {
        // Store order details in session for displaying on thank you page
        $_SESSION['order_details'] = $_SESSION['cart'];

        // Clear the cart after successful payment
        unset($_SESSION['cart']);

        // Redirect to Thank You page
        header("Location: thankyou.php");
        exit();
    }

    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $user_id = $_SESSION['user_id'];
        $delivery_method = $_SESSION['delivery_method'];
        $branch = isset($_SESSION['branch']) ? $_SESSION['branch'] : null; // Get the selected branch
        
        foreach ($_SESSION['cart'] as $product_id => $item) {
            $product_name = $item['product_name'];
            $quantity = $item['quantity'];
            $price = $item['price'];
            $order_date = date('Y-m-d H:i:s'); // Order date
    
            // Insert the order details into the database
            $sql = "INSERT INTO orders (user_id, product_id, product_name, quantity, price, order_date, delivery_method, branch) 
                    VALUES ('$user_id', '$product_id', '$product_name', '$quantity', '$price', '$order_date', '$delivery_method', '$branch')";
            $conn->query($sql);
        }
    }

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Payment Gateway</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        .checkout-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
        }
        .checkout-container h1 {
            color: #333;
        }
        .checkout-container p {
            font-size: 1.2em;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        label {
            display: block;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .pay-btn {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            margin-top: 20px;
        }
        .pay-btn:hover {
            background-color: #218838;
        }
        .error {
            color: red;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        a {
            text-decoration: none;
            color: #007bff;
            display: inline-block;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <h1>Payment Gateway</h1>
        <p>Total Amount: RM<?php echo number_format($totalAmount, 2); ?></p>
        
        <?php
        if (!empty($errors)) {
            echo '<div class="error">';
            foreach ($errors as $error) {
                echo $error . '<br>';
            }
            echo '</div>';
        }
        ?>

        <form method="POST">
            <div class="form-group">
                <label for="acc_holder_name">Account Holder Name:</label>
                <input type="text" id="acc_holder_name" name="acc_holder_name" required>
            </div>
            <div class="form-group">
    <label for="card_number">Card Number:</label>
    <input 
        type="text" 
        id="card_number" 
        name="card_number" 
        maxlength="19" 
        required 
        pattern="\d{4}\s\d{4}\s\d{4}\s\d{4}" 
        title="Card number must be 16 digits (4 groups of 4 digits separated by spaces)">
</div>

            <div class="form-group">
                <label for="expiry_date">Expiry Date (MM/YY):</label>
                <input type="text" id="expiry_date" name="expiry_date" required pattern="(0[1-9]|1[0-2])/\d{2}" title="Expiry date must be in MM/YY format">
            </div>
            <div class="form-group">
                <label for="cvc">CVC:</label>
                <input type="text" id="cvc" name="cvc" maxlength="3" required pattern="\d{3}" title="CVC must be 3 digits">
            </div>
            <button type="submit" name="pay_now" class="pay-btn">Pay</button>
        </form>
        <br><a href="Totalcart.php">Back to Cart</a>
    </div>

    <script>
    // Automatically add spaces after every 4 digits in the card number field
    const cardNumberInput = document.getElementById('card_number');

    cardNumberInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, ''); // Remove all non-digit characters
        value = value.replace(/(.{4})/g, '$1 ').trim(); // Add space after every 4 digits
        e.target.value = value;
    });
</script>

</body>
</html>

<?php
$conn->close();
?>
