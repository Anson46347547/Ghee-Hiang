<?php
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

// Get the product_id from the URL
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if ($product_id > 0) {
    // Fetch product details from the database
    $sql = "SELECT * FROM products WHERE product_id = $product_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Product not found!";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f7f7f7;
            margin-top:180px;
        }

        .product-container {
            display: flex;
            align-items: flex-start;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            width: 100%;
        }

        img {
            width: 300px;
            margin-right: 20px;
            border-radius: 10px;
        }

        .product-details {
            display: flex;
            flex-direction: column;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        p {
            margin: 5px 0;
        }

        .form-group {
            margin-top: 10px;
        }

        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="product-container">
        <img src="<?php echo htmlspecialchars($product['product_image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
        <div class="product-details">
            <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
            <p><?php echo htmlspecialchars($product['product_description']); ?></p>
            <p>Price: RM<?php echo number_format($product['item_price'], 2); ?></p>
            <p>Stock: <?php echo $product['stock_quantity']; ?></p>

           <!-- Add to Cart form -->
    <form action="Totalcart.php" method="post" class="form-group">
    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
    
    <label for="quantity">Quantity:</label>
    
    <div style="display: flex; align-items: center; margin-bottom: 10px;">
        <button type="button" onclick="decreaseQuantity()" style="padding: 10px;">-</button>
        <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" style="text-align: center; width: 50px; margin: 0 10px;">
        <button type="button" onclick="increaseQuantity()" style="padding: 10px;">+</button>
    </div>
    
    <button type="submit">Add to Cart</button>
</form>

<script>
    function decreaseQuantity() {
        var quantity = document.getElementById("quantity");
        if (quantity.value > 1) {
            quantity.value--;
        }
    }

    function increaseQuantity() {
        var quantity = document.getElementById("quantity");
        if (quantity.value < <?php echo $product['stock_quantity']; ?>) {
            quantity.value++;
        }
    }
</script>
            
        </div>
    </div>
</body>
</html>
