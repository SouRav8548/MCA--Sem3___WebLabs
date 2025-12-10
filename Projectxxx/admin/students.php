<?php
// admin/students.php
require __DIR__ . '/../includes/auth.php';
require_admin();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';

$msg = '';
$error = '';

// Handle create/update/delete
if (is_post()) {
    if (!csrf_verify($_POST['csrf'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            $student_id = trim($_POST['student_id']);
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $dob = $_POST['dob'];
            $password = $_POST['password'];
            if (!$student_id || !$name || !$dob || !$password) {
                $error = 'All required fields must be filled.';
            } elseif (!preg_match('/^[A-Za-z0-9_-]+$/', $student_id)) {
                $error = 'Invalid Student ID format.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO students (student_id, name, email, dob, password_hash) VALUES (:sid, :name, :email, :dob, :ph)');
                $stmt->execute([
                    'sid' => $student_id,
                    'name' => $name,
                    'email' => $email ?: null,
                    'dob' => $dob,
                    'ph' => password_hash($password, PASSWORD_DEFAULT),
                ]);
                $msg = 'Student created.';
            }
        } elseif ($action === 'update') {
            $id = (int) $_POST['id'];
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $dob = $_POST['dob'];
            if (!$id || !$name || !$dob) {
                $error = 'Invalid data.';
            } else {
                $stmt = $pdo->prepare('UPDATE students SET name=:name, email=:email, dob=:dob WHERE id=:id');
                $stmt->execute(['name' => $name, 'email' => $email ?: null, 'dob' => $dob, 'id' => $id]);
                $msg = 'Student updated.';
            }
        } elseif ($action === 'delete') {
            $id = (int) $_POST['id'];
            $stmt = $pdo->prepare('DELETE FROM students WHERE id=:id');
            $stmt->execute(['id' => $id]);
            $msg = 'Student deleted.';
        }
    }
}

$students = $pdo->query('SELECT id, student_id, name, email, dob FROM students ORDER BY created_at DESC')->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<h2>Manage Students</h2>
<?php if ($msg): ?>
    <div class="alert success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($error): ?>
    <div class="alert error"><?= e($error) ?></div><?php endif; ?>

<section class="card">
    <h3>Add New Student</h3>
    <form method="post" class="grid">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create" />
        <div>
            <label>Student ID</label>
            <input name="student_id" required />
        </div>
        <div>
            <label>Name</label>
            <input name="name" required />
        </div>
        <div>
            <label>Email</label>
            <input type="email" name="email" />
        </div>
        <div>
            <label>Date of Birth</label>
            <input type="date" name="dob" required />
        </div>
        <div>
            <label>Initial Password</label>
            <input type="password" name="password" required />
        </div>
        <button class="btn" type="submit">Add Student</button>
    </form>
</section>

<section class="card">
    <h3>All Students</h3>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Student ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>DOB</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $s): ?>
                <tr>
                    <form method="post">
                        <?= csrf_field() ?>
                        <td><?= e($s['id']) ?><input type="hidden" name="id" value="<?= e($s['id']) ?>"></td>
                        <td><?= e($s['student_id']) ?></td>
                        <td><input name="name" value="<?= e($s['name']) ?>" required></td>
                        <td><input name="email" value="<?= e($s['email']) ?>"></td>
                        <td><input type="date" name="dob" value="<?= e($s['dob']) ?>" required></td>
                        <td class="nowrap">
                            <input type="hidden" name="action" value="update">
                            <button class="btn small" type="submit">Save</button>
                    </form>
                    <form method="post" onsubmit="return confirm('Delete student and all marks?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= e($s['id']) ?>">
                        <button class="btn small danger" type="submit">Delete</button>
                    </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>