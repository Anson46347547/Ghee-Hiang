<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gheehiang";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user ID from URL
$user_id = $_GET['user_id'];

// Fetch user data
$sql = "SELECT username, email, contact, password, address FROM users WHERE id='$user_id'";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Update user info
if (isset($_POST['update_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $password = $_POST['password'];
    $address = $_POST['address'];

    $update_sql = "UPDATE users SET 
        username='$username', 
        email='$email', 
        contact='$contact', 
        password='$password', 
        address='$address' 
        WHERE id='$user_id'";

    if ($conn->query($update_sql) === TRUE) {
        echo "<script>window.top.postMessage({action: 'close', message: 'Change successfully!'}, '*');</script>";
    } else {
        echo "<script>window.top.postMessage({action: 'close', message: 'Error updating user.'}, '*');</script>";
    }

    $conn->close();
    exit(); // Stop further execution
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
</head>
<body>
<style>
    body{
        text-align:center;
        
    }

    button{
        font-size:20px;
        border-radius:20px;
    }

    
</style>

<h1>Edit User Information</h1>

<form method="post">
    Username: <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>"><br><br>
    Email: <input type="text" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"><br><br>
    Contact: <input type="text" name="contact" value="<?php echo htmlspecialchars($user['contact']); ?>"><br><br>
    Password: <input type="text" name="password" value="<?php echo htmlspecialchars($user['password']); ?>"><br><br>
    Address: <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>"><br><br><br>
    <button type="submit" name="update_user">Update</button>
</form>

</body>
</html>
