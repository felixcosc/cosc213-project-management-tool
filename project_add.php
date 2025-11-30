<?php
// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Sends the user to the login page immediately if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?error=please_login");
    exit;
}

// Gives me access to my $connection object
require_once __DIR__ . '/reusable/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // The trim is to remove any empty spaces
    $project_name = isset($_POST['title']) ? trim($_POST['title']) : '';
    $owner_id = $_SESSION['user_id'];

    // Makes sure the project name is not empty when submitted
    if (empty($project_name)) {
        echo "Project name cannot be empty.";
        exit;
    }

    // Preparing my statement object to prevent SQL injection
    $stmt = $connection->prepare("INSERT INTO projects (title, owner_id) VALUES (?, ?)");
    $stmt->bind_param("si", $project_name, $owner_id);
    
    // Redirecting the user to the project.php page if successfully created
    if ($stmt->execute()) {
        header("Location: project.php");
        exit;
    } else {
        // If any issues occur it gives them an error why
        echo "There was an issue creating your project: " . $stmt->error;
    }

    $stmt->close();
    $connection->close();
}
?>
