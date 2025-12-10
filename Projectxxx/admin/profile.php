<?php
// admin/profile.php
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
        $current = $_POST['current'] ?? '';
        $new = $_POST['new'] ?? '';
        $stmt = $pdo->prepare('SELECT id, password_hash FROM admins WHERE id=:id');
        $stmt->execute(['id' => $_SESSION['admin_id']]);
        $a = $stmt->fetch();
        if ($a && password_verify($current, $a['password_hash'])) {
            $pdo->prepare('UPDATE admins SET password_hash=:ph WHERE id=:id')->execute([
                'ph' => password_hash($new, PASSWORD_DEFAULT),
                'id' => $_SESSION['admin_id']
            ]);
            $msg = 'Password updated.';
        } else {
            $error = 'Current password incorrect.';
        }
    }
}
require __DIR__ . '/../includes/header.php';
?>
<h2>Admin Profile</h2>
<?php if ($msg): ?>
    <div class="alert success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($error): ?>
    <div class="alert error"><?= e($error) ?></div><?php endif; ?>

<form method="post" class="form">
    <?= csrf_field() ?>
    <label>Current Password</label>
    <input type="password" name="current" required>
    <label>New Password</label>
    <input type="password" name="new" required>
    <button class="btn" type="submit">Update</button>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>