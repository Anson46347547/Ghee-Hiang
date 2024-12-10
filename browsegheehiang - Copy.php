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

    // Fetch products and group them by category
    $sql = "SELECT * FROM products ORDER BY category, product_id";
    $result = $conn->query($sql);

    $products_by_category = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $category = $row['category'];
            if (!isset($products_by_category[$category])) {
                $products_by_category[$category] = array();
            }
            $products_by_category[$category][] = $row;
        }
    }

    // Predefined category order
    $predefined_categories = array('Pastries', 'Cookies', 'sesameoil', 'Coffee & Tea', 'Chocolate Cookies', 'Sesame chili sauces');

    // Fetch categories from the database
    $category_sql = "SELECT DISTINCT category FROM products";
    $category_result = $conn->query($category_sql);
    $database_categories = array();

    // Add database categories to the array
    if ($category_result->num_rows > 0) {
        while ($category_row = $category_result->fetch_assoc()) {
            $database_categories[] = $category_row['category'];
        }
    }

    // Merge predefined categories with database categories (preserving predefined order)
    $category_order = array_merge($predefined_categories, array_diff($database_categories, $predefined_categories));

    // Sort the products by this predefined and dynamic category order
    $sorted_products_by_category = array();
    foreach ($category_order as $category) {
        if (isset($products_by_category[$category])) {
            $sorted_products_by_category[$category] = $products_by_category[$category];
        }
    }
    ?>
        
<!DOCTYPE html>
<head>
<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="shopss.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js"></script>
    <script defer src="function.js"></script>
        
</head>

<body>
    <!--header-->
    <div class="header">
      <button id="menu-toggle" class="menu-toggle">
          <span></span>
          <span></span>
          <span></span>
      </button>

      <ul class="menu">
      <li><a href="browsescroll.html">About Us</a></li>
        <li><a href="browseevent.html">News/Events</a></li>
        <li><a href="browsehome.html"><img src="dragon.gif" alt="logo" width="343" height="200"></a></li>
        <li><a href="browsegheehiang.php">Shop with Ghee Hiang</a></li>
        <li><a href="browsefindus.html">Find Us </a></li>
      </ul>
  </div>

  <div id="carouselExampleSlidesOnly" class="carousel slide" data-bs-ride="carousel">
  
  <br>
  <br>
  <br>
  <br>

  <div class="blank-space"></div>
    
  <div class="user-actions">
    
    <div class="logout">
        <a href="Login.php" class="logout-button">Login</a>
    </div>
</div>

    <br>
    <br>    

  <!-- Tab Menu Section -->
  <div class="tab-menu">
        <ul>
            <?php
            $tab_index = 1;
            foreach ($sorted_products_by_category as $category => $products) {
                echo '<li><a class="tab-a' . ($tab_index == 1 ? ' active-a' : '') . '" data-id="tab' . $tab_index . '">' . htmlspecialchars($category) . '</a></li>';
                $tab_index++;
            }
            ?>
            </ul>
   
</div>
    <!-- Tab Content Section -->
     <?php
    $tab_index = 1;
    foreach ($sorted_products_by_category as $category => $products) {
        echo '<div class="tab' . ($tab_index == 1 ? ' tab-active' : '') . '" data-id="tab' . $tab_index . '">';
        
        foreach ($products as $product) {
            echo '<div class="other-element">';
            echo '<div class="item-pic">';
            echo '<img src="' . htmlspecialchars($product['product_image']) . '" alt="' . htmlspecialchars($product['product_name']) . '" title="' . htmlspecialchars($product['product_name']) . '">';
        
            // Check if the product is in stock
            if ($product['stock_quantity'] > 0) {
                // If in stock, show the Add to Cart button
                echo '<a href="Login.php?product_id=' . $product['product_id'] . '" class="add-to-cart-button">Add to Cart</a>';
            } else {
                // If out of stock, show a button that triggers a popup message
                echo '<a href="#" onclick="alert(\'No stock available right now\'); return false;" class="add-to-cart-button out-of-stock">Out of Stock</a>';
            }
        
            echo '</div>';  
        
            echo '<label class="label">' . htmlspecialchars($product['product_name']) . '</label>';
            echo '<div class="item-description">' . htmlspecialchars($product['product_description']) . '</div>';
            echo '<div class="item-price">RM' . number_format($product['item_price'], 2) . '</div>';
            echo '<div class="stock-quantity">Available Stock: ' . $product['stock_quantity'] . '</div>';
            echo '</div>';
        }

        echo '</div>'; // Close tab div
        
        $tab_index++;
    }
?>
            </div>
        </div>
    </div>
</div>  

  <div class="blank-space"></div>

  <footer>
      <div class="waves">
          <div class="wave" id="wave1"></div>
          <div class="wave" id="wave2"></div>
          <div class="wave" id="wave3"></div>
          <div class="wave" id="wave4"></div>
      </div>
      <ul class="social_icon">
          <li><a href="#"><ion-icon name="logo-facebook"></ion-icon></a></li>
          <li><a href="#"><ion-icon name="logo-instagram"></ion-icon></a></li>
          <li><a href="#"><ion-icon name="logo-youtube"></ion-icon></a></li>
          <li><a href="#"><ion-icon name="logo-whatsapp"></ion-icon></a></li>
      </ul>
      <ul class="menu">
      <li><a href="browsescroll.html">About Us</a></li>
        <li><a href="browseevent.html">News/Events</a></li>
        <li><a href="browsegheehiang.php">Shop With Ghee Hiang</a></li>
        <li><a href="browsefindus.html">Location</a></li>
      </ul>
      <p>Ghee Hiang | All Rights Reserved.</p>
  </footer>
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

  <script>

    const tabMenu = document.querySelector('.tab-menu');
    tabMenu.addEventListener('wheel', (evt) => {
        evt.preventDefault();
        tabMenu.scrollLeft += evt.deltaY;
    });

    // JavaScript code to handle tab switching
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.tab-a');
        const tabContents = document.querySelectorAll('.tab');

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const id = this.getAttribute('data-id');

                // Remove active classes
                tabs.forEach(t => t.classList.remove('active-a'));
                tabContents.forEach(tc => tc.classList.remove('tab-active'));

                // Add active classes to current tab and content
                this.classList.add('active-a');
                document.querySelector('.tab[data-id="' + id + '"]').classList.add('tab-active');
            });
        });
    });
</script>


<?php
$conn->close();
?>

</body>
</html>