<?php
session_start();
include("php/connect.php");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: Login.php");
    exit();
}

// Handle the "Verify OTP" button click
if (isset($_POST['verify_otp'])) {
    $entered_otp = trim($_POST['otp']); // Get the entered OTP

    // Verify OTP
    if ($entered_otp == $_SESSION['otp']) {
        unset($_SESSION['otp']); // OTP is correct, remove it from session
        $_SESSION['otp_verified'] = true; // Mark OTP as verified
        header("Location: userprofile.php"); // Redirect to profile page
        exit();
    } else {
        echo "<div class='message'><p>Invalid OTP. Please try again.</p></div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Login.css">
    <title>Verify OTP</title>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Verify OTP</header>
            <form action="" method="post">
                <!-- Input for OTP -->
                <div class="field input">
                <label for="otp">Enter OTP</label>
                <input type="text" name="otp" id="otp" required placeholder="6 Digit Has Send To Your Gmail">
                </div>

                <!-- Button to verify OTP -->
                <div class="field">
                    <input type="submit" class="btn" name="verify_otp" value="Verify OTP">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
