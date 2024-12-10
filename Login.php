<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Login.css">
    <title>Login</title>
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
  
<br>
<br>

    <div class="container">
        <div class="box form-box">
            <header>Login</header>
            <form action="" method="post">
                <div class="field input">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" autocomplete="off" required>
                </div>

                <div class="field buttons">
                    <input type="submit" class="btn" name="submit" value="Login" required>
                </div>
                <div class="links">
                    New to GHEE HIANG ? <a href="Registeration.php">Sign Up Now</a>
                </div>
            </form>

            <?php 
        include("php/connect.php");

    if(isset($_POST['submit'])){
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    // Check if the user is an admin
    $admin_result = mysqli_query($con, "SELECT * FROM admins WHERE username='$username'") or die("Query Error");
    $admin_row = mysqli_fetch_assoc($admin_result); 

    if(is_array($admin_row) && !empty($admin_row)){
        // Verify password
        if (password_verify($password, $admin_row['password'])) {
            // Start the session and store admin data
            session_start();
            $_SESSION['valid'] = 'admin';
            $_SESSION['username'] = $admin_row['username'];

            // Redirect to admin page
            header("Location: adminpage.php");
            exit();
        } else {
            echo "<div class='message'>
                    <p>Wrong Username or Password</p>
                  </div>";
        }
    } else {
        // Fetch user from the `users` table
        $user_result = mysqli_query($con, "SELECT * FROM users WHERE username='$username' AND password='$password'") or die("Query Error");
        $user_row = mysqli_fetch_assoc($user_result);

        if(is_array($user_row) && !empty($user_row)){
            // Start the session and store user data
            session_start();
            $_SESSION['valid'] = $user_row['username'];
            $_SESSION['username'] = $user_row['username'];

            // Redirect to user page
            header("Location: shopwithgheehiang.php");
            exit();
        } else {
            echo "<div class='message'>
                    <p>Wrong Username or Password</p>
                  </div>";
        }
    }
}
?>
        </div>
    </div>
</body>
</html>
