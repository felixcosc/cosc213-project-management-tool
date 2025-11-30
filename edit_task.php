<?php

// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Makes sure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=please_login");
    exit;
}

// Connects us to the database making sure we can use the $connection object
require_once __DIR__ . '/reusable/db.php';

$user_id = $_SESSION['user_id'];

// Makes sure there is a task ID so the task can be properly edited
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Missing task ID.";
    exit;
}

$task_id = intval($_GET['id']);

// Pulls the task, using bind param and a statment object to prevent against SQL injection
$stmt = $connection->prepare("SELECT t.*, p.owner_id FROM tasks t JOIN projects p ON t.project_id = p.id WHERE t.id = ?");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Makes sure the task exists
if (!$task || $task['owner_id'] != $user_id) {
    echo "Task not found or you are not the owner.";
    exit;
}

$project_id = $task['project_id'];

// Pulls the project members of the task if the owner would like to reassign anyone
$stmt = $connection->prepare("SELECT u.id, u.username FROM project_members pm JOIN users u ON pm.user_id = u.id WHERE pm.project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$members_result = $stmt->get_result();
$members = $members_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Assigns all the user input to our variables
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $assigned_to = !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : null;
    
    // No empty tasks
    if (empty($title)) {
        echo "Task title cannot be empty.";
        exit;
    }
    // Safe update of task avoiding SQL injection
    $stmt = $connection->prepare("UPDATE tasks SET title = ?, description = ?, assigned_to = ? WHERE id = ?");
    $stmt->bind_param("ssii", $title, $description, $assigned_to, $task_id);
    // Successful update
    if ($stmt->execute()) {
        header("Location: view_project.php?id=" . $project_id);
        exit;
    } else {
        // An error occured, the user will be told why
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit Task: <?php echo htmlspecialchars($task['title']); ?></title>
</head>
<body>
<h1>Edit Task: <?php echo htmlspecialchars($task['title']); ?></h1>

<form method="POST" action="">
    <label>Task Title:</label>
    <input type="text" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required><br><br>

    <label>Description (optional):</label>
    <textarea name="description"><?php echo htmlspecialchars($task['description']); ?></textarea><br><br>

    <label>Assign to (optional):</label>
    <select name="assigned_to">
        <option value="">-- None --</option>
        <?php foreach ($members as $member): ?>
            <option value="<?php echo $member['id']; ?>" <?php echo ($task['assigned_to'] == $member['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($member['username']); ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <button type="submit">Update Task</button>
</form>

<br>
<a href="view_project.php?id=<?php echo $project_id; ?>">Back to Project</a>
<a href="dashboard.php">Dashboard</a>
</body>
</html>
