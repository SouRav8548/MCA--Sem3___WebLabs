<?php
require_once '../config.php';
checkRole(ROLE_CUSTOMER);

$user = $_SESSION['user'] ?? null;
$userId = (int) $user['id'];

$message = '';
$error = '';

// Load customer's active accounts (for debit/credit)
$accounts = fetchAll(
    $conn,
    "SELECT id, account_number, account_type, balance
     FROM accounts
     WHERE user_id = ? AND status = 'active'
     ORDER BY created_at ASC",
    'i',
    [$userId]
);

// Simple tenure options (months) and payout modes
$tenureOptions = [6, 12, 24, 36, 60]; // months
$payoutModes = ['on_maturity', 'quarterly', 'monthly'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accountId = (int) post('account_id');
    $amount = (float) post('amount');
    $tenure = (int) post('tenure');
    $payoutMode = sanitizeInput(post('payout_mode'));
    $instructions = sanitizeInput(post('instructions'));

    $errors = [];

    if ($accountId <= 0) {
        $errors[] = 'Please select a debit account.';
    }
    if ($amount <= 0) {
        $errors[] = 'Please enter a valid deposit amount.';
    }
    if (!in_array($tenure, $tenureOptions, true)) {
        $errors[] = 'Please select a valid tenure.';
    }
    if (!in_array($payoutMode, $payoutModes, true)) {
        $errors[] = 'Please select a valid interest payout option.';
    }

    // Ensure account belongs to customer
    if (!$errors) {
        $acc = fetchOne(
            $conn,
            "SELECT id, balance FROM accounts WHERE id = ? AND user_id = ? AND status = 'active'",
            'ii',
            [$accountId, $userId]
        );
        if (!$acc) {
            $errors[] = 'Invalid account selected.';
        } elseif ($acc['balance'] < $amount) {
            $errors[] = 'Insufficient balance in the selected account for this FD amount.';
        }
    }

    if (!$errors) {
        // Store as FD request; actual debit/FD creation occurs after staff approval
        $stmt = $conn->prepare("
            INSERT INTO fd_requests
                (user_id, source_account_id, amount, tenure_months, payout_mode, status, instructions, created_at)
            VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW())
        ");
        $stmt->bind_param(
            'i d i s s',
            $userId,
            $accountId,
            $amount,
            $tenure,
            $payoutMode,
            $instructions
        );
        // Note: if your MySQLi version dislikes spaces in 'd i s s' change to 'idiss'

        if ($stmt->execute()) {
            $message = 'Your fixed deposit request has been submitted and is pending bank approval.';
            logActivity(
                $conn,
                $userId,
                'customer_fd_request',
                "Customer requested FD of {$amount} for {$tenure} months from account ID {$accountId}"
            );
        } else {
            $error = 'Failed to submit fixed deposit request. Please try again later.';
        }
        $stmt->close();
    } else {
        $error = implode(' ', $errors);
    }
}

// Fetch recent FD requests
$recentFD = fetchAll(
    $conn,
    "SELECT r.*, a.account_number
     FROM fd_requests r
     LEFT JOIN accounts a ON a.id = r.source_account_id
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
    <title>Fixed Deposit Request - SecureBank</title>
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
        input[type="number"],
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

        input:focus,
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
            <h1>Fixed Deposit Request</h1>
            <p class="subtitle">
                Create a fixed deposit request by choosing a debit account, deposit amount, and tenure. The bank will
                process and confirm the FD details.[web:506][web:511]
            </p>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="card">
                    <div class="card-title">FD Request Details</div>
                    <div class="card-subtitle">
                        Choose the funding account, amount to invest, and deposit tenure.[web:502][web:510]
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="account_id">Debit From Account *</label>
                            <select id="account_id" name="account_id" required>
                                <option value="">Select account</option>
                                <?php foreach ($accounts as $acc): ?>
                                    <option value="<?php echo (int) $acc['id']; ?>">
                                        <?php
                                        echo htmlspecialchars(
                                            $acc['account_number'] . ' • ' . ucfirst($acc['account_type']) .
                                            ' • Bal: ' . formatCurrency((float) $acc['balance']),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="hint">FD amount will be debited from this account after approval.[web:4]</div>
                        </div>
                        <div class="form-group">
                            <label for="amount">Deposit Amount *</label>
                            <input type="number" id="amount" name="amount" min="1" step="0.01" required>
                            <div class="hint">Enter the principal amount for the fixed deposit (e.g., 50000).[web:10]
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="tenure">Tenure (months) *</label>
                            <select id="tenure" name="tenure" required>
                                <option value="">Select tenure</option>
                                <?php foreach ($tenureOptions as $t): ?>
                                    <option value="<?php echo (int) $t; ?>"><?php echo (int) $t; ?> months</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="hint">Typical FD tenures range from 6 months to 5 years.[web:505]</div>
                        </div>
                        <div class="form-group">
                            <label for="payout_mode">Interest Payout *</label>
                            <select id="payout_mode" name="payout_mode" required>
                                <option value="">Select option</option>
                                <option value="on_maturity">On maturity (cumulative)</option>
                                <option value="quarterly">Quarterly payout</option>
                                <option value="monthly">Monthly payout</option>
                            </select>
                            <div class="hint">Choose whether interest is paid periodically or on maturity only.[web:506]
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-title">Additional Instructions</div>
                    <div class="card-subtitle">
                        Provide any special instructions (renewal preference, maturity credit account,
                        etc.).[web:506][web:509]
                    </div>
                    <div class="form-group">
                        <label for="instructions">Instructions (optional)</label>
                        <textarea id="instructions" name="instructions"
                            placeholder="Example: Renew principal on maturity; credit interest to same account."></textarea>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Submit FD Request</button>
                <div class="info-small" style="margin-top:8px;">
                    Actual interest rate and maturity amount will be confirmed by the bank when the FD is
                    opened.[web:505][web:311]
                </div>
            </form>

            <div class="card" style="margin-top:18px;">
                <div class="card-title">Recent FD Requests</div>
                <div class="card-subtitle">
                    Track the latest fixed deposit requests submitted from your profile.[web:335]
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Account</th>
                            <th>Amount</th>
                            <th>Tenure</th>
                            <th>Payout</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentFD)): ?>
                            <tr>
                                <td colspan="6">No fixed deposit requests found.[web:308]</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentFD as $r): ?>
                                <tr>
                                    <td><?php echo formatDate($r['created_at']); ?></td>
                                    <td class="info-small">
                                        <?php echo htmlspecialchars($r['account_number'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td><?php echo formatCurrency((float) $r['amount']); ?></td>
                                    <td class="info-small"><?php echo (int) $r['tenure_months']; ?> months</td>
                                    <td class="info-small">
                                        <?php
                                        $label = 'On maturity';
                                        if ($r['payout_mode'] === 'quarterly')
                                            $label = 'Quarterly';
                                        if ($r['payout_mode'] === 'monthly')
                                            $label = 'Monthly';
                                        echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
                                        ?>
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