<?php
// register.php
require 'config.php';
$errors = [];
$name = $email = '';
$role = ''; // track selected role for redisplay

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['password_confirm'] ?? '';
    $role = $_POST['role'] ?? '';   // NEW: get role from form

    // Basic required checks
    if ($name === '' || $email === '' || $pass === '' || $pass2 === '' || $role === '') {
        $errors[] = 'All fields are required, including role.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    } elseif ($pass !== $pass2) {
        $errors[] = 'Passwords do not match.';
    }

    // Role must be exactly "admin" or "user"
    if ($role !== 'admin' && $role !== 'user') {
        $errors[] = 'Invalid role selected.';
    }

    if (!$errors) {
        // check if email already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email is already registered.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);

            // INSERT in correct order: name, email, password_hash, role
            $stmt = $pdo->prepare(
                'INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,?)'
            );
            $stmt->execute([$name, $email, $hash, $role]);

            header('Location: login.php?registered=1');
            exit;
        }
    }
}

include 'header.php';
?>

<style>
    .role-toggle {
        margin-top: 1.2rem;
    }

    .role-toggle p {
        margin: 0 0 0.6rem 0;
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--color-text, #f4f5fb);
    }

    .role-toggle-inner {
        position: relative;
        display: inline-grid;
        grid-template-columns: 1fr 1fr;
        align-items: center;
        width: 220px;
        border-radius: 999px;
        background: rgba(135, 206, 250, 0.08);
        border: 1px solid rgba(135, 206, 250, 0.6);
        overflow: hidden;
    }

    .role-toggle-inner input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .role-option {
        position: relative;
        z-index: 2;
        padding: 0.4rem 0.8rem;
        text-align: center;
        font-size: 0.9rem;
        color: var(--color-text, #f4f5fb);
        cursor: pointer;
        user-select: none;
        transition: color 0.2s ease;
    }

    .role-highlight {
        position: absolute;
        top: 3px;
        left: 3px;
        width: calc(50% - 6px);
        height: calc(100% - 6px);
        border-radius: 999px;
        background: linear-gradient(135deg,
                var(--color-primary, #4e9fff),
                var(--color-accent, #ff6fb5));
        z-index: 1;
        transition: transform 0.25s ease;
    }

    /* Move highlight when "user" selected */
    #user:checked~.role-highlight {
        transform: translateX(100%);
    }

    /* Active text color */
    #admin:checked~label[for="admin"],
    #user:checked~label[for="user"] {
        color: #ffffff;
        font-weight: 600;
    }

    .role-option:hover {
        color: var(--color-primary, #87cefa);
    }
</style>



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


        <div class="form-group role-toggle">
            <p>Your Role</p>
            <div class="role-toggle-inner">
                <input type="radio" id="admin" name="role" value="admin" <?php if ($role === 'admin')
                    echo 'checked'; ?>
                    required>
                <label for="admin" class="role-option">Admin</label>

                <input type="radio" id="user" name="role" value="user" <?php if ($role === 'user' || $role === '')
                    echo 'checked'; ?> required>
                <label for="user" class="role-option">User</label>

                <span class="role-highlight"></span>
            </div>
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