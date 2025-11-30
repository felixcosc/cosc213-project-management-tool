<?php
// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Included so I can use my $connection object
include __DIR__ . '/reusable/db.php';

// Stores user input
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    
    // Hashing the password immediately after user input
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    //Preventing duplicate username or email addresses from happening
    $check = $connection->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();
    
    // Makes sure the user or email does not exist already
    if ($check->num_rows > 0) {
       echo "Username or email already taken.";
       exit();
    }

    // Preventing SQL injection by not storing the values here, and instead using placeholders
    $sql = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
    
    // Statement object creation.
    $stmt = $connection->prepare($sql);
    
    // Using bind_param as another layer of security, so SQL injection may not take place, only data
    $stmt->bind_param("sss", $username, $email, $hashed_password);

    //If all the user input is accepted, it will take them to the login page. If not, it will explain why
    if ($stmt->execute()) {
        echo "Successfully registered!";
        header("Location: login.html");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
    $check->close();
    $connection->close();
}
?>
