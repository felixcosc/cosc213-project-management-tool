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

// Connects to the database so we can use our $connection object
require_once __DIR__ . '/reusable/db.php';

$user_id = $_SESSION['user_id'];

// Makes sure a task ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Task ID missing.";
    exit;
}

$task_id = intval($_GET['id']);

// Fetch task and check if user is allowed to edit it (owner or project member)
$stmt = $connection->prepare("
    SELECT t.*, p.owner_id 
    FROM tasks t 
    JOIN projects p ON t.project_id = p.id 
    LEFT JOIN project_members pm ON p.id = pm.project_id AND pm.user_id = ?
    WHERE t.id = ? AND (p.owner_id = ? OR pm.user_id = ?)
");
$stmt->bind_param("iiii", $user_id, $task_id, $user_id, $user_id);
$stmt->execute();
$task = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$task) {
    echo "Task not found or you do not have permission to do this.";
    exit;
}

// Pulls all the project members
$stmt = $connection->prepare("
    SELECT u.id, u.username 
    FROM project_members pm 
    JOIN users u ON pm.user_id = u.id 
    WHERE pm.project_id = ?
");
// Variable for doing an admin check
$is_admin = ($task['owner_id'] == $user_id);

// Statment object and bind_param to prevent SQL injection
$stmt->bind_param("i", $task['project_id']);
$stmt->execute();
$members_result = $stmt->get_result();
$members = $members_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Will only run if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $assigned_to = !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : null;
    $status = $_POST['status'] ?? 'todo'; // <- changed to lowercase enum default
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;

    if (empty($title)) {
        echo "Task title cannot be empty.";
        exit;
    }

    // Updates the task in our database
    $stmt = $connection->prepare("
        UPDATE tasks 
        SET title = ?, description = ?, assigned_to = ?, status = ?, due_date = ? 
        WHERE id = ?
    ");
    $stmt->bind_param("sssssi", $title, $description, $assigned_to, $status, $due_date, $task_id);
    // Successfully edits the task
    if ($stmt->execute()) {
        header("Location: view_project.php?id=" . $task['project_id']);
        exit;
    } else {
        // Issue updating the task, tells the user why
        echo "Error updating task: " . $stmt->error;
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

<!-- Form for editing the task -->
<form method="POST" action="">
 <label>Task Title:</label>
 <input type="text" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required <?= (!$is_admin) ? 'disabled' : '' ?>><br>
 
 <!-- Adding a description -->
 <label>Description (optional):</label>
 <textarea name="description" <?= (!$is_admin) ? 'disabled' : '' ?>><?php echo htmlspecialchars($task['description']); ?></textarea><br>
 
 <!-- Assigning to a specific user -->
 <label>Assign to (optional):</label>
 <select name="assigned_to" <?= (!$is_admin) ? 'disabled' : '' ?>>
  <option value="">-- None --</option>
  <?php foreach ($members as $member): ?>
   <option value="<?php echo $member['id']; ?>" <?php if ($member['id'] == $task['assigned_to']) echo 'selected'; ?>>
    <?php echo htmlspecialchars($member['username']); ?>
   </option>
  <?php endforeach; ?>
 </select><br>
 
 <!-- Sets the status of the task -->
 <label>Status:</label>
 <select name="status">
  <option value="todo" <?php if ($task['status'] == 'todo') echo 'selected'; ?>>To-Do</option>
  <option value="in_progress" <?php if ($task['status'] == 'in_progress') echo 'selected'; ?>>In Progress</option>
  <option value="done" <?php if ($task['status'] == 'done') echo 'selected'; ?>>Done</option>
 </select><br>
 
 <!-- Sets the due date -->
 <label>Due Date (optional):</label>
 <input type="date" name="due_date" value="<?php echo htmlspecialchars($task['due_date']); ?>" <?= (!$is_admin) ? 'disabled' : '' ?>><br>

 <button type="submit">Update Task</button>
</form>

<a href="view_project.php?id=<?php echo $task['project_id']; ?>">Back to Project</a><br>
<a href="project.php">Project Dashboard</a>
</body>
</html>
