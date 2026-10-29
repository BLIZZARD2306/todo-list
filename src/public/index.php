<?php
session_start();
require __DIR__ . '/../config.php';

// A token prevents other websites from submitting changes to your list.
if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

$error = '';

// CREATE, UPDATE, and DELETE: handle the submitted form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['token'], $token)) {
        http_response_code(403);
        exit('Invalid form token. Refresh the page and try again.');
    }

    $action = $_POST['action'] ?? '';
    $id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

    if ($action === 'add') {
        $title = is_string($_POST['title'] ?? null) ? trim($_POST['title']) : '';
        if ($title === '' || strlen($title) > 200) {
            $error = 'Please enter a task. If it is too long, shorten it and try again.';
        } else {
            $statement = $pdo->prepare('INSERT INTO tasks (title) VALUES (?)');
            $statement->execute([$title]);
        }
    } elseif ($action === 'complete' && $id > 0) {
        $statement = $pdo->prepare('UPDATE tasks SET completed = 1 WHERE id = ?');
        $statement->execute([$id]);
    } elseif ($action === 'delete' && $id > 0) {
        $statement = $pdo->prepare('DELETE FROM tasks WHERE id = ?');
        $statement->execute([$id]);
    }

    // Redirect so refreshing the page does not submit the form twice.
    if ($error === '') {
        header('Location: index.php', true, 303);
        exit;
    }
}

// READ: get the tasks to display, with active tasks first.
$statement = $pdo->query('SELECT * FROM tasks ORDER BY completed ASC, id DESC');
$tasks = $statement->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My To-Do List</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main>
    <h1>My To-Do List</h1>
    <p>One task at a time.</p>

    <?php if ($error !== ''): ?>
        <p class="error" role="alert"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post" class="add-form">
        <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
        <input type="hidden" name="action" value="add">
        <input type="text" name="title" placeholder="What do you need to do?" aria-label="Task title" maxlength="200" required>
        <button type="submit">Add task</button>
    </form>

    <?php if (count($tasks) === 0): ?>
        <p>Your list is empty. Add your first task above!</p>
    <?php endif; ?>

    <ul>
        <?php foreach ($tasks as $task): ?>
            <li>
                <span class="<?= $task['completed'] ? 'completed' : '' ?>"><?= htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8') ?></span>

                <form method="post" class="task-actions">
                    <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">
                    <input type="hidden" name="id" value="<?= (int)$task['id'] ?>">
                    <?php if (!$task['completed']): ?>
                        <button type="submit" name="action" value="complete">Complete</button>
                    <?php else: ?>
                        <small>Done</small>
                    <?php endif; ?>
                    <button type="submit" name="action" value="delete" class="delete">Delete</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</main>
</body>
</html>
