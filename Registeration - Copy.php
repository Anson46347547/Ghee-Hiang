<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Login.css">
    <title>Register</title>
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
  <br>
  <br>
  <br>
  <br>  

      <div class="container">
        <div class="box form-box">

        <?php 
         
         include("php/connect.php");
         if(isset($_POST['submit'])){
            $username = $_POST['username'];
            $email = $_POST['email'];
            $contact = $_POST['contact'];
            $password = $_POST['password'];
            $address = $_POST['address'];

         //verifying the unique email

         $verify_query = mysqli_query($con,"SELECT Email FROM users WHERE Email='$email'");

         if(mysqli_num_rows($verify_query) !=0 ){
            echo "<div class='message'>
                      <p>This email is used, Try another One Please!</p>
                  </div> <br>";
            echo "<a href='javascript:self.history.back()'><button class='btn'>Go Back</button>";
         }
         else{

            mysqli_query($con,"INSERT INTO users(username,email,contact,password,address) VALUES('$username','$email','$contact','$password','$address')") or die("Erroe Occured");

            echo "<div class='message'>
                      <p>Registration successfully!</p>
                  </div> <br>";
            echo "<a href='Login.php'><button class='btn'>Login Now</button>";
         

         }

         }else{
         
        ?>

            <header>Sign Up</header>
            <form action="" method="post">
                <div class="field input">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label for="email">Email</label>
                    <input type="text" name="email" id="email" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label for="contact">Contact</label>
                    <input type="text" name="contact" id="contact" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" autocomplete="off" required>
                </div>

                <div class="field input">
                    <label for="password">Address</label>
                    <input type="text" name="address" id="address" autocomplete="off" required>
                </div>

                <div class="field">
                    
                    <input type="submit" class="btn" name="submit" value="Register" required>
                </div>
                <div class="links">
                    Already a member of GHEE HIANG ? <a href="Login.php">Sign In</a>
                </div>
            </form>
        </div>
        <?php } ?>
      </div>
</body>
</html>