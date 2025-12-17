<?php
require_once '../config.php';
checkAnyRole([ROLE_ADMIN, ROLE_MANAGER]);

// Filters
$dateFrom = sanitizeInput(get('from', ''));
$dateTo = sanitizeInput(get('to', ''));
$type = sanitizeInput(get('type', 'all'));   // all, deposit, withdrawal, transfer
$account = sanitizeInput(get('account', ''));
$customer = sanitizeInput(get('customer', ''));  // name/username/email/phone

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
if ($account !== '') {
    $where .= " AND a.account_number LIKE CONCAT('%', ?, '%')";
    $types .= 's';
    $params[] = $account;
}
if ($customer !== '') {
    $where .= " AND (u.full_name LIKE CONCAT('%', ?, '%')
                    OR u.username LIKE CONCAT('%', ?, '%')
                    OR u.email LIKE CONCAT('%', ?, '%')
                    OR u.phone LIKE CONCAT('%', ?, '%'))";
    $types .= 'ssss';
    $params[] = $customer;
    $params[] = $customer;
    $params[] = $customer;
    $params[] = $customer;
}

// Fetch filtered transactions (pagination could be added later)
$sql = "
    SELECT t.*, a.account_number, u.full_name, u.username
    FROM transactions t
    LEFT JOIN accounts a ON a.id = t.account_id
    LEFT JOIN users u ON u.id = a.user_id
    WHERE $where
    ORDER BY t.created_at DESC
    LIMIT 200
";
$transactions = fetchAll($conn, $sql, $types, $params);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>All Transactions - SecureBank</title>
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
            margin-bottom: 12px;
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
        input[type="text"],
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
            <h1>All Transactions</h1>
            <p class="subtitle">
                Complete list of account transactions with filters by date, type, account number, and customer
                details.[web:7][web:35]
            </p>

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
                            <label for="type">Type</label>
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
                            <label for="account">Account number</label>
                            <input type="text" id="account" name="account" placeholder="e.g. 1234"
                                value="<?php echo htmlspecialchars($account, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="customer">Customer (name/email/phone)</label>
                            <input type="text" id="customer" name="customer" placeholder="Search customer"
                                value="<?php echo htmlspecialchars($customer, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn">Apply</button>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-secondary"
                                onclick="window.location='view-transactions.php'">Clear</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-card">
                <div class="table-header">
                    <div>
                        <div class="table-title">Transactions (max 200)</div>
                        <div class="table-subtitle">
                            Results ordered by newest first. Use filters to narrow down to a specific account or
                            customer.[web:90]
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
                                    <td colspan="6">No transactions found for the selected filters.[web:332]</td>
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