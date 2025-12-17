<?php
require_once '../config.php';
checkRole(ROLE_CUSTOMER);

$user = $_SESSION['user'] ?? null;
$userId = (int) $user['id'];

$message = '';
$error = '';

// Load current data
$row = fetchOne(
    $conn,
    "SELECT u.full_name, u.email, u.phone,
            cp.aadhar, cp.pan, cp.address, cp.city, cp.state, cp.pincode
     FROM users u
     LEFT JOIN customer_profile cp ON cp.user_id = u.id
     WHERE u.id = ?",
    'i',
    [$userId]
);

$full_name = $row['full_name'] ?? '';
$email = $row['email'] ?? '';
$phone = $row['phone'] ?? '';
$aadhar = $row['aadhar'] ?? '';
$pan = $row['pan'] ?? '';
$address = $row['address'] ?? '';
$city = $row['city'] ?? '';
$state = $row['state'] ?? '';
$pincode = $row['pincode'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitizeInput(post('full_name'));
    $email = sanitizeInput(post('email'));
    $phone = sanitizeInput(post('phone'));
    $address = sanitizeInput(post('address'));
    $city = sanitizeInput(post('city'));
    $state = sanitizeInput(post('state'));
    $pincode = sanitizeInput(post('pincode'));

    $errors = [];

    if ($full_name === '')
        $errors[] = 'Full name is required.';
    if ($email === '' || !validateEmail($email))
        $errors[] = 'Valid email is required.';
    if ($phone !== '' && !validatePhone($phone))
        $errors[] = 'Phone number format is invalid.';

    // unique email (excluding self)
    if (!$errors) {
        $existing = fetchOne(
            $conn,
            "SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1",
            'si',
            [$email, $userId]
        );
        if ($existing) {
            $errors[] = 'This email address is already used by another user.';
        }
    }

    if (!$errors) {
        // update users
        $stmt = $conn->prepare("
            UPDATE users
            SET full_name = ?, email = ?, phone = ?
            WHERE id = ?
        ");
        $stmt->bind_param('sssi', $full_name, $email, $phone, $userId);
        $ok1 = $stmt->execute();
        $stmt->close();

        // upsert customer_profile simple way: try update, if 0 rows then insert
        $stmt2 = $conn->prepare("
            UPDATE customer_profile
            SET address = ?, city = ?, state = ?, pincode = ?
            WHERE user_id = ?
        ");
        $stmt2->bind_param('ssssi', $address, $city, $state, $pincode, $userId);
        $stmt2->execute();
        $affected = $stmt2->affected_rows;
        $stmt2->close();

        if ($affected === 0) {
            $stmt3 = $conn->prepare("
                INSERT INTO customer_profile (user_id, address, city, state, pincode, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt3->bind_param('issss', $userId, $address, $city, $state, $pincode);
            $stmt3->execute();
            $stmt3->close();
        }

        logActivity($conn, $userId, 'customer_update_profile', 'Customer updated profile');
        $message = 'Profile updated successfully.';
        $_SESSION['user']['full_name'] = $full_name;
        $_SESSION['user']['email'] = $email;
    } else {
        $error = implode(' ', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>My Profile - SecureBank</title>
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
            max-width: 800px;
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
        input[type="tel"],
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
        textarea:focus {
            outline: none;
            border-color: var(--primary, #2d5f82);
            box-shadow: 0 0 0 1px rgba(45, 95, 130, 0.3);
        }

        .hint {
            font-size: 11px;
            color: var(--text-secondary, #c0c0c0);
        }

        .readonly {
            opacity: 0.8;
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

        .small-link {
            font-size: 12px;
            color: var(--primary, #2d5f82);
            text-decoration: none;
        }

        .small-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="page">
        <?php include 'header.php'; ?>

        <div class="content">
            <h1>My Profile</h1>
            <p class="subtitle">
                View and update your personal and contact information. KYC identifiers are read-only once
                verified.[web:308][web:90]
            </p>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="section-card">
                    <div class="section-title">Basic Information</div>
                    <div class="section-hint">These details will be used for communication and statements.[web:335]
                    </div>
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
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone"
                                value="<?php echo htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="hint">Update your active mobile number.[web:374]</div>
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-title">KYC Identifiers</div>
                    <div class="section-hint">Aadhaar and PAN are shown for reference and may be locked once
                        verified.[web:390]</div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Aadhaar</label>
                            <input type="text" value="<?php echo htmlspecialchars($aadhar, ENT_QUOTES, 'UTF-8'); ?>"
                                class="readonly" disabled>
                            <div class="hint">Contact branch to correct KYC identifiers.[web:390]</div>
                        </div>
                        <div class="form-group">
                            <label>PAN</label>
                            <input type="text" value="<?php echo htmlspecialchars($pan, ENT_QUOTES, 'UTF-8'); ?>"
                                class="readonly" disabled>
                        </div>
                    </div>
                </div>

                <div class="section-card">
                    <div class="section-title">Address</div>
                    <div class="section-hint">Keep your address updated for card delivery and official
                        communication.[web:308]</div>
                    <div class="form-grid">
                        <div class="form-group" style="grid-column:1/-1;">
                            <label for="address">Address Line</label>
                            <textarea id="address" name="address"><?php
                            echo htmlspecialchars($address, ENT_QUOTES, 'UTF-8');
                            ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city"
                                value="<?php echo htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state"
                                value="<?php echo htmlspecialchars($state, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="pincode">Pincode</label>
                            <input type="text" id="pincode" name="pincode"
                                value="<?php echo htmlspecialchars($pincode, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Save Changes</button>
                <div style="margin-top:8px;font-size:12px;color:var(--text-secondary,#c0c0c0);">
                    To change your password, go to <a href="change-password.php" class="small-link">Change
                        Password</a>.[web:90]
                </div>
            </form>
        </div>
    </div>
</body>

</html>