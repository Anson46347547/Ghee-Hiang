<?php
session_start();
include("php/connect.php");

// Handle search input
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';

// Fetch orders based on the search query
$query = "
   SELECT 
    users.username, 
    users.contact, 
    users.address,   -- Added the address field
    GROUP_CONCAT(orders.product_name SEPARATOR ', ') AS products,
    GROUP_CONCAT(orders.quantity SEPARATOR ', ') AS quantities,
    GROUP_CONCAT(orders.price SEPARATOR ', ') AS prices,
    orders.order_date,
    GROUP_CONCAT(DISTINCT UPPER(orders.delivery_method) SEPARATOR ', ') AS delivery_methods
FROM users 
JOIN orders ON users.id = orders.user_id
WHERE users.username LIKE '%$search%' 
    OR users.contact LIKE '%$search%' 
    OR users.address LIKE '%$search%'  -- Added address search
    OR orders.order_date LIKE '%$search%'
    OR DATE_FORMAT(orders.order_date, '%d-%m-%Y') LIKE '%$search%'
     OR orders.delivery_method LIKE '%$search%'
GROUP BY users.id, orders.order_date
ORDER BY orders.order_date DESC
";

$result = mysqli_query($con, $query);

// Handle errors in query
if (!$result) {
    die("Error fetching orders: " . mysqli_error($con));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order List</title>
    <link rel="stylesheet" href="order.css">
</head>
<body>

<div class="container">
    <!-- Sidebar -->
    <div class="sidebar">
        <h2>GH ADMIN</h2>
        <ul>
            <li><a href="adminpage.php">Item Management</a></li>
            <li><a href="usersetting.php">User & Admin Settings</a></li>
            <li><a href="orderlist.php">Orders</a></li>
            <li><a href="report.php">Sales Reports</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Order List</h2>

        <!-- Search Form -->
        <form method="GET" style="margin-bottom: 20px;">
            <input 
                type="text" 
                name="search" 
                placeholder="Username, Contact, Date or Delivery Method" 
                value="<?php echo htmlspecialchars($search); ?>" 
                style="padding: 8px; width: 300px;">
            <button 
                type="submit" 
                style="padding: 8px; background-color: #2c3e50; color: #ecf0f1; border: none; cursor: pointer;">
                Search
            </button>
        </form>

        <!-- Order Table -->
        <?php if (mysqli_num_rows($result) > 0): ?>
            <table>
                <tr>
                    <th>Username</th>
                    <th>Contact</th>
                    <th>Address</th>
                    <th>Ordered Items</th>
                    <th>Total Price</th>
                    <th>Delivery Method</th>
                    <th>Order Date / Time</th>
                    <?php while ($row = mysqli_fetch_assoc($result)): 
        $products = explode(', ', $row['products']);
        $quantities = explode(', ', $row['quantities']);
        $prices = explode(', ', $row['prices']);
        $itemCount = count($products);

        // Calculate total price
        $totalPrice = 0;
        for ($i = 0; $i < $itemCount; $i++) {
            $totalPrice += $quantities[$i] * $prices[$i];
        }
    ?>
        <tr>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo htmlspecialchars($row['contact']); ?></td>
            <td><?php echo htmlspecialchars($row['address']); ?></td> <!-- Display Address -->
            <td>
                <?php if ($itemCount > 1): ?>
                    <a href="#" style="text-decoration: none;" onclick="showModal('<?php echo htmlspecialchars($row['products']); ?>', '<?php echo htmlspecialchars($row['quantities']); ?>', '<?php echo htmlspecialchars($row['prices']); ?>')">
                        View Items
                    </a>
                <?php else: ?>
                    <?php echo htmlspecialchars($products[0]); ?> (Qty: <?php echo $quantities[0]; ?>, RM<?php echo number_format($prices[0], 2); ?>)
                <?php endif; ?>
            </td>
            <td>RM<?php echo number_format($totalPrice, 2); ?></td>
            <td><?php echo htmlspecialchars($row['delivery_methods']); ?></td>
            <td><?php echo date('d-m-Y H:i', strtotime($row['order_date'])); ?></td>
        </tr>
    <?php endwhile; ?>
</table>
        <?php else: ?>
            <div class="no-data">No orders found.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal -->
<div id="orderModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Ordered Items</h3>
        <table id="modalTable">
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </table>
    </div>
</div>

<script>
    function showModal(products, quantities, prices) {
        const productArr = products.split(', ');
        const quantityArr = quantities.split(', ');
        const priceArr = prices.split(', ');

        const modalTable = document.getElementById('modalTable');
        
        modalTable.innerHTML = `
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>`;

        for (let i = 0; i < productArr.length; i++) {
            const row = modalTable.insertRow();
            const totalItemPrice = parseFloat(priceArr[i]) * parseInt(quantityArr[i]);
            row.insertCell(0).innerText = productArr[i];
            row.insertCell(1).innerText = quantityArr[i];
            row.insertCell(2).innerText = parseFloat(priceArr[i]).toFixed(2);
            row.insertCell(3).innerText = totalItemPrice.toFixed(2);
        }

        document.getElementById('orderModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('orderModal').style.display = 'none';
    }
</script>

</body>
</html>
