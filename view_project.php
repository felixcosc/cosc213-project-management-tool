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

require_once __DIR__ . '/reusable/db.php';

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Project ID missing.";
    exit;
}

$project_id = intval($_GET['id']);

// Verifies user ownership of project, going through prepare as security against SQL injection
$stmt = $connection->prepare("SELECT * FROM projects WHERE id = ? AND owner_id = ?");
$stmt->bind_param("ii", $project_id, $user_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Makes sure the project can be found before giving user access
if (!$project) {
    echo "Project not found.";
    exit;
}
// Pulls all tasks for the given project. Wildcarded tasks table as we are using all values
$stmt = $connection->prepare("SELECT t.*,  u.username AS assigned_username FROM tasks t LEFT JOIN users u ON t.assigned_to = u.id WHERE t.project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$tasks = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
 <meta charset="UTF-8">
 <title>Tasks for project <?php echo htmlspecialchars($project['title']); ?></title>
</head>
<body>
<h1>Tasks for project <?php echo htmlspecialchars($project['title']); ?></h1>
<a href="task_add.php?project_id=<?php echo $project_id; ?>">Add New Task</a><br>
<?php if (empty($tasks)): ?>
 <p>No tasks found for this project.</p>
<?php else: ?>
 <table border="1" cellpadding="5" cellspacing="0">
  <tr>
   <th>Title</th>
   <th>Description</th>
   <th>Assigned To</th>
   <th>Actions</th>
  </tr>
  <?php foreach ($tasks as $task): ?>
   <tr>
    <td><?php echo htmlspecialchars($task['title']); ?></td>
    <td><?php echo htmlspecialchars($task['description']); ?></td>
    <td><?php echo htmlspecialchars($task['assigned_username'] ?? 'Unassigned'); ?></td>
    <td>
     <a href="edit_task.php?id=<?php echo $task['id']; ?>">Edit</a>
     <a href="delete_task.php?id=<?php echo $task['id']; ?>&project_id=<?php echo $project_id; ?>">Delete</a>
    </td>
   </tr>
  <?php endforeach; ?>
 </table>
<?php endif; ?>
<br>
<a href="project.php">Back to Projects</a>
<a href="dashboard.php">Dashboard</a>
</body>
</html>
