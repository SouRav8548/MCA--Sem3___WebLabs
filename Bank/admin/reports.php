<?php
require_once '../config.php';
checkAnyRole([ROLE_ADMIN, ROLE_MANAGER]);

// Filters
$dateFrom = sanitizeInput(get('from', ''));
$dateTo = sanitizeInput(get('to', ''));
$type = sanitizeInput(get('type', 'all')); // all, deposit, withdrawal, transfer

$where = "1=1";
$params = [];
$types = '';

if ($dateFrom !== '') {
    $where .= " AND DATE(t.created_at) >= ?";
    $types .= 's';
    $params[] = $dateFrom;
}
if ($dateTo !== '') {
    $where .= " AND DATE(t.created_at) <= ?";
    $types .= 's';
    $params[] = $dateTo;
}
if ($type !== '' && $type !== 'all') {
    $where .= " AND t.type = ?";
    $types .= 's';
    $params[] = $type;
}

// Summary metrics
$totalCustomers = fetchOne($conn, "SELECT COUNT(*) AS c FROM users WHERE role = 'customer'", '')['c'] ?? 0;
$totalAccounts = fetchOne($conn, "SELECT COUNT(*) AS c FROM accounts", '')['c'] ?? 0;

$totalBalanceRow = fetchOne($conn, "SELECT SUM(balance) AS total FROM accounts", '');
$totalBalance = (float) ($totalBalanceRow['total'] ?? 0);

$sumDepositsRow = fetchOne(
    $conn,
    "SELECT SUM(amount) AS total FROM transactions t WHERE t.type = 'deposit'",
    ''
);
$sumWithdrawalsRow = fetchOne(
    $conn,
    "SELECT SUM(amount) AS total FROM transactions t WHERE t.type = 'withdrawal'",
    ''
);
$sumTransfersRow = fetchOne(
    $conn,
    "SELECT SUM(amount) AS total FROM transactions t WHERE t.type = 'transfer'",
    ''
);
$totalDeposits = (float) ($sumDepositsRow['total'] ?? 0);
$totalWithdrawals = (float) ($sumWithdrawalsRow['total'] ?? 0);
$totalTransfers = (float) ($sumTransfersRow['total'] ?? 0);
$netFlow = $totalDeposits - $totalWithdrawals; // simple net in/out

// Recent / filtered transactions
$sqlTransactions = "
    SELECT t.*, a.account_number, u.full_name, u.username
    FROM transactions t
    LEFT JOIN accounts a ON a.id = t.account_id
    LEFT JOIN users u ON u.id = a.user_id
    WHERE $where
    ORDER BY t.created_at DESC
    LIMIT 100
";
$transactions = fetchAll($conn, $sqlTransactions, $types, $params);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Reports - SecureBank</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            margin: 0;
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
            margin-bottom: 16px;
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

        .filter-card {
            background: var(--bg-secondary, #181f20);
            border-radius: 12px;
            padding: 12px 12px 10px;
            border: 1px solid var(--border-color, #2a3334);
            margin-bottom: 14px;
        }

        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: flex-end;
        }

        .form-group {
            font-size: 12px;
            min-width: 140px;
        }

        label {
            display: block;
            margin-bottom: 4px;
        }

        input[type="date"],
        select {
            width: 100%;
            padding: 7px 8px;
            border-radius: 8px;
            border: 1px solid var(--border-color, #2a3334);
            background: var(--bg-primary, #131a1a);
            color: var(--text-primary, #e0e0e0);
            font-size: 12px;
        }

        .btn {
            border-radius: 999px;
            padding: 7px 14px;
            border: none;
            background: var(--primary, #2d5f82);
            color: #fff;
            font-size: 13px;
            cursor: pointer;
        }

        .btn-secondary {
            background: transparent;
            border: 1px solid var(--border-color, #2a3334);
            color: var(--text-secondary, #c0c0c0);
        }

        .table-card {
            background: var(--bg-secondary, #181f20);
            border-radius: 12px;
            padding: 12px 12px 10px;
            border: 1px solid var(--border-color, #2a3334);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .table-title {
            font-size: 14px;
            font-weight: 600;
        }

        .table-subtitle {
            font-size: 11px;
            color: var(--text-secondary, #c0c0c0);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th,
        td {
            padding: 7px 6px;
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

        @media (max-width: 680px) {
            .content {
                padding: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <?php include 'header.php'; ?>

        <div class="content">
            <h1>Reports & Analytics</h1>
            <p class="subtitle">
                Overview of customers, accounts and transaction volumes, plus a detailed transaction report with
                filters.[web:4][web:308]
            </p>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Customers</div>
                    <div class="stat-value"><?php echo (int) $totalCustomers; ?></div>
                    <div class="stat-tag">All customer records.[web:4]</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Accounts</div>
                    <div class="stat-value"><?php echo (int) $totalAccounts; ?></div>
                    <div class="stat-tag">Across all branches.[web:308]</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Balance</div>
                    <div class="stat-value"><?php echo formatCurrency($totalBalance); ?></div>
                    <div class="stat-tag">Sum of account balances.[web:309]</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Deposits (All Time)</div>
                    <div class="stat-value"><?php echo formatCurrency($totalDeposits); ?></div>
                    <div class="stat-tag">Sum of deposit transactions.[web:4]</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Withdrawals (All Time)</div>
                    <div class="stat-value"><?php echo formatCurrency($totalWithdrawals); ?></div>
                    <div class="stat-tag">Sum of withdrawals.[web:4]</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Net Inflow (All Time)</div>
                    <div class="stat-value"><?php echo formatCurrency($netFlow); ?></div>
                    <div class="stat-tag">Deposits minus withdrawals.[web:309]</div>
                </div>
            </div>

            <div class="filter-card">
                <form method="get">
                    <div class="filter-row">
                        <div class="form-group">
                            <label for="from">From date</label>
                            <input type="date" id="from" name="from"
                                value="<?php echo htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="to">To date</label>
                            <input type="date" id="to" name="to"
                                value="<?php echo htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="type">Transaction type</label>
                            <select id="type" name="type">
                                <option value="all" <?php if ($type === 'all')
                                    echo 'selected'; ?>>All</option>
                                <option value="deposit" <?php if ($type === 'deposit')
                                    echo 'selected'; ?>>Deposit
                                </option>
                                <option value="withdrawal" <?php if ($type === 'withdrawal')
                                    echo 'selected'; ?>>
                                    Withdrawal</option>
                                <option value="transfer" <?php if ($type === 'transfer')
                                    echo 'selected'; ?>>Transfer
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn">Apply</button>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-secondary"
                                onclick="window.location='reports.php'">Clear</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-card">
                <div class="table-header">
                    <div>
                        <div class="table-title">Transactions (Last 100, Filtered)</div>
                        <div class="table-subtitle">
                            Includes deposits, withdrawals and transfers matching the selected filters.[web:4]
                        </div>
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Account / Customer</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Balance After</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transactions)): ?>
                                <tr>
                                    <td colspan="6">No transactions found for the selected filters.[web:320]</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($transactions as $t): ?>
                                    <tr>
                                        <td><?php echo formatDate($t['created_at']); ?></td>
                                        <td>
                                            <div>
                                                <?php echo htmlspecialchars($t['account_number'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <div class="info-small">
                                                <?php
                                                $name = $t['full_name'] ?: $t['username'] ?: '';
                                                echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                                                ?>
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
                                        <td><?php echo formatCurrency((float) ($t['balance_after'] ?? 0)); ?></td>
                                        <td class="info-small">
                                            <?php echo htmlspecialchars($t['reference'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>