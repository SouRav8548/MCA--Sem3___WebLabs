<?php
// admin/subjects.php
require __DIR__ . '/../includes/auth.php';
require_admin();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';

$msg = '';
$error = '';
if (is_post()) {
    if (!csrf_verify($_POST['csrf'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            $code = trim($_POST['code']);
            $name = trim($_POST['name']);
            $max = (int) $_POST['max_marks'];
            if (!$code || !$name || $max <= 0) {
                $error = 'Provide valid subject data.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO subjects (code, name, max_marks) VALUES (:code,:name,:max)');
                $stmt->execute(['code' => $code, 'name' => $name, 'max' => $max]);
                $msg = 'Subject added.';
            }
        } elseif ($action === 'update') {
            $id = (int) $_POST['id'];
            $name = trim($_POST['name']);
            $max = (int) $_POST['max_marks'];
            $stmt = $pdo->prepare('UPDATE subjects SET name=:name, max_marks=:max WHERE id=:id');
            $stmt->execute(['name' => $name, 'max' => $max, 'id' => $id]);
            $msg = 'Subject updated.';
        } elseif ($action === 'delete') {
            $id = (int) $_POST['id'];
            $pdo->prepare('DELETE FROM subjects WHERE id=:id')->execute(['id' => $id]);
            $msg = 'Subject deleted.';
        }
    }
}
$subjects = $pdo->query('SELECT * FROM subjects ORDER BY name')->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<h2>Manage Subjects</h2>
<?php if ($msg): ?>
    <div class="alert success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($error): ?>
    <div class="alert error"><?= e($error) ?></div><?php endif; ?>

<section class="card">
    <h3>Add Subject</h3>
    <form method="post" class="grid">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div><label>Code</label><input name="code" required></div>
        <div><label>Name</label><input name="name" required></div>
        <div><label>Max Marks</label><input type="number" name="max_marks" min="1" required></div>
        <button class="btn" type="submit">Add</button>
    </form>
</section>

<section class="card">
    <h3>All Subjects</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Max</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($subjects as $sub): ?>
                <tr>
                    <form method="post">
                        <?= csrf_field() ?>
                        <td><?= e($sub['code']) ?></td>
                        <td><input name="name" value="<?= e($sub['name']) ?>" required></td>
                        <td><input type="number" name="max_marks" value="<?= e($sub['max_marks']) ?>" min="1" required></td>
                        <td class="nowrap">
                            <input type="hidden" name="id" value="<?= e($sub['id']) ?>">
                            <input type="hidden" name="action" value="update">
                            <button class="btn small" type="submit">Save</button>
                    </form>
                    <form method="post" onsubmit="return confirm('Delete subject? Marks linked will be removed.')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= e($sub['id']) ?>">
                        <input type="hidden" name="action" value="delete">
                        <button class="btn small danger" type="submit">Delete</button>
                    </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>