<?php
    // Database connection parameters
    $servername = "localhost";
    $username = "root";
    $password = ""; // Update with your database password if any
    $dbname = "gheehiang";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Initialize success message flag
    $success = false;

    // Handle form submission to delete an item
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
        $product_id = intval($_POST['product_id']); // The product ID to delete
        $sql_delete = "DELETE FROM products WHERE product_id = $product_id";
        if (!$conn->query($sql_delete)) {
            echo "Error deleting product: " . $conn->error;
        } else {
            // Redirect to refresh the page
            header("Location: adminpage.php");
            exit();
        }
    }

    // Determine the selected category, default to 'Pastries' if none is selected
    $default_category = 'Pastries';
    $selected_category = isset($_POST['category']) ? $_POST['category'] : $default_category;

    // Fetch categories for the dropdown
    $category_sql = "SELECT DISTINCT category FROM products";
    $category_result = $conn->query($category_sql);

    // Fetch products based on selected category
    $product_list = [];
    if ($selected_category) {
        $sql_products = "SELECT product_id, product_name FROM products WHERE category = '$selected_category'";
        $product_result = $conn->query($sql_products);
        if ($product_result->num_rows > 0) {
            while ($row = $product_result->fetch_assoc()) {
                $product_list[] = $row;
            }
        }
    }

    // Product details (used to pre-fill form when an item is selected)
    $selected_product = null;
    if (isset($_POST['product_id']) && !empty($_POST['product_id'])) {
        $product_id = intval($_POST['product_id']);
        $sql_product_details = "SELECT * FROM products WHERE product_id = $product_id";
        $product_details_result = $conn->query($sql_product_details);
        if ($product_details_result->num_rows > 0) {
            $selected_product = $product_details_result->fetch_assoc();
        }
    }

    // Handle form submission to update an item
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_item'])) {
    $product_id = intval($_POST['product_id']);
    $updated_name = $conn->real_escape_string($_POST['name']);
    $updated_description = $conn->real_escape_string($_POST['description']);
    $updated_price = floatval($_POST['price']);
    $updated_quantity = intval($_POST['quantity']);

    $sql_update = "UPDATE products SET product_name='$updated_name', product_description='$updated_description', item_price=$updated_price, stock_quantity=$updated_quantity WHERE product_id=$product_id";
    
    if (!$conn->query($sql_update)) {
        echo "Error updating product: " . $conn->error;
    } else {
        // Redirect to the same page after updating to refresh
        header("Location: adminpage.php");
        exit();
    }
}

    // Handle form submission to add a new item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_new_item'])) {
    $new_product_name = $conn->real_escape_string($_POST['new_product_name']);
    $new_product_description = $conn->real_escape_string($_POST['new_product_description']);
    $new_product_price = floatval($_POST['new_product_price']);
    $new_stock_quantity = intval($_POST['new_stock_quantity']);

    // Check if a new category is being added or an existing one is selected
    if ($_POST['existing_category'] === 'add_new_category' && !empty($_POST['new_category'])) {
        $new_category = $conn->real_escape_string($_POST['new_category']);
    } else {
        $new_category = $conn->real_escape_string($_POST['existing_category']);
    }

    // Handle file upload for product image
    $new_product_image = $target_dir . basename($_FILES["new_product_image"]["name"]);
    if (move_uploaded_file($_FILES["new_product_image"]["tmp_name"], $new_product_image)) {
        echo "File successfully uploaded.";
    } else {
        echo "Failed to upload file.";
    }

    $new_product_price = floatval($_POST['new_product_price']); // Retrieve price

    // Insert the new product into the database
   $sql_add = "INSERT INTO products (product_name, product_description, category, product_image, stock_quantity, item_price)
        VALUES ('$new_product_name', '$new_product_description', '$new_category', '$new_product_image', $new_stock_quantity, $new_product_price)";

    if (!$conn->query($sql_add)) {
        echo "Error adding new product: " . $conn->error;
    } else {
        // Redirect to refresh the page
        header("Location: adminpage.php");
        exit();
    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Ad.css">
    <title>Admin Page</title>

    <script>
        function toggleAdjustmentForm() {
            var selectedItem = document.getElementById('selected_item').value;
            var adjustmentForm = document.getElementById('adjustment_form');
            if (selectedItem) {
                adjustmentForm.style.display = 'block';
            } else {
                adjustmentForm.style.display = 'none';
            }
        }

        window.onload = function() {
            toggleAdjustmentForm(); // Hide form initially if no item is selected
        };

        function checkCategorySelection() {
        var categorySelect = document.getElementById('existing_category');
        var newCategorySection = document.getElementById('new_category_section');
        if (categorySelect.value === 'add_new_category') {
            newCategorySection.style.display = 'block';
        } else {
            newCategorySection.style.display = 'none';
        }
    }

    function increaseQuantity(id) {
        var quantityField = document.getElementById('quantity_' + id);
        var currentValue = parseInt(quantityField.value);
        if (!isNaN(currentValue)) {
            quantityField.value = currentValue + 1;
        }
    }

    function decreaseQuantity(id) {
        var quantityField = document.getElementById('quantity_' + id);
        var currentValue = parseInt(quantityField.value);
        if (!isNaN(currentValue) && currentValue > 0) {
            quantityField.value = currentValue - 1;
        }
    }

    window.onload = function() {
        checkCategorySelection(); // Hide/show new category input on load
    };

    </script>
</head>

<body>

    <div class="sidebar">
        <h2>GH ADMIN</h2>
        <ul>
            <li><a href="adminpage.php">Item Management</a></li>
            <li><a href="usersetting.php">User & Admin Settings</a></li>
            <li><a href="orderlist.php">Orders</a></li>
            <li><a href="report.php">Sales Reports</a></li>
        </ul>
    </div>

    <div class="content">
        <h2>Stock Control</h2>

       <!-- Category Filter Dropdown -->
<form method="post" action="adminpage.php">
    <label for="category">Selected Category:</label>
    <select id="category" name="category" onchange="this.form.submit()">
        <?php
        if ($category_result->num_rows > 0) {
            while ($category_row = $category_result->fetch_assoc()) {
                $category_name = htmlspecialchars($category_row['category']);
                $selected = $category_name == $selected_category ? ' selected' : '';
                echo "<option value=\"$category_name\"$selected>$category_name</option>";
            }
        }
        ?>
    </select>
</form>

<!-- Selected Item Dropdown (dependent on the chosen category) -->
<?php if (count($product_list) > 0): ?>
<form method="post" action="adminpage.php">
    <input type="hidden" name="category" value="<?php echo $selected_category; ?>"> <!-- Retain selected category -->
    
    <label for="selected_item">Selected Item:</label>
    <select id="selected_item" name="product_id" onchange="this.form.submit()">
        <option value="">Select an item</option>
        <?php foreach ($product_list as $product): ?>
            <option value="<?php echo $product['product_id']; ?>" <?php echo ($selected_product && $selected_product['product_id'] == $product['product_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($product['product_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php endif; ?>

       <!-- Adjustment and Changes Form (Pre-filled with the selected product's details) -->
<?php if ($selected_product): ?>
    <form method="post" action="adminpage.php">
    <input type="hidden" name="product_id" value="<?php echo $selected_product['product_id']; ?>">

    <h3>Product Details</h3>
    <br>
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($selected_product['product_name']); ?>" required><br><br>

    <label for="description">Description:</label>
    <textarea id="description" name="description" required><?php echo htmlspecialchars($selected_product['product_description']); ?></textarea><br><br>

    <label for="price">Price:</label>
    <input type="text" id="price" name="price" value="<?php echo htmlspecialchars($selected_product['item_price']); ?>" required><br><br>

    <label for="quantity">Stock Quantity:</label>
    <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($selected_product['stock_quantity']); ?>" required><br><br>

    <button type="submit" name="update_item" onclick="return confirm('Are you sure you want to make this changes?');" >Save Changes</button>
    <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete this item?');">Delete</button>
</form>
<?php endif; ?>

      <?php if (count($product_list) > 0): ?>
    <!-- existing code to display products -->
<?php endif; ?>
    </div>

    <div class="contents">
                <h2>Add New Item</h2>
                <span id="error-message" style="color: red;"></span>

                <form id="add-new-item-form" method="post" action="adminpage.php" enctype="multipart/form-data" onsubmit="return validateForm() && confirm('Are you sure you want to add this item?');">
    <label for="new_product_name">Name:</label>
    <input type="text" id="new_product_name" name="new_product_name" required><br><br>

    <label for="new_product_description">Description:</label>
    <textarea id="new_product_description" name="new_product_description" required></textarea><br><br>

    <label for="existing_category">Category:</label>
    <select id="existing_category" name="existing_category" onchange="checkCategorySelection()" required>
        <option value="">Select A Category</option>
        <option value="add_new_category">ADD NEW CATEGORY</option>
        <?php
        $category_result->data_seek(0); // Reset pointer to reuse the result
        while ($row = $category_result->fetch_assoc()): ?>
            <option value="<?php echo htmlspecialchars($row['category']); ?>"><?php echo htmlspecialchars($row['category']); ?></option>
        <?php endwhile; ?>
    </select><br><br>

    <!-- New category section (hidden unless user chooses "ADD NEW CATEGORY") -->
    <div id="new_category_section" style="display:none;">
        <label for="new_category">New Category Name:</label>
        <input type="text" id="new_category" name="new_category"><br><br>
    </div>

    <label for="new_product_image">Product Image:</label>
    <input type="file" id="new_product_image" name="new_product_image" accept="image/*"><br><br>

    <label for="new_product_price">Product Price:</label>
    <input type="text" id="new_product_price" name="new_product_price" required><br><br>

    <label for="new_stock_quantity">Stock Quantity:</label>
    <div class="quantity-control">
        <button type="button" onclick="decreaseQuantity('new')">-</button>
        <input type="text" name="new_stock_quantity" id="quantity_new" value="0" required>
        <button type="button" onclick="increaseQuantity('new')">+</button>
    </div><br>

    <button type="submit" name="add_new_item" class="save-button" style="background-color:#2c3e50">Add</button>
</form>
            </div>
        </div>

</body>
</html>
