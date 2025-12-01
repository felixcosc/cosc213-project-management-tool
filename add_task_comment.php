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

// Makes sure task_id and the comment exist
if (!isset($_POST['task_id'], $_POST['comment']) || empty(trim($_POST['comment']))) {
    echo "Task ID or comment missing.";
    exit;
}

$task_id = intval($_POST['task_id']);
$comment_text = trim($_POST['comment']);

// Makes sure the task exists and makes sure the user is actually allowed to comment
$stmt = $connection->prepare("
    SELECT t.id, t.project_id, p.owner_id
    FROM tasks t
    JOIN projects p ON t.project_id = p.id
    LEFT JOIN project_members pm ON p.id = pm.project_id AND pm.user_id = ?
    WHERE t.id = ? AND (p.owner_id = ? OR pm.user_id IS NOT NULL)
    LIMIT 1
");
// Statement object, preventing SQL injection
$stmt->bind_param("iii", $user_id, $task_id, $user_id);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();
$stmt->close();
// In case the task cannot be found or the user does not have permission
if (!$task) {
    echo "Task not found or you do not have permission.";
    exit;
}

// Adds the comment to the table
$stmt = $connection->prepare("
    INSERT INTO task_comments (task_id, user_id, comment) 
    VALUES (?, ?, ?)
");
$stmt->bind_param("iis", $task_id, $user_id, $comment_text);
if ($stmt->execute()) {
    // Sends user back to view_project.phpp
    header("Location: view_project.php?id=" . $task['project_id']);
    exit;
} else {
    // There was a problem, it will tell the user why
    echo "Error adding comment: " . $stmt->error;
}
$stmt->close();
?>
