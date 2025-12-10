<?php
// public/login_admin.php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';

$error = '';
if (is_post()) {
    if (!csrf_verify($_POST['csrf'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $stmt = $pdo->prepare('SELECT id, password_hash FROM admins WHERE username = :u LIMIT 1');
        $stmt->execute(['u' => $username]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($password, $admin['password_hash'])) {
            login_admin($admin['id']);
            redirect('/marksheet-portal/admin/dashboard.php');
        } else {
            $error = 'Invalid credentials.';
        }
    }
}
require __DIR__ . '/../includes/header.php';
?>
<h2>Admin Login</h2>
<?php if ($error): ?>
    <div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="form">
    <?= csrf_field() ?>
    <label>Username</label>
    <input type="text" name="username" required />
    <label>Password</label>
    <input type="password" name="password" required />
    <button type="submit" class="btn">Login</button>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>