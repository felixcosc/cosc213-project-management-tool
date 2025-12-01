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

// Connecting to the database
require_once __DIR__ . '/reusable/db.php';

// Pulling the user id from the session
$user_id = $_SESSION['user_id'];

// If the project ID is not there
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Missing Project ID.";
    exit;
}

// Using intval as extra security
$project_id = intval($_GET['id']);

// Setting up a statement object and bind parameter to prevent SQL injection
$stmt = $connection->prepare("SELECT * FROM projects WHERE id = ? AND owner_id = ?");
$stmt->bind_param("ii", $project_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();
$stmt->close();

// If the project cannot be found or the user does not have permission
if (!$project) {
    echo "Project not found or you do not have permission to do this.";
    exit;
}

// Trimming the new project name to avoid white spaces
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_title = trim($_POST['title']);
    // No empty project title
    if (empty($new_title)) {
        echo "Project title can't be empty.";
    } else {
        $stmt = $connection->prepare("UPDATE projects SET title = ? WHERE id = ? AND owner_id = ?");
        $stmt->bind_param("sii", $new_title, $project_id, $user_id);
        if ($stmt->execute()) {
            header("Location: project.php");
            exit;
        } else {
            // Error, telling the user what went wrong
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
 <meta charset="UTF-8">
 <!-- Telling the user what project they are editing -->
 <title>Edit Project - <?php echo htmlspecialchars($project['title']); ?></title>
</head>
<body>
<h1>Edit Project</h1>

<form method="POST" action="">
 <label for="title">Project Title:</label>
 <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($project['title']); ?>" required>
 <button type="submit">Save Changes</button>
</form>
<br>
<!-- Nav tools -->
<a href="project.php">Back to Projects</a>
<a href="logout.php">Logout</a>
</body>
</html>
