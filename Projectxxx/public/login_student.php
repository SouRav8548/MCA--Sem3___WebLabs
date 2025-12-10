<?php
// public/login_student.php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';

$error = '';
if (is_post()) {
    if (!csrf_verify($_POST['csrf'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $sid = trim($_POST['student_id'] ?? '');
        $password = $_POST['password'] ?? '';
        $stmt = $pdo->prepare('SELECT id, password_hash FROM students WHERE student_id = :sid LIMIT 1');
        $stmt->execute(['sid' => $sid]);
        $stu = $stmt->fetch();
        if ($stu && password_verify($password, $stu['password_hash'])) {
            login_student($stu['id']);
            redirect('/marksheet-portal/student/marksheet.php');
        } else {
            $error = 'Invalid credentials.';
        }
    }
}
require __DIR__ . '/../includes/header.php';
?>
<h2>Student Login</h2>
<?php if ($error): ?>
    <div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="form">
    <?= csrf_field() ?>
    <label>Student ID</label>
    <input type="text" name="student_id" required />
    <label>Password</label>
    <input type="password" name="password" required />
    <button type="submit" class="btn">Login</button>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>