<?php
// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Makes sure the user is logged in, if not redirects them to login.html
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?error=please_login");
    exit;
}

// Connects to our database so we can use our $connection object
require_once __DIR__ . '/reusable/db.php';

$user_id = $_SESSION['user_id'];

// Only runs if user submits a form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Makes sure the username and project ID are both present
    if (!isset($_POST['project_id'], $_POST['username']) || empty($_POST['username'])) {
        echo "Project ID or username missing.";
        exit;
    }

    $project_id = intval($_POST['project_id']);
    $username = trim($_POST['username']);

    // Makes sure the user owns the project. Makes a statement object to prevent SQL injection
    $stmt = $connection->prepare("SELECT * FROM projects WHERE id = ? AND owner_id = ?");
    $stmt->bind_param("ii", $project_id, $user_id);
    $stmt->execute();
    $project = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$project) {
        echo "Project not found.";
        exit;
    }

    // Looks up the username
    $stmt = $connection->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Can not find the user
    if (!$user) {
        echo "User not found.";
        exit;
    }

    // Makes sure the user is not already a member of the project
    $stmt = $connection->prepare("SELECT * FROM project_members WHERE project_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $project_id, $user['id']);
    $stmt->execute();
    $existing_member = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existing_member) {
        echo "User is already a member of this project.";
        exit;
    }

    // If the user is not already a member, puts them in the project. Statement object to prevent SQL injection
    $stmt = $connection->prepare("INSERT INTO project_members (project_id, user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $project_id, $user['id']);
    $stmt->execute();
    $stmt->close();

    // Sends them to view_project.php
    header("Location: view_project.php?id=" . $project_id);
    exit;
}
?>
