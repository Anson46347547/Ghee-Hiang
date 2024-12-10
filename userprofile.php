<?php
session_start();
include("php/connect.php");

// Check if OTP was verified
if (!isset($_SESSION['otp_verified']) || $_SESSION['otp_verified'] !== true) {
    header("Location: otp_verification.php"); // Redirect to OTP page
    exit();
}

// Now you can proceed to load the profile page content after OTP verification
$username = $_SESSION['username'];

// Fetch current user information
$result = mysqli_query($con, "SELECT * FROM users WHERE username='$username'");
$user = mysqli_fetch_assoc($result);

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: Login.php");
    exit();
}

// Handle profile update form submission
if (isset($_POST['update'])) {
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];
    $new_contact = $_POST['contact'];
    $new_password = $_POST['password'];
    $new_address = $_POST['address'];

    // Update user information
    $update_query = "UPDATE users SET username='$new_username', email='$new_email', contact='$new_contact', password='$new_password', address='$new_address' WHERE username='$username'";
    mysqli_query($con, $update_query) or die("Error Occurred");

    // Update session username if changed
    if ($new_username != $username) {
        $_SESSION['username'] = $new_username;
    }

    echo "<div class='message'><p>Information updated successfully!</p></div>";
    
    // Reload the user information
    $result = mysqli_query($con, "SELECT * FROM users WHERE username='$new_username'");
    $user = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Login.css">
    <title>User Profile</title>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Profile</header>
            <form action="" method="post">
                <div class="field input">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>

                <div class="field input">
                    <label for="email">Email</label>
                    <input type="text" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="field input">
                    <label for="contact">Contact</label>
                    <input type="text" name="contact" id="contact" value="<?php echo htmlspecialchars($user['contact']); ?>" required>
                </div>

                <div class="field input">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" value="<?php echo htmlspecialchars($user['password']); ?>" required>
                </div>

                <div class="field input">
                    <label for="address">Address</label>
                    <input type="text" name="address" id="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>
                </div>

                <div class="field">
                    <input type="submit" class="btn" name="update" value="Save Changes">                   
                </div>

                <div class="BACK">
                    <a href="shopwithgheehiang.php" style="text-decoration:none;">Continue Shop with Ghee Hiang</a>
                </div>
                
            </form>
        </div>
    </div>
</body>
</html>