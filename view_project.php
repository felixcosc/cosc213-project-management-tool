<?php
// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Makes sure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?error=please_login");
    exit;
}

// Connects to our database so we can use our $connection object
require_once __DIR__ . '/reusable/db.php';

$user_id = $_SESSION['user_id'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Project ID missing.";
    exit;
}

$project_id = intval($_GET['id']);

// Makes sure the user is either the owner or a member of the project before giving them access
$stmt = $connection->prepare("
SELECT * FROM (
    (SELECT * FROM projects WHERE id = ? AND owner_id = ?)
    UNION
    (SELECT p.* FROM projects p
     JOIN project_members pm ON p.id = pm.project_id
     WHERE p.id = ? AND pm.user_id = ?)
) AS combined
LIMIT 1
");
$stmt->bind_param("iiii", $project_id, $user_id, $project_id, $user_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) {
    echo "Project not found or you do not have permission to do this.";
    exit;
}

// Pull tasks for this project
$stmt = $connection->prepare("
SELECT t.*, u.username AS assigned_username 
FROM tasks t 
LEFT JOIN users u ON t.assigned_to = u.id 
WHERE t.project_id = ?
");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$tasks_raw = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Groups all of the tasks based on their status
$tasks = [];
foreach ($tasks_raw as $task) {
    $status = $task['status'] ?? 'todo';
    if (!isset($tasks[$status])) {
        $tasks[$status] = [];
    }
    $tasks[$status][] = $task;
}

// Pull project members
$stmt = $connection->prepare("
SELECT u.id, u.username 
FROM project_members pm 
JOIN users u ON pm.user_id = u.id 
WHERE pm.project_id = ?
");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$members_result = $stmt->get_result();
$members = $members_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Pull comments for tasks in this project
$stmt = $connection->prepare("
SELECT tc.*, u.username 
FROM task_comments tc 
JOIN users u ON tc.user_id = u.id 
WHERE tc.task_id IN (
    SELECT id FROM tasks WHERE project_id = ?
)
ORDER BY tc.created_at ASC
");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$comments_result = $stmt->get_result();
$comments_raw = $comments_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Organize comments by task_id
$comments = [];
foreach ($comments_raw as $comment) {
    $comments[$comment['task_id']][] = $comment;
}
?>

<!DOCTYPE html>
<html>
<head>
 <meta charset="UTF-8">
 <title>Project <?php echo htmlspecialchars($project['title']); ?></title>
</head>
<body>
<h1>Project: <?php echo htmlspecialchars($project['title']); ?></h1>

<h2>Tasks</h2>
<a href="new_task.php?project_id=<?php echo $project_id; ?>">Add New Task</a><br>

<?php if (empty($tasks_raw)): ?>
 <p>No tasks found for this project.</p>
<?php else: ?>
    <?php foreach ($tasks as $status => $status_tasks): ?>
        <h3><?php echo htmlspecialchars($status); ?></h3>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>Title</th>
                <th>Description</th>
                <th>Assigned To</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Actions</th>
                <th>Comments</th>
            </tr>
            <?php foreach ($status_tasks as $task): ?>
                <tr>
                    <td><?php echo htmlspecialchars($task['title']); ?></td>
                    <td><?php echo htmlspecialchars($task['description']); ?></td>
                    <td><?php echo htmlspecialchars($task['assigned_username'] ?? 'Unassigned'); ?></td>
                    <td><?php echo htmlspecialchars($task['status']); ?></td>
                    <td><?php echo htmlspecialchars($task['due_date'] ?? ''); ?></td>
                    <td>
                        <a href="edit_task.php?id=<?php echo $task['id']; ?>">Edit</a>
                        <a href="delete_task.php?id=<?php echo $task['id']; ?>&project_id=<?php echo $project_id; ?>">Delete</a>
                    </td>
                    <td>
                        <?php if (!empty($comments[$task['id']])): ?>
                            <ul>
                            <?php foreach ($comments[$task['id']] as $comment): ?>
                                <li><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong> <?php echo htmlspecialchars($comment['comment']); ?></li>
                            <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <em>No comments</em>
                        <?php endif; ?>

                        <!-- Add comment form -->
                        <form method="POST" action="add_task_comment.php">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <input type="text" name="comment" placeholder="Add a comment" required>
                            <button type="submit">Post</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <br>
    <?php endforeach; ?>
<?php endif; ?>

<h2>Project Members</h2>
<ul>
<?php foreach ($members as $member): ?>
    <li><?php echo htmlspecialchars($member['username']); ?></li>
<?php endforeach; ?>
</ul>

<h3>Add Member</h3>
<form method="POST" action="add_project_member.php">
    <input type="hidden" name="project_id" value="<?php echo $project_id; ?>">
    <input type="text" name="username" placeholder="Username of member" required>
    <button type="submit">Add Member</button>
</form>

<br>
<a href="project.php">Back to Projects</a>
<a href="logout.php">Logout</a>
</body>
</html>
