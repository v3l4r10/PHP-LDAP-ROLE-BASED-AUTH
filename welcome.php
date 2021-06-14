<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
</head>
<body>
    <h1 class="my-5">Hello, <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>. Welcome .</h1>
    <p>
        <a href="logout.php">Sign Out</a>
    </p>
</body>
</html>