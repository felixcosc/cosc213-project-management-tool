<?php
// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();



// If the user is not logged in, they are redirected after this point, allowing me to use cleaner code later on
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?error=please_login");
    exit;
}

// Making sure I can use my $connection object 
require_once __DIR__ . '/reusable/db.php';

$user_id = $_SESSION['user_id'];

// Grabbing all projects of the particular user or where they are a member of the project. Going the bind parameter route again to prevent SQL injection
$stmt = $connection->prepare("
    SELECT *, 'owner' AS role FROM projects WHERE owner_id = ?
    UNION
    SELECT p.*, 'member' AS role FROM projects p 
    JOIN project_members pm ON p.id = pm.project_id WHERE pm.user_id = ?
");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$projects = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
 <link rel="stylesheet" href="reusable/styles.css">
 <meta charset="UTF-8">
 <title>My Projects</title>
</head>
<body>
<!-- Welcomes the user to their projects by username -->
<h1>Welcome to your Project Dashboard, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
<h2>My Projects</h2>
<?php if (empty($projects)): ?>
      <!-- If their project list is empty it lets them know -->
      <p>Your project list is empty.</p>
<?php else: ?>
      <!-- Options for editing or deleting their project. Tells them the name of each one -->
      <ul>
      <?php foreach ($projects as $project): ?>
          <li>
            <?php echo htmlspecialchars($project['title']); ?>
            <?php echo $project['role'] === 'owner' ? '(Owner)' : '(Member)'; ?>
            - <a href="view_project.php?id=<?php echo $project['id']; ?>">View Tasks</a>
            <?php if ($project['role'] === 'owner'): ?>
                - <a href="new_task.php?project_id=<?php echo $project['id']; ?>">Add New Task</a>
                - <a href="edit_project.php?id=<?php echo $project['id']; ?>">Edit Project</a>
                - <a href="delete_project.php?id=<?php echo $project['id']; ?>">Delete Project</a>
            <?php endif; ?>
          </li>
      <?php endforeach?>
      </ul>
<?php endif?>
<hr>
<!-- User input for making a new project -->
<h2>Create New Project</h2>
<form method="POST" action="project_add.php">
    <input type="text" name="title" placeholder="Project Name" required>
    <button type="submit">Add</button>
</form>
<!-- Navigation tools -->
<br>
<a href="logout.php">Logout</a>
</body>
</html>
