<?php
session_start();
include("php/connect.php"); // Include database connection

// Check if order details exist
$orderDetails = isset($_SESSION['order_details']) ? $_SESSION['order_details'] : [];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

// Retrieve delivery method (assuming it's stored in the session)
$deliveryMethod = isset($_SESSION['delivery_method']) ? $_SESSION['delivery_method'] : 'Standard'; // Default to 'Standard' if not set

// Function to calculate the total amount
function calculateTotalAmount($orderDetails) {
    $total = 0;
    foreach ($orderDetails as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

$totalAmount = calculateTotalAmount($orderDetails);

// Save order details to the database and update stock
if (!empty($orderDetails) && !empty($username)) {
    // Fetch user ID based on the username
    $userQuery = mysqli_query($con, "SELECT id FROM users WHERE username='$username'");
    $user = mysqli_fetch_assoc($userQuery);

    if ($user) {
        $userId = $user['id'];

        // Prepare a statement for inserting orders to prevent SQL injection
        $stmtInsertOrder = $con->prepare("INSERT INTO orders (user_id, product_name, quantity, price, order_date, delivery_method) VALUES (?, ?, ?, ?, NOW(), ?)");
        $stmtUpdateStock = $con->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_name = ?");

        foreach ($orderDetails as $item) {
            $productName = $item['product_name'];
            $quantity = $item['quantity'];
            $price = $item['price'];

            // Check stock availability
            $stockQuery = mysqli_query($con, "SELECT stock_quantity FROM products WHERE product_name='$productName'");
            $stock = mysqli_fetch_assoc($stockQuery);

            if ($stock && $stock['stock_quantity'] >= $quantity) {
                // Insert order
                $stmtInsertOrder->bind_param("isids", $userId, $productName, $quantity, $price, $deliveryMethod);
                $stmtInsertOrder->execute();
                

                // Update stock quantity
                $stmtUpdateStock->bind_param("is", $quantity, $productName);
                $stmtUpdateStock->execute();
            } else {
                echo "<script>alert('Insufficient stock for $productName');</script>";
            }
        }
        // Close statements
        $stmtInsertOrder->close();
        $stmtUpdateStock->close();
    } else {
        echo "<script>alert('User not found');</script>";
    }
}

// Clear the cart after successful order
unset($_SESSION['cart']);
unset($_SESSION['order_details']);

// Include PHPMailer library
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Fetch user's email based on username
$emailQuery = mysqli_query($con, "SELECT email FROM users WHERE username='$username'");
$userEmail = mysqli_fetch_assoc($emailQuery)['email'];

if ($userEmail) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'leeanson3324@gmail.com'; // Your email address
        $mail->Password = 'uljyahvawzkrxcij'; // Your email password (use app password if using Gmail)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('noreply@gheehiang.com', 'Ghee Hiang');
        $mail->addAddress($userEmail); // User's email address

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Thanks for Purchasing at Ghee Hiang!';
        
        // Build the email body
        $emailBody = "<html><body>";
        $emailBody .= "<p>Here are the details of your order:</p>";
        $emailBody .= "<table border='1' cellspacing='0' cellpadding='10'>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                        </tr>";
        foreach ($orderDetails as $item) {
            $emailBody .= "<tr>
                            <td>" . htmlspecialchars($item['product_name']) . "</td>
                            <td>" . $item['quantity'] . "</td>
                            <td>RM" . number_format($item['price'], 2) . "</td>
                           </tr>";
        }
        $emailBody .= "</table>";
        $emailBody .= "<p><strong>Total Amount : RM" . number_format($totalAmount, 2) . "</strong></p>";
        $emailBody .= "<p>Your chosen delivery method : <strong>$deliveryMethod</strong></p>";
        $emailBody .= "<p>We appreciate your business   and hope to see you again soon!</p>";
        $emailBody .= "</body></html>";

        $mail->Body = $emailBody;

        // Send the email
        $mail->send();
    } catch (Exception $e) {
        // Log the error if needed, but don't show alerts
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="thank.css">
    <title>Thank You</title>
</head>
<body>
    <div class="thank-you-container">
        <h1>Thanks for Purchasing</h1>
        <p>Your order has been successfully processed</p>
        <p>Please check your email for comfirmation</p>

        <!-- Conditional message based on the delivery method -->
        <p>
            <?php 
                if ($deliveryMethod == 'delivery') {
                    echo "We will deliver within 1 hour";
                } elseif ($deliveryMethod == 'pickup') {
                    echo "Order will be prepared for collect after 15 minutes.";
                }
            ?>
        </p>

        <!-- Display Order Summary Table -->
        <?php if (!empty($orderDetails)): ?>
            <table>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price per item</th>
                </tr>
                <?php foreach ($orderDetails as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>RM<?php echo number_format($item['price'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <div class="total-amount">
            <p>Total Amount: RM<?php echo number_format($totalAmount, 2); ?></p>
        </div>

        <a href="shopwithgheehiang.php" class="button">Continue Shopping</a>
    </div>
</body>
</html>
