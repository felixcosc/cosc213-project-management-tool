<?php
// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Giving me access to my $conn object
include __DIR__ . '/reusable/db.php';


// This makes sure that the code does not run until the information is submitted by the user
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // The code will stop running if either the username or password are left empty
    if (empty($username) || empty($password)) {
        echo "Username and password are required.";
        exit();
    }
    
    // Collecting id, username and  hashed password from the database. Making a statement object and preparing it to prevent against SQL injection 
    $sql = "SELECT id, username, password_hash FROM users WHERE username = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    // Making sure the username exists in the database before continuing
    if ($result->num_rows === 0) {
        echo "Invalid username or password.";
        exit();
    }

    $user = $result->fetch_assoc();
    // This statement compares the hashed password and user entered password to make sure it is safe to authenticate
    if (password_verify($password, $user['password_hash'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: project.php");
        exit();
    } else {
        echo "Invalid username or password.";
    }

    $stmt->close();
    $connection->close();
}
?>
