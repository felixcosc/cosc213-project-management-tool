<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// If the user is not logged in, they are redirected after this point, allowing me to use cleaner code later on
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=please_login");
    exit;
}

require_once __DIR__ . '/reusable/db.php';
$user_id = $_SESSION['user_id'];

// Grabbing all projects of that particular user. Going the bind parameter route again to prevent SQL injection
$stmt = $connection->prepare("SELECT * FROM projects where owner_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$projects = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<!DOCTYPE html>
<html>
<head>
 <title>My Projects</title>
</head>
<body>
<h1>Welcome to your projects, <?php echo htmlspecialchars($_SESSION['username']);?></h1>
<h2>My Projects</h2>
<?php if (empty($projects)):?>
      <p>Your project list is empty.</p>
<?php else:?>
      <ul><?php foreach ($projects as $project):?>
          <li><?php echo htmlspecialchars($project['project_name']);?>
          - <a href="edit_project.php?id=<?php echo $project['id']; ?>">Edit Project</a>
          <a href="delete_project.php?id=<?php echo $project['id']; ?>">Delete Project</a>
          </li>
          <?php endforeach;?>
     </ul>
<?php endif;?>
<hr>
<h2>Create New Project</h2>
<form method="POST" action="project_add.php">
    <input type="text" name="project_name" placeholder="Placeholder Name" required>
    <button type="submit">Add</button>
</form>
<br>
<a href="dashboard.php">Dashboard</a>
<a href="logout.php">Logout</a>
</body>
</html>


