<?php
require_once '../config.php';
checkRole(ROLE_CUSTOMER);

$user = $_SESSION['user'] ?? null;
$userId = (int) $user['id'];

$message = '';
$error = '';

// Basic account types – you may load from DB instead
$accountTypes = ['savings', 'current', 'salary', 'fixed_deposit'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_type = sanitizeInput(post('account_type'));
    $branch_id = (int) post('branch_id');
    $notes = sanitizeInput(post('notes'));

    $errors = [];

    if (!in_array($account_type, $accountTypes, true)) {
        $errors[] = 'Please select a valid account type.';
    }
    if ($branch_id <= 0) {
        $errors[] = 'Please choose a branch.';
    }

    if (!$errors) {
        // Create a request row, not a live account yet
        $stmt = $conn->prepare("
            INSERT INTO account_open_requests (user_id, branch_id, account_type, notes, status, created_at)
            VALUES (?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->bind_param('iiss', $userId, $branch_id, $account_type, $notes);
        if ($stmt->execute()) {
            $message = 'Your account opening request has been submitted and is pending approval.';
            logActivity(
                $conn,
                $userId,
                'customer_open_account_request',
                "Customer requested new {$account_type} account at branch ID {$branch_id}"
            );
        } else {
            $error = 'Failed to submit your request. Please try again later.';
        }
        $stmt->close();
    } else {
        $error = implode(' ', $errors);
    }
}

// Branch list for dropdown
$branches = fetchAll($conn, "SELECT id, name, code, city, state FROM branches ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Open New Account - SecureBank</title>
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

        .card {
            background: var(--bg-secondary, #181f20);
            border-radius: 12px;
            padding: 16px 14px 14px;
            border: 1px solid var(--border-color, #2a3334);
            margin-bottom: 14px;
        }

        .card-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .card-subtitle {
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
            min-height: 70px;
            resize: vertical;
        }

        select:focus,
        textarea:focus {
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

        .note {
            margin-top: 8px;
            font-size: 12px;
            color: var(--text-secondary, #c0c0c0);
        }
    </style>
</head>

<body>
    <div class="page">
        <?php include 'header.php'; ?>

        <div class="content">
            <h1>Open New Account</h1>
            <p class="subtitle">
                Submit a request to open an additional bank account. The branch will review and approve your
                request.[web:4][web:90]
            </p>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="card">
                    <div class="card-title">Account Details</div>
                    <div class="card-subtitle">
                        Choose the type of account and preferred branch for opening.[web:10]
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="account_type">Account Type *</label>
                            <select id="account_type" name="account_type" required>
                                <option value="">Select type</option>
                                <?php foreach ($accountTypes as $type): ?>
                                    <option value="<?php echo htmlspecialchars($type, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $type)), ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="hint">Common options: savings, current, salary, fixed deposit.[web:432]</div>
                        </div>
                        <div class="form-group">
                            <label for="branch_id">Branch *</label>
                            <select id="branch_id" name="branch_id" required>
                                <option value="">Select branch</option>
                                <?php foreach ($branches as $b): ?>
                                    <option value="<?php echo (int) $b['id']; ?>">
                                        <?php
                                        echo htmlspecialchars(
                                            $b['name'] . ' (' . $b['city'] . ', ' . $b['state'] . ')',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="hint">Select the branch where you want the account to be maintained.[web:432]
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-title">Additional Notes</div>
                    <div class="card-subtitle">
                        Provide any extra information or preferences for this account (optional).[web:469]
                    </div>
                    <div class="form-group">
                        <label for="notes">Message to branch</label>
                        <textarea id="notes" name="notes"
                            placeholder="Example: Salary account for XYZ company, prefer SMS alerts, etc."></textarea>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Submit Request</button>
                <div class="note">
                    You will be notified once the bank approves your new account request. After approval, it will appear
                    under “My Accounts”.[web:90][web:4]
                </div>
            </form>
        </div>
    </div>
</body>

</html>