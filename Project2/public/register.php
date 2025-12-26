<?php
require_once __DIR__ . '/../config/db.php';
include 'partials/header.php';
include 'partials/nav.php';
$errors = [];
$success = '';
$name = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? '');
    if (!$email)
        $errors[] = 'Valid email required.';
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors[] = 'Password must be 8+ chars (1 upper, 1 lower, 1 number).';
    }
    if (empty($name))
        $errors[] = 'Name required.';
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, name, verified) VALUES (?, ?, ?, 1)');
            if ($stmt->execute([$email, $hash, $name])) {
                $success = "Account created successfully! <a href='login.php' style='color: var(--primary); font-weight: bold; text-decoration: underline;'>Login Now</a>";
                $name = '';
                $email = '';
            } else {
                $errors[] = "Database Error.";
            }
        }
    }
}
?>
<div class="container" style="max-width: 500px;">
    <div class="card">
        <h2 class="card-title">Sign Up</h2>
        <?php foreach ($errors as $e): ?>
            <div class="error"><?php echo htmlspecialchars($e); ?></div>
        <?php endforeach; ?>

        <?php if ($success): ?>
            <div class="success"><?php echo $success; ?></div>
        <?php else: ?>
            <form class="form" method="post">
                <div>
                    <label>Name</label>
                    <input name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
                <div>
                    <label>Email</label>
                    <input name="email" type="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                <div>
                    <label>Password</label>
                    <input name="password" type="password" required>
                </div>
                <button class="btn" type="submit">Create Account</button>
            </form>
            <div style="text-align: center; margin-top: 1rem;">
                <a href="login.php" style="color: var(--primary);">Already have an account? Login</a>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php include 'partials/footer.php'; ?>