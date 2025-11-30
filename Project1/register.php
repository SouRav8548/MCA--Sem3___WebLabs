<?php
// register.php
require 'config.php';
$errors = [];
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password_confirm'] ?? '';

    if ($name === '' || $email === '' || $pass === '' || $pass2 === '') {
        $errors[] = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    } elseif ($pass !== $pass2) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        // check if email already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email is already registered.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            // All self-registered accounts are role "user"
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?, "user")');
            $stmt->execute([$name, $email, $hash]);
            header('Location: login.php?registered=1');
            exit;
        }
    }
}

include 'header.php';
?>
<div class="card">
    <h1>Register</h1>
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
            <label for="name">Full name</label>
            <input id="name" name="name" type="text" required value="<?php echo htmlspecialchars($name); ?>">
        </div>
        <div class="form-group">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" required value="<?php echo htmlspecialchars($email); ?>">
        </div>
        <div class="form-group">
            <label for="password">Password (min 6 characters)</label>
            <input id="password" name="password" type="password" minlength="6" required>
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirm password</label>
            <input id="password_confirm" name="password_confirm" type="password" minlength="6" required>
        </div>
        <button type="submit" class="btn btn-primary">Create account</button>
    </form>
</div>
<?php include 'footer.php'; ?>
