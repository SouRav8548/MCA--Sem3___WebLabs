<?php
require_once '../config.php';
checkRole(ROLE_CUSTOMER);

$user = $_SESSION['user'] ?? null;
$userId = (int) $user['id'];

$message = '';
$error = '';

// Load customer's active accounts
$accounts = fetchAll(
    $conn,
    "SELECT id, account_number, account_type
     FROM accounts
     WHERE user_id = ? AND status = 'active'
     ORDER BY created_at ASC",
    'i',
    [$userId]
);

// Allowed leaves options
$leaveOptions = [25, 50, 100];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accountId = (int) post('account_id');
    $leaves = (int) post('leaves');
    $notes = sanitizeInput(post('notes'));

    $errors = [];

    if ($accountId <= 0) {
        $errors[] = 'Please select an account.';
    }
    if (!in_array($leaves, $leaveOptions, true)) {
        $errors[] = 'Please select a valid number of leaves.';
    }

    // Ensure account belongs to this customer
    if (!$errors) {
        $acc = fetchOne(
            $conn,
            "SELECT id FROM accounts WHERE id = ? AND user_id = ? AND status = 'active'",
            'ii',
            [$accountId, $userId]
        );
        if (!$acc) {
            $errors[] = 'Invalid account selected.';
        }
    }

    // Optional: block duplicate pending request for same account
    if (!$errors) {
        $pending = fetchOne(
            $conn,
            "SELECT id FROM cheque_book_requests
             WHERE user_id = ? AND account_id = ? AND status = 'pending'
             LIMIT 1",
            'ii',
            [$userId, $accountId]
        );
        if ($pending) {
            $errors[] = 'You already have a pending cheque book request for this account.';
        }
    }

    if (!$errors) {
        $stmt = $conn->prepare("
            INSERT INTO cheque_book_requests (user_id, account_id, leaves, status, notes, created_at)
            VALUES (?, ?, ?, 'pending', ?, NOW())
        ");
        $stmt->bind_param('iiis', $userId, $accountId, $leaves, $notes);
        if ($stmt->execute()) {
            $message = 'Your cheque book request has been submitted and is pending approval.';
            logActivity(
                $conn,
                $userId,
                'customer_cheque_request',
                "Customer requested cheque book ({$leaves} leaves) for account ID {$accountId}"
            );
        } else {
            $error = 'Failed to submit cheque book request. Please try again later.';
        }
        $stmt->close();
    } else {
        $error = implode(' ', $errors);
    }
}

// Recent cheque requests
$recentRequests = fetchAll(
    $conn,
    "SELECT r.*, a.account_number
     FROM cheque_book_requests r
     LEFT JOIN accounts a ON a.id = r.account_id
     WHERE r.user_id = ?
     ORDER BY r.created_at DESC
     LIMIT 5",
    'i',
    [$userId]
);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Cheque Book Request - SecureBank</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th,
        td {
            padding: 6px 4px;
            border-bottom: 1px solid var(--border-color, #2a3334);
            text-align: left;
        }

        th {
            font-weight: 600;
            color: var(--text-secondary, #c0c0c0);
        }

        tr:last-child td {
            border-bottom: none;
        }

        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: 11px;
        }

        .badge-pending {
            background: rgba(244, 165, 66, 0.18);
            color: var(--warning, #f4a542);
        }

        .badge-approved {
            background: rgba(46, 184, 111, 0.18);
            color: var(--success, #2eb86f);
        }

        .badge-rejected {
            background: rgba(201, 31, 47, 0.18);
            color: var(--danger, #c91f2f);
        }

        .info-small {
            font-size: 11px;
            color: var(--text-secondary, #c0c0c0);
        }
    </style>
</head>

<body>
    <div class="page">
        <?php include 'header.php'; ?>

        <div class="content">
            <h1>Cheque Book Request</h1>
            <p class="subtitle">
                Request a new cheque book for one of your active accounts and track its approval status
                online.[web:291][web:491]
            </p>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="post">
                <div class="card">
                    <div class="card-title">Request Details</div>
                    <div class="card-subtitle">
                        Choose the account and number of leaves for your new cheque book.[web:492][web:493]
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="account_id">Account *</label>
                            <select id="account_id" name="account_id" required>
                                <option value="">Select account</option>
                                <?php foreach ($accounts as $acc): ?>
                                    <option value="<?php echo (int) $acc['id']; ?>">
                                        <?php
                                        echo htmlspecialchars(
                                            $acc['account_number'] . ' • ' . ucfirst($acc['account_type']),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="hint">Only active savings/current accounts are eligible for cheque
                                books.[web:490]</div>
                        </div>
                        <div class="form-group">
                            <label for="leaves">Number of leaves *</label>
                            <select id="leaves" name="leaves" required>
                                <option value="">Select</option>
                                <?php foreach ($leaveOptions as $l): ?>
                                    <option value="<?php echo (int) $l; ?>"><?php echo (int) $l; ?> leaves</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="hint">Common options: 25, 50, or 100 leaves per cheque book.[web:493][web:498]
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-title">Additional Information</div>
                    <div class="card-subtitle">
                        Add any remarks or special instructions for delivery (optional).[web:489]
                    </div>
                    <div class="form-group">
                        <label for="notes">Remarks</label>
                        <textarea id="notes" name="notes"
                            placeholder="Example: Deliver to registered address; urgent requirement, etc."></textarea>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Submit Request</button>
                <div class="info-small" style="margin-top:8px;">
                    The cheque book will be issued and dispatched as per bank policy; you can check status
                    below.[web:492][web:495]
                </div>
            </form>

            <div class="card" style="margin-top:18px;">
                <div class="card-title">Recent Cheque Book Requests</div>
                <div class="card-subtitle">
                    Last few requests placed from your internet banking profile.[web:491]
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Account</th>
                            <th>Leaves</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentRequests)): ?>
                            <tr>
                                <td colspan="4">No cheque book requests found.[web:500]</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentRequests as $r): ?>
                                <tr>
                                    <td><?php echo formatDate($r['created_at']); ?></td>
                                    <td class="info-small">
                                        <?php echo htmlspecialchars($r['account_number'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="info-small">
                                        <?php echo (int) $r['leaves']; ?> leaves
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = 'badge-pending';
                                        if ($r['status'] === 'approved')
                                            $badgeClass = 'badge-approved';
                                        if ($r['status'] === 'rejected')
                                            $badgeClass = 'badge-rejected';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>">
                                            <?php echo htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>