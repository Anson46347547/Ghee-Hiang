<?php
session_start();
include("php/connect.php");

// Default year (current year) or selected year from the dropdown
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Fetch years available in the orders table
$query_years = "
    SELECT DISTINCT YEAR(order_date) AS year 
    FROM orders 
    ORDER BY year DESC
";

$result_years = mysqli_query($con, $query_years);
if (!$result_years) {
    die("Error fetching years: " . mysqli_error($con));
}

$years = [];
while ($row = mysqli_fetch_assoc($result_years)) {
    $years[] = $row['year'];
}

// Fetch monthly sales total report for the selected year
$query_monthly_sales = "
    SELECT 
        DATE_FORMAT(order_date, '%Y-%m') AS month, 
        SUM(quantity * price) AS total_sales
    FROM orders
    WHERE YEAR(order_date) = '$selected_year'
    GROUP BY month
    ORDER BY month DESC
";

$result_monthly_sales = mysqli_query($con, $query_monthly_sales);
if (!$result_monthly_sales) {
    die("Error fetching monthly sales: " . mysqli_error($con));
}

// Fetch specific item sales report for the selected year
$query_item_sales = "
    SELECT 
        product_name, 
        SUM(quantity) AS total_quantity_sold
    FROM orders
    WHERE YEAR(order_date) = '$selected_year'
    GROUP BY product_name
    ORDER BY total_quantity_sold DESC
";

$result_item_sales = mysqli_query($con, $query_item_sales);
if (!$result_item_sales) {
    die("Error fetching item sales: " . mysqli_error($con));
}

$monthly_sales_data = [];
$item_sales_data = [];

// Process monthly sales data
while ($row = mysqli_fetch_assoc($result_monthly_sales)) {
    $monthly_sales_data[] = [
        'month' => $row['month'],
        'total_sales' => $row['total_sales']
    ];
}

// Process item sales data
while ($row = mysqli_fetch_assoc($result_item_sales)) {
    $item_sales_data[] = [
        'product_name' => $row['product_name'],
        'total_quantity_sold' => $row['total_quantity_sold']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <!-- Year Selection -->
        <form method="GET" action="report.php">
            <label for="year">Select Year:</label>
            <select name="year" id="year" onchange="this.form.submit()">
                <?php foreach ($years as $year): ?>
                    <option value="<?php echo $year; ?>" <?php echo $selected_year == $year ? 'selected' : ''; ?>>
                        <?php echo $year; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Monthly Sales Total Report -->
        <div style="width: 1000px; margin: 30px auto;">
            <h3>Monthly Sales for <?php echo $selected_year; ?></h3>
            <canvas id="monthlySalesChart"></canvas>
        </div>

        <!-- Specific Item Sales Report -->
        <div style="width: 1000px; margin: 30px auto;">
            <h3>Item Sales for <?php echo $selected_year; ?></h3>
            <canvas id="itemSalesChart"></canvas>
        </div>
    </div>
</div>

<script>
    // Monthly Sales Total Chart
    const monthlySalesLabels = <?php echo json_encode(array_column($monthly_sales_data, 'month')); ?>;
    const monthlySalesData = <?php echo json_encode(array_column($monthly_sales_data, 'total_sales')); ?>;

    const monthlySalesChart = new Chart(document.getElementById('monthlySalesChart'), {
        type: 'bar',
        data: {
            labels: monthlySalesLabels,
            datasets: [{
                label: 'Total Sales',
                data: monthlySalesData,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return 'RM ' + value.toFixed(2); }
                    }
                }
            }
        }
    });

    // Specific Item Sales Chart
    const itemSalesLabels = <?php echo json_encode(array_column($item_sales_data, 'product_name')); ?>;
    const itemSalesData = <?php echo json_encode(array_column($item_sales_data, 'total_quantity_sold')); ?>;

    const itemSalesChart = new Chart(document.getElementById('itemSalesChart'), {
        type: 'bar',
        data: {
            labels: itemSalesLabels,
            datasets: [{
                label: 'Quantity Sold',
                data: itemSalesData,
                backgroundColor: 'rgba(153, 102, 255, 0.6)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>

</body>
</html>
