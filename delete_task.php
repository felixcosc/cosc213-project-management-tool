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

// Connects to my database so we can use our $connection object
require_once __DIR__ . '/reusable/db.php';

$user_id = $_SESSION['user_id'];

// Makes sure the task and/or project ID are present before running
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['project_id']) || empty($_GET['project_id'])) {
    echo "Missing task or project ID.";
    exit;
}

$task_id = intval($_GET['id']);
$project_id = intval($_GET['project_id']);

// Makes sure the task being deleted is project owned by the user. Uses a statement object to protect against SQL injection
$stmt = $connection->prepare("SELECT t.id, p.owner_id FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.id = ?");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();
$stmt->close();
// In case the task in question cannot be found
if (!$task || $task['owner_id'] != $user_id) {
    echo "Task not found.";
    exit;
}

// Deletes the task
$stmt = $connection->prepare("DELETE FROM tasks WHERE id = ?");
$stmt->bind_param("i", $task_id);
if ($stmt->execute()) {
    header("Location: view_project.php?id=" . $project_id);
    exit;
} else {
    // Problem deleting the task. Tells the user what happened
    echo "Error deleting task: " . $stmt->error;
}
$stmt->close();
?>
