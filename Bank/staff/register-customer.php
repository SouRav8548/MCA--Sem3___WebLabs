<?php
require_once '../config.php';
checkRole(ROLE_STAFF);

$message = '';
$error = '';

// Defaults
$full_name = $email = $phone = $aadhar = $pan = $address = $city = $state = $pincode = $username = '';

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
    $status = sanitizeInput(post('status', 'active')); // staff can choose active/pending

    $errors = [];

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

    // Unique username/email
    if (!$errors) {
        $existing = fetchOne(
            $conn,
            "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1",
            'ss',
            [$username, $email]
        );
        if ($existing) {
            $errors[] = 'Username or email already exists.';
        }
    }

    if (!$errors) {
        $hash = hashPassword($password);
        $role = ROLE_CUSTOMER;

        $stmt = $conn->prepare("
            INSERT INTO users (username, email, full_name, phone, password, role, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param('sssssss', $username, $email, $full_name, $phone, $hash, $role, $status);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            $stmt->close();

            $stmt2 = $conn->prepare("
                INSERT INTO customer_profile (user_id, aadhar, pan, address, city, state, pincode, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
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

            logActivity(
                $conn,
                (int) $_SESSION['user']['id'],
                'staff_register_customer',
                "Staff created customer ID $user_id"
            );

            $message = 'Customer registered successfully via staff panel.';
            // clear form
            $full_name = $email = $phone = $aadhar = $pan = $address = $city = $state = $pincode = $username = '';
        } else {
            $error = 'Error while creating customer. Please try again.';
        }
    } else {
        $error = implode(' ', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Staff · Register Customer</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg-primary, #131a1a);
            color: var(--text-primary, #e0e0e0);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .page {
            min-height: 100vh;
        }

        .content {
            max-width: 900px;
            margin: 20px auto 30px;
            padding: 0 18px 22px;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 4px;
        }

        .subtitle {
            font-size: 12px;
            color: var(--text-secondary, #c0c0c0);
            margin-bottom: 14px;
        }

        .alert {
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .alert-success {
            background: rgba(46, 184, 111, 0.12);
            color: var(--success, #2eb86f);
            border: 1px solid rgba(46, 184, 111, 0.3);
        }

        .alert-danger {
            background: rgba(201, 31, 47, 0.12);
            color: var(--danger, #c91f2f);
            border: 1px solid rgba(201, 31, 47, 0.3);
        }

        .section-card {
            background: var(--bg-secondary, #181f20);
            border-radius: 12px;
            padding: 16px 14px 14px;
            border: 1px solid var(--border-color, #2a3334);
            margin-bottom: 14px;
        }

        .section-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 4px;
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
            font-size: 13px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        label {
            font-size: 13px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"],
        select,
        textarea {
            width: 100%;
            padding: 8px 9px;
            border-radius: 8px;
            border: 1px solid var(--border-color, #2a3334);
            background: var(--bg-primary, #131a1a);
            color: var(--text-primary, #e0e0e0);
            font-size: 13px;
        }

        textarea {
            resize: vertical;
            min-height: 70px;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--primary, #2d5f82);
            box-shadow: 0 0 0 1px rgba(45, 95, 130, 0.3);
        }

        .hint {
            font-size: 11px;
            color: var(--text-secondary, #c0c0c0);
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
            margin-top: 8px;
        }
    </style>
</head>

<body>
    <div class="page">
        <?php include 'header.php'; ?>

        <div class="content">
            <h1>Register New Customer</h1>
            <p class="subtitle">
                Staff‑initiated customer registration with KYC details and initial login credentials.[web:7][web:4]
            </p>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="section-card">
                    <div class="section-title">Login Details</div>
                    <div class="section-hint">These credentials will be shared with the customer for internet banking
                        login.[web:4]</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="username">Username *</label>
                            <input type="text" id="username" name="username"
                                value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" required>
                            <div class="hint">Unique username for login.</div>
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
                        <div class="form-group">
                            <label for="status">Account Status *</label>
                            <select id="status" name="status">
                                <option value="active" <?php if ($status === 'active')
                                    echo 'selected'; ?>>Active</option>
                                <option value="pending" <?php if ($status === 'pending')
                                    echo 'selected'; ?>>Pending
                                </option>
                            </select>
                            <div class="hint">Active = ready to use, Pending = requires admin approval.[web:90]</div>
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-title">Personal Information</div>
                    <div class="section-hint">Details as per official documents.[web:7]</div>
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
                            <label for="phone">Phone *</label>
                            <input type="tel" id="phone" name="phone"
                                value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>" required>
                            <div class="hint">10‑digit mobile.</div>
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-title">KYC Details</div>
                    <div class="section-hint">Mandatory KYC information captured by staff.[web:332]</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="aadhar">Aadhaar *</label>
                            <input type="text" id="aadhar" name="aadhar"
                                value="<?php echo htmlspecialchars($aadhar, ENT_QUOTES, 'UTF-8'); ?>" required>
                            <div class="hint">12‑digit Aadhaar number.</div>
                        </div>
                        <div class="form-group">
                            <label for="pan">PAN *</label>
                            <input type="text" id="pan" name="pan"
                                value="<?php echo htmlspecialchars($pan, ENT_QUOTES, 'UTF-8'); ?>" required>
                            <div class="hint">Format: ABCDE1234F.</div>
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-title">Address</div>
                    <div class="section-hint">Communication address for statements and correspondence.[web:7]</div>
                    <div class="form-grid">
                        <div class="form-group" style="grid-column:1/-1;">
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

                <button type="submit" class="btn-primary">Create Customer</button>
                <div class="bottom-note">
                    After creating the customer, staff can proceed to open accounts and issue cards from the relevant
                    sections.[web:39]
                </div>
            </form>
        </div>
    </div>
</body>

</html>