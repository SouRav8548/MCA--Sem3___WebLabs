<?php
require_once 'config.php';

if (isLoggedIn()) {
    // Logged‑in users should not see registration normally
    header('Location: index.php');
    exit;
}

$errors = [];
$success = false;

// Preserve form values
$full_name = '';
$email = '';
$phone = '';
$aadhar = '';
$pan = '';
$address = '';
$city = '';
$state = '';
$pincode = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput(post('full_name'));
    $email = sanitizeInput(post('email'));
    $phone = sanitizeInput(post('phone'));
    $aadhar = preg_replace('/\D/', '', post('aadhar'));
    $pan = strtoupper(sanitizeInput(post('pan')));
    $address = sanitizeInput(post('address'));
    $city = sanitizeInput(post('city'));
    $state = sanitizeInput(post('state'));
    $pincode = sanitizeInput(post('pincode'));
    $username = sanitizeInput(post('username'));
    $password = post('password');
    $confirm = post('confirm_password');

    // Basic validations
    if ($full_name === '')
        $errors[] = 'Full name is required.';
    if ($email === '' || !validateEmail($email))
        $errors[] = 'Valid email is required.';
    if ($phone === '' || !validatePhone($phone))
        $errors[] = 'Valid 10‑digit phone number is required.';
    if ($aadhar === '' || !validateAadhar($aadhar))
        $errors[] = 'Valid 12‑digit Aadhaar is required.';
    if ($pan === '' || !validatePAN($pan))
        $errors[] = 'Valid PAN is required.';
    if ($address === '')
        $errors[] = 'Address is required.';
    if ($city === '')
        $errors[] = 'City is required.';
    if ($state === '')
        $errors[] = 'State is required.';
    if ($pincode === '')
        $errors[] = 'Pincode is required.';
    if ($username === '')
        $errors[] = 'Username is required.';
    if ($password === '' || strlen($password) < 6)
        $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)
        $errors[] = 'Password and confirm password do not match.';

    // Check unique username/email/Aadhaar/PAN
    if (!$errors) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $errors[] = 'Username or email already exists.';
        }
        $stmt->close();
    }

    if (!$errors) {
        // Insert into users table
        $hash = hashPassword($password);
        $role = ROLE_CUSTOMER;
        $status = 'pending';

        $stmt = $conn->prepare("
            INSERT INTO users (username, email, full_name, phone, password, role, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $sql = "INSERT INTO users (username, email, full_name, phone, password, role, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die('Prepare failed: ' . $conn->error); // TEMP for debugging
        }

        $stmt->bind_param(
            'sssssss',
            $username,
            $email,
            $full_name,
            $phone,
            $hash,
            $role,
            $status
        );
        $stmt->bind_param('sssssss', $username, $email, $full_name, $phone, $hash, $role, $status);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            $stmt->close();

            // Insert extended KYC into customer_profile
            $stmt2 = $conn->prepare("
                INSERT INTO customer_profile (user_id, aadhar, pan, address, city, state, pincode, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            if (!$stmt2) {
                die('Prepare stmt2 failed: ' . $conn->error);
            }
            $stmt2->bind_param(
                'issssss',
                $user_id,
                $aadhar,
                $pan,
                $address,
                $city,
                $state,
                $pincode
            );
            $stmt2->execute();
            $stmt2->close();

            logActivity($conn, (int) $user_id, 'register', 'New customer registration');

            $success = true;
            Message::addSuccess('Registration submitted successfully. Your account is pending approval by the bank.');
            // Clear form values
            $full_name = $email = $phone = $aadhar = $pan = $address = $city = $state = $pincode = $username = '';
        } else {
            $errors[] = 'Error while creating account. Please try again.';
        }
    }

    foreach ($errors as $err) {
        Message::addError($err);
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Open Account - SecureBank</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg-primary, #131a1a);
            color: var(--text-primary, #e0e0e0);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .page-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header {
            padding: 12px 20px;
            border-bottom: 1px solid var(--border-color, #2a3334);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-secondary, #181f20);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .brand-logo {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: linear-gradient(135deg, #2d5f82, #8b6f47);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 15px;
        }

        .brand-text {
            font-size: 15px;
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
        }

        main {
            flex: 1;
            max-width: 900px;
            margin: 20px auto 30px;
            padding: 0 15px 20px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 4px;
        }

        .subtitle {
            font-size: 13px;
            color: var(--text-secondary, #c0c0c0);
            margin-bottom: 18px;
        }

        .section-card {
            background: var(--bg-secondary, #181f20);
            border-radius: 12px;
            padding: 18px 16px 16px;
            border: 1px solid var(--border-color, #2a3334);
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .section-hint {
            font-size: 12px;
            color: var(--text-secondary, #c0c0c0);
            margin-bottom: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px 14px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 13px;
        }

        label {
            font-size: 13px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"] {
            padding: 8px 9px;
            border-radius: 8px;
            border: 1px solid var(--border-color, #2a3334);
            background: var(--bg-primary, #131a1a);
            color: var(--text-primary, #e0e0e0);
            font-size: 13px;
        }

        input:focus {
            outline: none;
            border-color: var(--primary, #2d5f82);
            box-shadow: 0 0 0 1px rgba(45, 95, 130, 0.3);
        }

        textarea {
            padding: 8px 9px;
            border-radius: 8px;
            border: 1px solid var(--border-color, #2a3334);
            background: var(--bg-primary, #131a1a);
            color: var(--text-primary, #e0e0e0);
            font-size: 13px;
            resize: vertical;
            min-height: 70px;
        }

        .hint {
            font-size: 11px;
            color: var(--text-secondary, #c0c0c0);
        }

        .alert {
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 13px;
            margin-bottom: 10px;
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

        .btn-primary {
            border-radius: 999px;
            padding: 9px 18px;
            border: none;
            background: var(--primary, #2d5f82);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: var(--secondary, #8b6f47);
        }

        .bottom-note {
            font-size: 12px;
            color: var(--text-secondary, #c0c0c0);
            margin-top: 10px;
        }

        .bottom-note a {
            color: var(--primary, #2d5f82);
            text-decoration: none;
        }

        footer {
            padding: 10px 15px;
            border-top: 1px solid var(--border-color, #2a3334);
            font-size: 11px;
            color: var(--text-secondary, #c0c0c0);
            text-align: center;
            background: var(--bg-secondary, #181f20);
        }

        @media (max-width: 600px) {
            header {
                padding: 10px 12px;
            }
        }
    </style>
</head>

<body>
    <div class="page-wrapper">
        <header>
            <div class="brand">
                <div class="brand-logo">SB</div>
                <div class="brand-text">SecureBank</div>
            </div>
            <button type="button" class="theme-toggle" id="themeToggle">🌙</button>
        </header>

        <main>
            <h1>Open a New Account</h1>
            <p class="subtitle">Fill in your details to register as a customer. Your account will be activated after
                verification.</p>

            <?php
            Message::displayErrors();
            Message::displaySuccess();
            ?>

            <form method="post" novalidate>
                <!-- Account & Login Section -->
                <div class="section-card">
                    <div class="section-title">Login Details</div>
                    <div class="section-hint">These credentials will be used to login to your internet banking.</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="username">Username *</label>
                            <input type="text" id="username" name="username"
                                value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" required>
                            <div class="hint">Unique username (used for login).</div>
                        </div>
                        <div class="form-group">
                            <label for="password">Password *</label>
                            <input type="password" id="password" name="password" required>
                            <div class="hint">Minimum 6 characters. Use a strong password.</div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                </div>

                <!-- Personal Details -->
                <div class="section-card">
                    <div class="section-title">Personal Information</div>
                    <div class="section-hint">Your basic details as per official documents.</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="full_name">Full Name *</label>
                            <input type="text" id="full_name" name="full_name"
                                value="<?php echo htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email"
                                value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone"
                                value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>" required>
                            <div class="hint">10‑digit mobile number.</div>
                        </div>
                    </div>
                </div>

                <!-- KYC Details -->
                <div class="section-card">
                    <div class="section-title">KYC Details</div>
                    <div class="section-hint">KYC (Know Your Customer) is required for all banking customers.</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="aadhar">Aadhaar Number *</label>
                            <input type="text" id="aadhar" name="aadhar"
                                value="<?php echo htmlspecialchars($aadhar, ENT_QUOTES, 'UTF-8'); ?>" required>
                            <div class="hint">12‑digit Aadhaar number.</div>
                        </div>
                        <div class="form-group">
                            <label for="pan">PAN *</label>
                            <input type="text" id="pan" name="pan"
                                value="<?php echo htmlspecialchars($pan, ENT_QUOTES, 'UTF-8'); ?>" required>
                            <div class="hint">Format: ABCDE1234F</div>
                        </div>
                    </div>
                </div>

                <!-- Address Details -->
                <div class="section-card">
                    <div class="section-title">Address</div>
                    <div class="section-hint">Your communication address.</div>
                    <div class="form-grid">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="address">Address Line *</label>
                            <textarea id="address" name="address" required><?php
                            echo htmlspecialchars($address, ENT_QUOTES, 'UTF-8');
                            ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="city">City *</label>
                            <input type="text" id="city" name="city"
                                value="<?php echo htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="state">State *</label>
                            <input type="text" id="state" name="state"
                                value="<?php echo htmlspecialchars($state, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="pincode">Pincode *</label>
                            <input type="text" id="pincode" name="pincode"
                                value="<?php echo htmlspecialchars($pincode, ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 10px;">
                    <button type="submit" class="btn-primary">Submit Registration</button>
                    <div class="bottom-note">
                        Already have an account? <a href="login.php">Login here</a>.
                    </div>
                </div>
            </form>
        </main>

        <footer>
            By submitting this form, you request account opening with SecureBank. Your request will be processed after
            KYC verification.
        </footer>
    </div>

    <script>
        // Theme toggle
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