<?php
require_once '../config.php';
checkRole(ROLE_CUSTOMER);

$user = $_SESSION['user'] ?? null;
$userId = (int) $user['id'];

// Fetch customer's accounts
$accounts = fetchAll(
    $conn,
    "SELECT a.*, b.name AS branch_name
     FROM accounts a
     LEFT JOIN branches b ON b.id = a.branch_id
     WHERE a.user_id = ?
     ORDER BY a.created_at ASC",
    'i',
    [$userId]
);

// Aggregate balances
$totalBalance = 0;
foreach ($accounts as $acc) {
    $totalBalance += (float) $acc['balance'];
}

// Recent transactions for customer
$recentTx = fetchAll(
    $conn,
    "SELECT t.*, a.account_number
     FROM transactions t
     JOIN accounts a ON a.id = t.account_id
     WHERE a.user_id = ?
     ORDER BY t.created_at DESC
     LIMIT 10",
    'i',
    [$userId]
);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>My Dashboard - SecureBank</title>
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
            padding: 16px 18px 22px;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .stat-card {
            background: var(--bg-secondary, #181f20);
            border-radius: 12px;
            padding: 12px 11px;
            border: 1px solid var(--border-color, #2a3334);
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-secondary, #c0c0c0);
        }

        .stat-value {
            font-size: 19px;
            font-weight: 700;
            margin-top: 4px;
        }

        .stat-tag {
            font-size: 11px;
            margin-top: 4px;
            color: var(--primary, #2d5f82);
        }

        .panel-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(0, 1.1fr);
            gap: 16px;
        }

        .panel {
            background: var(--bg-secondary, #181f20);
            border-radius: 12px;
            border: 1px solid var(--border-color, #2a3334);
            padding: 14px 12px 12px;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .panel-title {
            font-size: 14px;
            font-weight: 600;
        }

        .panel-subtitle {
            font-size: 11px;
            color: var(--text-secondary, #c0c0c0);
        }

        .link-sm {
            font-size: 11px;
            color: var(--primary, #2d5f82);
            text-decoration: none;
        }

        .link-sm:hover {
            text-decoration: underline;
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

        .badge-active {
            background: rgba(46, 184, 111, 0.18);
            color: var(--success, #2eb86f);
        }

        .badge-closed {
            background: rgba(201, 31, 47, 0.18);
            color: var(--danger, #c91f2f);
        }

        .badge-deposit {
            background: rgba(46, 184, 111, 0.18);
            color: var(--success, #2eb86f);
        }

        .badge-withdrawal {
            background: rgba(201, 31, 47, 0.18);
            color: var(--danger, #c91f2f);
        }

        .badge-transfer {
            background: rgba(244, 165, 66, 0.18);
            color: var(--warning, #f4a542);
        }

        .info-small {
            font-size: 11px;
            color: var(--text-secondary, #c0c0c0);
        }

        @media (max-width: 800px) {
            .content {
                padding: 12px;
            }

            .panel-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <?php include 'header.php'; ?>

        <div class="content">
            <h1>Hello, <?php echo htmlspecialchars($user['full_name'] ?: $user['username'], ENT_QUOTES, 'UTF-8'); ?>
            </h1>
            <p class="subtitle">
                Overview of your accounts, balances and latest transactions with quick access to transfers and service
                requests.[web:4][web:403]
            </p>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Balance</div>
                    <div class="stat-value"><?php echo formatCurrency($totalBalance); ?></div>
                    <div class="stat-tag">Sum across all your accounts.[web:4]</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Number of Accounts</div>
                    <div class="stat-value"><?php echo count($accounts); ?></div>
                    <div class="stat-tag">Savings, current and others.[web:403]</div>
                </div>
            </div>

            <div class="panel-grid">
                <div class="panel">
                    <div class="panel-header">
                        <div>
                            <div class="panel-title">My Accounts</div>
                            <div class="panel-subtitle">
                                Account numbers, types, branches and current balances.[web:4]
                            </div>
                        </div>
                        <a href="accounts.php" class="link-sm">View all</a>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th>Type / Branch</th>
                                <th>Status</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($accounts)): ?>
                                <tr>
                                    <td colspan="4">You do not have any active accounts yet.[web:308]</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($accounts as $a): ?>
                                    <tr>
                                        <td>
                                            <div><?php echo htmlspecialchars($a['account_number'], ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <div class="info-small">
                                                Opened <?php echo formatDate($a['created_at']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="info-small">
                                                <?php echo htmlspecialchars(ucfirst($a['account_type']), ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <div class="info-small">
                                                <?php echo htmlspecialchars($a['branch_name'] ?? 'Main Branch', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            $badgeClass = $a['status'] === 'active' ? 'badge-active' : 'badge-closed';
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>">
                                                <?php echo htmlspecialchars($a['status'], ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatCurrency((float) $a['balance']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="panel">
                    <div class="panel-header">
                        <div>
                            <div class="panel-title">Recent Transactions</div>
                            <div class="panel-subtitle">
                                Last 10 movements on your accounts.[web:4][web:335]
                            </div>
                        </div>
                        <a href="transactions.php" class="link-sm">View history</a>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Account</th>
                                <th>Type</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentTx)): ?>
                                <tr>
                                    <td colspan="4">No transactions recorded yet.[web:308]</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentTx as $t): ?>
                                    <tr>
                                        <td><?php echo formatDate($t['created_at']); ?></td>
                                        <td>
                                            <div>
                                                <?php echo htmlspecialchars($t['account_number'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                            $badgeClass = 'badge-deposit';
                                            if ($t['type'] === 'withdrawal')
                                                $badgeClass = 'badge-withdrawal';
                                            if ($t['type'] === 'transfer')
                                                $badgeClass = 'badge-transfer';
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>">
                                                <?php echo htmlspecialchars(ucfirst($t['type']), ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatCurrency((float) $t['amount']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div style="margin-top:10px;font-size:12px;">
                        <a href="transfer.php" class="link-sm">Transfer funds</a> ·
                        <a href="request-atm.php" class="link-sm">Apply for ATM card</a> ·
                        <a href="request-cheque.php" class="link-sm">Request cheque book</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>