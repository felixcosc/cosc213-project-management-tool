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

// Makes sure we received the project_id
if (!isset($_GET['project_id']) || empty($_GET['project_id'])) {
    echo "Project ID missing.";
    exit;
}

// Extra security
$project_id = intval($_GET['project_id']);

// Statement object, security against SQL injection
$stmt = $connection->prepare("SELECT * FROM projects WHERE id = ? AND owner_id = ?");
$stmt->bind_param("ii", $project_id, $user_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

// If the project is not found or the user does not have permission the script will not run
if (!$project) {
    echo "Project not found or you do not have permission to do this.";
    exit;
}

// Goes through and checks users who are part of the project
$stmt = $connection->prepare("
    SELECT u.id, u.username
    FROM project_members pm
    JOIN users u ON pm.user_id = u.id
    WHERE pm.project_id = ?
");

// Bind parameter as an extra layer of security against SQL injection
$stmt->bind_param("i", $project_id);
$stmt->execute();
$members_result = $stmt->get_result();
// $members will become an associative array
$members = $members_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Will only run if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // All submitted info is read and assigned
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $assigned_to = !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : null;
    $status = $_POST['status'] ?? 'todo'; // default to todo
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
    
    // No empty titles
    if (empty($title)) {
        echo "Task title cannot be empty.";
        exit;
    }
    
    // Creates a new task and creates an object
    $stmt = $connection->prepare("
        INSERT INTO tasks (project_id, title, description, assigned_to, status, due_date, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("isssss", $project_id, $title, $description, $assigned_to, $status, $due_date);
    
    // If the task is created successfully, redirects to view_project.php with the project id in the query string
    if ($stmt->execute()) {
        header("Location: view_project.php?id=" . $project_id);
        exit;
    } else {
     
      //Unsuccessful  
      echo "Error creating task: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
 <meta charset="UTF-8">
 <title>Add Task to <?php echo htmlspecialchars($project['title']); ?></title>
</head>
<body>
<h1>Add Task to <?php echo htmlspecialchars($project['title']); ?></h1>
<form method="POST" action="">
 <label>Task Title:</label>
 <input type="text" name="title" required><br>
 
 <label>Description (optional):</label>
 <textarea name="description"></textarea><br><br>

 <label>Assign to (optional):</label>
 <!-- This block shows all members of a project so they can be added -->
 <select name="assigned_to">
  <option value="">-- None --</option>
  <?php foreach ($members as $member): ?>
   <option value="<?php echo $member['id']; ?>">
    <?php echo htmlspecialchars($member['username']); ?>
   </option>
  <?php endforeach; ?>
 </select><br><br>

 <label>Status:</label>
 <select name="status">
  <option value="todo">To-Do</option>
  <option value="in_progress">In Progress</option>
  <option value="done">Done</option>
 </select><br><br>

 <label>Due Date (optional):</label>
 <input type="date" name="due_date"><br><br>

 <button type="submit">Add Task</button>
</form>

<br><a href="view_project.php?id=<?php echo $project_id; ?>">Back to Project</a><br>
<a href="logout.php">Logout</a>
</body>
</html>
