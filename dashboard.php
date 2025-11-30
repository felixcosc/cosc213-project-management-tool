<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// If the user is not logged in, it will let them know they need to be before redirecting them to the login.
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to access the dashboard. Redirecting to login...";
    header("Refresh: 3, url=login.php");
    exit();
}

// If the user is logged in, this will grab their info
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html>
<head>
 <meta charset="UTF-8">
 <title>Dashboard></title>
</head>
<body>
<h1>Welcome to your Dashboard, <?php echo htmlspecialchars($username);?></h1>
<p>You are successfully logged in.</p>
<!-- Placeholder for later-->
Add projects here
Add logout button
</body>
</html>
