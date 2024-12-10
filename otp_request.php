<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // PHPMailer

session_start();
include("php/connect.php");

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: Login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch current user information
$result = mysqli_query($con, "SELECT * FROM users WHERE username='$username'");
$user = mysqli_fetch_assoc($result);

// Function to send OTP to user's email
function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'leeanson3324@gmail.com'; // Replace with your Gmail
        $mail->Password = 'uljyahvawzkrxcij'; // Replace with your Gmail password or app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        //Recipients
        $mail->setFrom('leeanson3324@gmail.com', 'Ghee Hiang');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Verification Code';
        $mail->Body = 'Your OTP verification code is: ' . $otp;

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Handle the "Send OTP Code" button click
if (isset($_POST['send_otp'])) {
    $otp = rand(100000, 999999); // Generate a 6-digit OTP
    $_SESSION['otp'] = $otp; // Store OTP in session

    // Send OTP to registered email
    sendOTP($user['email'], $otp);
    header("Location: verify_otp.php"); // Redirect to OTP verification page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Login.css">
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Secure Verification Required</header>
            <form action="" method="post">
                <div class="field">
                    <input type="submit" class="btn" name="send_otp" value="Request OTP Code To Gmail">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
