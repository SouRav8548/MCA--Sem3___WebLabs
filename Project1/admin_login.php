<?php
// admin_login.php
require 'config.php';
$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email === '' || $pass === '') {
        $errors[] = 'Email and password are required.';
    } else {
        // Only admins here
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND role = "admin"');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        if ($admin && password_verify($pass, $admin['password_hash'])) {
            unset($admin['password_hash']);
            $_SESSION['user'] = $admin;
            header('Location: admin/questions.php');
            exit;
        } else {
            $errors[] = 'Invalid admin credentials.';
        }
    }
}

include 'header.php';
?>
<div class="card">
    <h1>Admin Login</h1>
    <?php if ($errors): ?>
        <ul>
            <?php foreach ($errors as $e): ?>
                <li class="incorrect-answer"><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <form method="post" novalidate>
        <div class="form-group">
            <label for="email">Admin email</label>
            <input id="email" name="email" type="email" required value="<?php echo htmlspecialchars($email); ?>">
        </div>
        <div class="form-group">
            <label for="password">Admin password</label>
            <input id="password" name="password" type="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Sign in as admin</button>
    </form>
</div>
<?php include 'footer.php'; ?>
