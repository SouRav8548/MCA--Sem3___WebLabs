<?php
require_once __DIR__ . '/../config/db.php';
include 'partials/header.php';
include 'partials/nav.php';

$errors = [];
$email = ''; // Initialize variable for sticky form

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember = !empty($_POST['remember']);

    if (!$email || !$password) {
        $errors[] = 'All fields are required.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id, password_hash, verified, name FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Verify user exists AND password is correct
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Invalid credentials.';
        } elseif (!$user['verified']) {
            $errors[] = 'Please verify your email first.';
        } else {
            // Success: Reset Session ID for security (prevents session fixation)
            session_regenerate_id(true);

            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $email,
                'name' => $user['name']
            ];
            $_SESSION['last_activity'] = time();

            // Handle "Remember Me"
            if ($remember) {
                // Generate a secure random token
                $token = bin2hex(random_bytes(32));

                // Store token in database
                $upd = $pdo->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
                if ($upd->execute([$token, $user['id']])) {
                    // Set cookie for 14 days
                    // Note: set 'secure' to true if you are on HTTPS (production)
                    setcookie('remember', $token, time() + (86400 * 14), "/", "", false, true);
                }
            }

            header('Location: dashboard.php');
            exit;
        }
    }
}
?>

<div class="container" style="max-width: 450px;">
    <div class="card">
        <h2 class="card-title">Login</h2>

        <?php foreach ($errors as $e): ?>
            <div class="error"><?php echo htmlspecialchars($e); ?></div>
        <?php endforeach; ?>

        <?php if (isset($_GET['timeout'])): ?>
            <div class="error">Session timed out. Please login again.</div>
        <?php endif; ?>

        <form class="form" method="post">
            <div>
                <label>Email</label>
                <input name="email" type="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div>
                <label>Password</label>
                <input name="password" type="password" required>
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <input type="checkbox" name="remember" id="remember" style="width: auto;">
                <label for="remember" style="margin:0; cursor: pointer;">Remember me</label>
            </div>
            <button class="btn" type="submit">Sign In</button>
        </form>

        <div style="text-align: center; margin-top: 1rem;">
            <a href="register.php" style="color: var(--primary);">Don't have an account? Sign Up</a>
        </div>
    </div>
</div>
<?php include 'partials/footer.php'; ?>