<?php
require_once '../config.php';
checkRole('ROLE_CUSTOMER');

$user   = $_SESSION['user'] ?? null;
$userId = (int)$user['id'];

// Load all customer's accounts for dropdown
$accounts = fetchAll(
    $conn,
    "SELECT id, account_number, account_type, balance
     FROM accounts
     WHERE user_id = ?
     ORDER BY created_at ASC",
    'i',
    [$userId]
);

// Determine selected account (first one by default)
$selectedId = isset($_GET['account_id']) ? (int)$_GET['account_id'] : 0;
if ($selectedId <= 0 && !empty($accounts)) {
    $selectedId = (int)$accounts[0]['id'];
}

// Fetch selected account details (ensure it belongs to this customer)
$account = null;
$recentTx = [];

if ($selectedId > 0) {
    $account = fetchOne(
        $conn,
        "SELECT * FROM accounts WHERE id = ? AND user_id = ?",
        'ii',
        [$selectedId, $userId]
    );

    if ($account) {
        $recentTx = fetchAll(
            $conn,
            "SELECT t.*
             FROM transactions t
             WHERE t.account_id = ?
             ORDER BY t.created_at DESC
             LIMIT 5",
            'i',
            [$account['id']]
        );
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Balance Enquiry - SecureBank</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg-primary, #131a1a);
            color: var(--text-primary, #e0e0e0);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .page { min-height: 100vh; }
        .content {
            max-width: 800px;
            margin: 20px auto 30px;
            padding: 0 18px 22px;
        }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .subtitle {
            font-size: 12px;
            color: var(--text-secondary, #c0c0c0);
            margin-bottom: 14px;
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
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
            margin-bottom: 10px;
        }
        .form-group {
            font-size: 13px;
            min-width: 220px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        label { font-size: 13px; }
        select {
            width: 100%;
            padding: 8px 9px;
            border-radius: 8px;
            border: 1px solid var(--border-color, #2a3334);
            background: var(--bg-primary, #131a1a);
            color: var(--text-primary, #e0e0e0);
            font-size: 13px;
        }
        .btn {
            border-radius: 999px;
            padding: 8px 16px;
            border: none;
            background: var(--primary, #2d5f82);
            color: #fff;
            font-size: 13px;
            cursor: pointer;
        }
        .balance-display {
            margin-top: 10px;
            padding: 12px 11px;
            border-radius: 10px;
            background: radial-gradient(circle at top left, #23445f, #131a1a);
            border: 1px solid rgba(45,95,130,0.5);
        }
        .balance-label {
            font-size: 12px;
            color: #c0e0ff;
        }
        .balance-value {
            font-size: 24px;
            font-weight: 700;
            margin-top: 4px;
        }
        .info-small {
            font-size: 11px;
            color: var(--text-secondary, #c0c0c0);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            padding: 6px 4px;
            border-bottom: 1px solid var(--border-color, #2a3334);
            text-align: left;
        }
        th {
            font-weight: 600;
            color: var(--text-secondary, #c0c0c0);
        }
        tr:last-child td { border-bottom: none; }
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: 11px;
        }
        .badge-deposit {
            background: rgba(46,184,111,0.18);
            color: var(--success, #2eb86f);
        }
        .badge-withdrawal {
            background: rgba(201,31,47,0.18);
            color: var(--danger, #c91f2f);
        }
        .badge-transfer {
            background: rgba(244,165,66,0.18);
            color: var(--warning, #f4a542);
        }
    </style>
</head>
<body>
<div class="page">
    <?php include 'header.php'; ?>

    <div class="content">
        <h1>Balance Enquiry</h1>
        <p class="subtitle">
            Select an account to see its current balance and a short list of recent transactions.[web:291][web:335]
        </p>

        <div class="card">
            <div class="card-title">Select Account</div>
            <div class="card-subtitle">
                Choose one of your accounts to view the latest available balance.[web:409]
            </div>

            <form method="get">
                <div class="form-row">
                    <div class="form-group">
                        <label for="account_id">Account</label>
                        <select id="account_id" name="account_id">
                            <?php foreach ($accounts as $acc): ?>
                                <option
                                    value="<?php echo (int)$acc['id']; ?>"
                                    <?php if ($selectedId === (int)$acc['id']) echo 'selected'; ?>
                                >
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
                    </div>
                    <button type="submit" class="btn">Show Balance</button>
                </div>
            </form>

            <?php if ($account): ?>
                <div class="balance-display">
                    <div class="balance-label">Current balance for account <?php
                        echo htmlspecialchars($account['account_number'], ENT_QUOTES, 'UTF-8');
                    ?></div>
                    <div class="balance-value">
                        <?php echo formatCurrency((float)$account['balance']); ?>
                    </div>
                    <div class="info-small">
                        Account type: <?php echo htmlspecialchars(ucfirst($account['account_type']), ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>
            <?php elseif (!empty($accounts)): ?>
                <div class="info-small" style="margin-top:8px;">Select an account and click “Show Balance”.[web:291]</div>
            <?php else: ?>
                <div class="info-small" style="margin-top:8px;">You do not have any accounts yet.[web:308]</div>
            <?php endif; ?>
        </div>

        <?php if ($account): ?>
            <div class="card">
                <div class="card-title">Recent Transactions</div>
                <div class="card-subtitle">
                    Last 5 transactions that affected this account’s balance.[web:308]
                </div>

                <table>
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Balance After</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($recentTx)): ?>
                        <tr><td colspan="4">No recent transactions for this account.[web:323]</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentTx as $t): ?>
                            <tr>
                                <td><?php echo formatDate($t['created_at']); ?></td>
                                <td>
                                    <?php
                                    $badgeClass = 'badge-deposit';
                                    if ($t['type'] === 'withdrawal') $badgeClass = 'badge-withdrawal';
                                    if ($t['type'] === 'transfer')   $badgeClass = 'badge-transfer';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo htmlspecialchars(ucfirst($t['type']), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>
                                <td><?php echo formatCurrency((float)$t['amount']); ?></td>
                                <td><?php echo formatCurrency((float)($t['balance_after'] ?? $account['balance'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
