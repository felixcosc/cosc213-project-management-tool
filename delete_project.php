<?php
// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Making sure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?error=please_login");
    exit;
}

// Connecting to my database to use $connection object
require_once __DIR__ . '/reusable/db.php';

$user_id = $_SESSION['user_id'];

// Sends user back to dashboard if the project ID does not exist
if (!isset($_GET['id']) || empty($_GET['id'])) {
   header("Location: dashboard.php?deleted=true");
   exit;
}

// Security against SQL injection
$project_id = intval($_GET['id']);

// Extra security to make sure the deleted project actually belongs to the user
$stmt = $connection->prepare("DELETE FROM projects WHERE id = ? AND owner_id = ?");
$stmt->bind_param("ii", $project_id, $user_id);
$stmt->execute();

// If the project was deleted, send back to dashboard with query string true. If not, send back with false
if ($stmt->affected_rows > 0) {
    header("Location: dashboard.php?deleted=true");
} else {
    header("Location: dashboard.php?deleted=false");
}

$stmt->close();
?>
