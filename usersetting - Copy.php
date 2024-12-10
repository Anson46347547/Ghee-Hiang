<?php
// Database connection
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

// Fetch all registered users from the users table
$sql = "SELECT id, username, email, contact, password, address FROM users";
$result = $conn->query($sql);

if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    // First, delete related orders
    $delete_orders_sql = "DELETE FROM orders WHERE user_id='$user_id'";
    $conn->query($delete_orders_sql);

    // Then, delete the user
    $delete_user_sql = "DELETE FROM users WHERE id='$user_id'";
    if ($conn->query($delete_user_sql) === TRUE) {
        echo "User and related orders deleted successfully.";
        header("Refresh:0");
    } else {
        echo "Error deleting user: " . $conn->error;
    }
}

if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];

    $check_orders_sql = "SELECT * FROM orders WHERE user_id='$user_id'";
    $result = $conn->query($check_orders_sql);

    if ($result->num_rows > 0) {
        echo "<script>alert('Cannot delete user. They have existing orders.');</script>";
    } else {
        $delete_user_sql = "DELETE FROM users WHERE id='$user_id'";
        if ($conn->query($delete_user_sql) === TRUE) {
            echo "User deleted successfully.";
            header("Refresh:0");
        } else {
            echo "Error deleting user: " . $conn->error;
        }
    }
}

if (isset($_POST['add_admin'])) {
    $admin_username = $_POST['admin_username'];
    $admin_password = password_hash($_POST['admin_password'], PASSWORD_DEFAULT); // Hash the password for security

    $sql = "INSERT INTO admins (username, password) VALUES ('$admin_username', '$admin_password')";
    
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('New admin added successfully.');</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>    " . $conn->error;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="user.css">
    <title>User Settings</title>

</head>
<body>

<div class="container">
    <div class="sidebar">   
        <h2>GH ADMIN</h2>
        <ul>
            <li><a href="adminpage.php">Item Management</a></li>
            <li><a href="usersetting.php">User & Admin Settings</a></li>
            <li><a href="orderlist.php">Orders</a></li>
            <li><a href="report.php">Sales Reports</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1>All Users</h1>  
        <?php
        if ($result->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>Username</th><th>Contact</th><th>Manage</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['username']) . "</td>"; 
                echo "<td>" . htmlspecialchars($row['contact']) . "</td>";  
                echo "<td>";
                echo "<button onclick=\"openPopup('edituser.php?user_id=" . $row['id'] . "')\">Edit</button>";
                echo " ";
                echo "<form method='post' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this user?\");'>
                        <input type='hidden' name='user_id' value='" . $row['id'] . "'>
                        <button type='submit' name='delete_user'>Delete</button>
                      </form>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No users found.</p>";
        }
        ?>
        
        <div class="addadmin">
            <h2>Add New Admin</h2>
            <form method="post">
                <input type="text" id="admin_username" name="admin_username" placeholder="Username" required>
                <br>
                <input type="password" id="admin_password" name="admin_password" placeholder="Password" required>
                <br>
                <button type="submit" name="add_admin">Add Admin</button>
                <button type="button" onclick="openPopup('Totaladmin.php')">Show Total Admin</button>       
            </form>
        </div>
    </div>
</div>

<div id="overlay" class="overlay"></div>
<div id="popup" class="popup">
    <span class="close-btn" onclick="closePopup()">X</span>
    <iframe id="popup-frame" style="width: 100%; height: 400px; border: none;"></iframe>
    <p id="message" style="display: none;"></p>
</div>

<script>
    function openPopup(url) {
        document.getElementById('popup-frame').src = url;
        document.getElementById('overlay').classList.add('active');
        document.getElementById('popup').classList.add('active');
    }

    function closePopup() {
        document.getElementById('popup-frame').src = '';
        document.getElementById('overlay').classList.remove('active');
        document.getElementById('popup').classList.remove('active');
    }

    window.addEventListener('message', function(event) {
        if (event.origin !== window.location.origin) return;

        const data = event.data;
        if (data.action === 'close') {
            closePopup();
            alert(data.message);
            location.reload();
        }
    });
</script>

</body>
</html>
