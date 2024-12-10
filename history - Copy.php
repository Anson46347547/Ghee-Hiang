<?php
session_start();
include("php/connect.php"); // Include database connection

// Fetch username from the session
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

if (!$username) {
    echo "<script>alert('Please log in to view your purchase history.'); window.location.href='login.php';</script>";
    exit;
}

// Fetch user ID
$userQuery = mysqli_query($con, "SELECT id FROM users WHERE username='$username'");
$user = mysqli_fetch_assoc($userQuery);

if (!$user) {
    echo "<script>alert('User not found'); window.location.href='login.php';</script>";
    exit;
}

$userId = $user['id'];

// Fetch user's purchase history
$historyQuery = mysqli_query($con, "
    SELECT product_name, quantity, price, order_date, delivery_method 
    FROM orders 
    WHERE user_id = $userId
    ORDER BY order_date DESC
");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Purchase History</title>
    <link rel="stylesheet" href="history.css">
</head>
<style>
    .history-container {
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 10px;
    background-color: #f9f9f9;
}

.history-container h1 {
    text-align: center;
    margin-bottom: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

table th, table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

table th {
    background-color: #f4f4f4;
    font-weight: bold;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.button {
    display: inline-block;
    margin-top: 10px;
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    text-align: center;
    margin-left:310px;
}

.button:hover {
    background-color: #0056b3;
}

</style>
<body>
    <div class="history-container">
        <h1>Purchase History</h1>
        <?php if (mysqli_num_rows($historyQuery) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price (RM)</th>
                        <th>Delivery Method</th>
                    </tr>
                </thead>
                <tbody>
                <a href="shopwithgheehiang.php" class="button">Continue Shopping</a>
    <?php 
    $previousDateTime = ''; // Track the previous order's date and time
    while ($row = mysqli_fetch_assoc($historyQuery)): 
        $currentDateTime = $row['order_date'];
        if ($previousDateTime !== $currentDateTime): 
            // Start a new receipt section for a different order date and time
    ?>
        <tr>
            <td colspan="5" style="font-weight: bold; text-align: left; background-color: #f4f4f4;">
                Order Date & Time: <?php echo htmlspecialchars($currentDateTime); ?>
            </td>
        </tr>
    <?php 
        endif; 
    ?>
        <tr>
            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
            <td><?php echo $row['quantity']; ?></td>
            <td>RM<?php echo number_format($row['price'], 2); ?></td>
            
            <td><?php echo htmlspecialchars($row['delivery_method']); ?></td>
        </tr>
    <?php 
        $previousDateTime = $currentDateTime; // Update the previous order date and time
    endwhile; 
    ?>
</tbody>

            </table>
        <?php else: ?>
            <p>You have no purchase history yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
