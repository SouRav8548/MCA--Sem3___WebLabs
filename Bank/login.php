<?php
require_once 'config.php';

// If already logged in, redirect based on role
if (isLoggedIn()) {
    $role = $_SESSION['user']['role'] ?? '';
    switch ($role) {
        case ROLE_ADMIN:
        case ROLE_MANAGER:
            header('Location: admin/dashboard.php');
            exit;
        case ROLE_STAFF:
            header('Location: staff/dashboard.php');
            exit;
        case ROLE_CUSTOMER:
            header('Location: customer/dashboard.php');
            exit;
    }
}

$emailOrUsername = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailOrUsername = sanitizeInput(post('email'));
    $password = post('password');

    if ($emailOrUsername === '' || $password === '') {
        Message::addError('Please enter both username/email and password.');
    } else {
        // Find user by username or email
        $sql = "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('ss', $emailOrUsername, $emailOrUsername);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user && verifyPassword($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    Message::addError('Your account is not active. Please contact support.');
                } else {
                    // Set session user data
                    $_SESSION['user'] = [
                        'id' => (int) $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'full_name' => $user['full_name'] ?? $user['username'],
                        'role' => $user['role'],
                    ];

                    logActivity($conn, (int) $user['id'], 'login', 'User logged in');

                    // Redirect by role
                    switch ($user['role']) {
                        case ROLE_ADMIN:
                        case ROLE_MANAGER:
                            header('Location: admin/dashboard.php');
                            exit;
                        case ROLE_STAFF:
                            header('Location: staff/dashboard.php');
                            exit;
                        case ROLE_CUSTOMER:
                            header('Location: customer/dashboard.php');
                            exit;
                        default:
                            header('Location: index.php');
                            exit;
                    }
                }
            } else {
                Message::addError('Invalid credentials. Please check and try again.');
            }
        } else {
            Message::addError('Unable to process login at the moment.');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SecureBank</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-primary, #131a1a);
            color: var(--text-primary, #e0e0e0);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .auth-card {
            background: var(--bg-secondary, #181f20);
            border-radius: 16px;
            padding: 24px 22px 22px;
            border: 1px solid var(--border-color, #2a3334);
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.6);
        }

        .auth-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .brand-logo {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: linear-gradient(135deg, #2d5f82, #8b6f47);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #fff;
            font-size: 16px;
        }

        .brand-text {
            font-size: 16px;
            font-weight: 600;
        }

        .theme-toggle {
            background: transparent;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            border: 1px solid var(--border-color, #2a3334);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-secondary, #c0c0c0);
            font-size: 15px;
        }

        h1 {
            font-size: 22px;
            margin: 0 0 4px;
        }

        .subtitle {
            font-size: 13px;
            color: var(--text-secondary, #c0c0c0);
            margin-bottom: 18px;
        }

        .alert {
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 13px;
            margin-bottom: 12px;
        }

        .alert-success {
            background: rgba(46, 184, 111, 0.1);
            color: var(--success, #2eb86f);
            border: 1px solid rgba(46, 184, 111, 0.3);
        }

        .alert-danger {
            background: rgba(201, 31, 47, 0.1);
            color: var(--danger, #c91f2f);
            border: 1px solid rgba(201, 31, 47, 0.3);
        }

        .form-group {
            margin-bottom: 14px;
        }

        label {
            display: block;
            font-size: 13px;
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 9px 10px;
            border-radius: 8px;
            border: 1px solid var(--border-color, #2a3334);
            background: var(--bg-primary, #131a1a);
            color: var(--text-primary, #e0e0e0);
            font-size: 14px;
            box-sizing: border-box;
        }

        input:focus {
            outline: none;
            border-color: var(--primary, #2d5f82);
            box-shadow: 0 0 0 1px rgba(45, 95, 130, 0.3);
        }

        .form-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            margin-bottom: 12px;
            color: var(--text-secondary, #c0c0c0);
        }

        .form-row a {
            color: var(--primary, #2d5f82);
            text-decoration: none;
        }

        .btn-primary {
            width: 100%;
            border-radius: 999px;
            padding: 9px 14px;
            border: none;
            background: var(--primary, #2d5f82);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 4px;
        }

        .btn-primary:hover {
            background: var(--secondary, #8b6f47);
        }

        .demo-box {
            margin-top: 14px;
            font-size: 12px;
            padding: 8px 10px;
            border-radius: 8px;
            background: rgba(45, 95, 130, 0.08);
            border: 1px dashed var(--border-color, #2a3334);
        }

        .demo-box code {
            background: rgba(0, 0, 0, 0.25);
            padding: 2px 4px;
            border-radius: 4px;
        }

        .bottom-link {
            margin-top: 12px;
            font-size: 13px;
            text-align: center;
            color: var(--text-secondary, #c0c0c0);
        }

        .bottom-link a {
            color: var(--primary, #2d5f82);
            text-decoration: none;
        }

        @media (max-width: 480px) {
            .auth-card {
                padding: 18px 16px 18px;
            }
        }
    </style>
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <div class="brand">
                    <div class="brand-logo">SB</div>
                    <div class="brand-text">SecureBank</div>
                </div>
                <button type="button" class="theme-toggle" id="themeToggle" title="Toggle theme">🌙</button>
            </div>

            <h1>Sign in</h1>
            <p class="subtitle">Use your admin, staff or customer credentials to access the banking portal.</p>

            <?php
            Message::displayErrors();
            Message::displaySuccess();
            ?>

            <form method="post" novalidate>
                <div class="form-group">
                    <label for="email">Username or Email</label>
                    <input type="text" id="email" name="email"
                        value="<?php echo htmlspecialchars($emailOrUsername, ENT_QUOTES, 'UTF-8'); ?>"
                        autocomplete="username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                </div>

                <div class="form-row">
                    <span>Session timeout: 30 minutes of inactivity</span>
                </div>

                <button type="submit" class="btn-primary">Login</button>
            </form>

            <div class="demo-box">
                <strong>Demo admin login</strong><br>
                Username: <code>admin</code><br>
                Password: <code>admin123</code>
            </div>

            <div class="bottom-link">
                New customer? <a href="register.php">Open an account</a>
            </div>
        </div>
    </div>

    <script>
        // Theme toggle logic
        (function () {
            const root = document.documentElement;
            const saved = localStorage.getItem('theme');
            if (saved === 'light' || saved === 'dark') {
                root.setAttribute('data-theme', saved);
            } else {
                root.setAttribute('data-theme', 'dark');
            }

            const btn = document.getElementById('themeToggle');
            const updateIcon = () => {
                const current = root.getAttribute('data-theme');
                btn.textContent = current === 'light' ? '☀️' : '🌙';
            };
            updateIcon();

            btn.addEventListener('click', function () {
                const current = root.getAttribute('data-theme');
                const next = current === 'dark' ? 'light' : 'dark';
                root.setAttribute('data-theme', next);
                localStorage.setItem('theme', next);
                updateIcon();
            });
        })();
    </script>
</body>

</html>