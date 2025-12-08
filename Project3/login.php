<?php
// login.php
require 'config.php';
$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($email === '' || $pass === '') {
        $errors[] = 'Email and password are required.';
    } else {
        // Only allow role=user here
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND role = "user"');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($pass, $user['password_hash'])) {
            unset($user['password_hash']);
            $_SESSION['user'] = $user;
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Invalid credentials.';
        }
    }
}

include 'header.php';
?>
<div class="card">
    <h1>Login</h1>
    <?php if (!empty($_GET['registered'])): ?>
        <p class="correct-answer">Account created. Please log in.</p>
    <?php endif; ?>
    <?php if ($errors): ?>
        <ul>
            <?php foreach ($errors as $e): ?>
                <li class="incorrect-answer"><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <form method="post" novalidate>
        <div class="form-group">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" required value="<?php echo htmlspecialchars($email); ?>">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Sign in</button>
    </form>
</div>
<?php include 'footer.php'; ?>
