<?php
require_once '../config.php';
checkRole(ROLE_CUSTOMER);

$user = $_SESSION['user'] ?? null;
$userId = (int) $user['id'];

// Fetch customer's accounts for filter dropdown
$accounts = fetchAll(
    $conn,
    "SELECT id, account_number
     FROM accounts
     WHERE user_id = ?
     ORDER BY created_at ASC",
    'i',
    [$userId]
);

// Filters
$accountFilter = get('account');      // account_number
$typeFilter = get('type');         // deposit/withdrawal/transfer
$fromDate = get('from');         // Y-m-d
$toDate = get('to');           // Y-m-d
$page = max(1, (int) get('page', 1));
$perPage = 20;

// Build WHERE
$where = "a.user_id = ?";
$params = [$userId];
$types = 'i';

if ($accountFilter !== '') {
    $where .= " AND a.account_number = ?";
    $params[] = $accountFilter;
    $types .= 's';
}
if ($typeFilter !== '' && in_array($typeFilter, ['deposit', 'withdrawal', 'transfer'], true)) {
    $where .= " AND t.type = ?";
    $params[] = $typeFilter;
    $types .= 's';
}
if ($fromDate !== '' && $toDate !== '') {
    $where .= " AND DATE(t.created_at) BETWEEN ? AND ?";
    $params[] = $fromDate;
    $params[] = $toDate;
    $types .= 'ss';
} elseif ($fromDate !== '') {
    $where .= " AND DATE(t.created_at) >= ?";
    $params[] = $fromDate;
    $types .= 's';
} elseif ($toDate !== '') {
    $where .= " AND DATE(t.created_at) <= ?";
    $params[] = $toDate;
    $types .= 's';
}

// Count total
$countSql = "
    SELECT COUNT(*) AS cnt
    FROM transactions t
    JOIN accounts a ON a.id = t.account_id
    WHERE $where
";
$rowCount = fetchOne($conn, $countSql, $types, $params);
$total = (int) ($rowCount['cnt'] ?? 0);
$pages = max(1, (int) ceil($total / $perPage));
$offset = ($page - 1) * $perPage;

// Fetch page
$listSql = "
    SELECT t.*, a.account_number
    FROM transactions t
    JOIN accounts a ON a.id = t.account_id
    WHERE $where
    ORDER BY t.created_at DESC
    LIMIT $offset, $perPage
";
$transactions = fetchAll($conn, $listSql, $types, $params);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Transaction History - SecureBank</title>
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

        .card {
            background: var(--bg-secondary, #181f20);
            border-radius: 12px;
            padding: 14px 12px 12px;
            border: 1px solid var(--border-color, #2a3334);
            margin-bottom: 14px;
        }

        .card-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .card-subtitle {
            font-size: 11px;
            color: var(--text-secondary, #c0c0c0);
            margin-bottom: 8px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 8px 10px;
            align-items: flex-end;
        }

        .form-group {
            font-size: 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        label {
            font-size: 12px;
        }

        select,
        input[type="date"] {
            width: 100%;
            padding: 7px 8px;
            border-radius: 8px;
            border: 1px solid var(--border-color, #2a3334);
            background: var(--bg-primary, #131a1a);
            color: var(--text-primary, #e0e0e0);
            font-size: 12px;
        }

        select:focus,
        input[type="date"]:focus {
            outline: none;
            border-color: var(--primary, #2d5f82);
        }

        .btn {
            border-radius: 999px;
            padding: 8px 14px;
            border: none;
            background: var(--primary, #2d5f82);
            color: #fff;
            font-size: 12px;
            cursor: pointer;
        }

        .btn.secondary {
            background: transparent;
            color: var(--text-secondary, #c0c0c0);
            border: 1px solid var(--border-color, #2a3334);
        }

        .btn+.btn {
            margin-left: 6px;
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

        .pagination {
            margin-top: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            font-size: 12px;
        }

        .page-link {
            padding: 4px 8px;
            border-radius: 999px;
            border: 1px solid var(--border-color, #2a3334);
            text-decoration: none;
            color: var(--text-secondary, #c0c0c0);
        }

        .page-link.active {
            background: var(--primary, #2d5f82);
            color: #fff;
            border-color: var(--primary, #2d5f82);
        }

        .page-link:hover {
            border-color: var(--primary, #2d5f82);
        }

        @media (max-width: 768px) {
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
            <h1>Transaction History</h1>
            <p class="subtitle">
                View detailed history of deposits, withdrawals and transfers on your accounts with date and type
                filters.[web:4][web:545]
            </p>

            <div class="card">
                <div class="card-title">Filter</div>
                <div class="card-subtitle">
                    Narrow down transactions by account, type and date range.[web:90]
                </div>
                <form method="get">
                    <div class="filter-grid">
                        <div class="form-group">
                            <label for="account">Account</label>
                            <select id="account" name="account">
                                <option value="">All accounts</option>
                                <?php foreach ($accounts as $acc): ?>
                                    <option
                                        value="<?php echo htmlspecialchars($acc['account_number'], ENT_QUOTES, 'UTF-8'); ?>"
                                        <?php if ($accountFilter === $acc['account_number'])
                                            echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($acc['account_number'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="type">Type</label>
                            <select id="type" name="type">
                                <option value="">All types</option>
                                <option value="deposit" <?php if ($typeFilter === 'deposit')
                                    echo 'selected'; ?>>Deposit
                                </option>
                                <option value="withdrawal" <?php if ($typeFilter === 'withdrawal')
                                    echo 'selected'; ?>>
                                    Withdrawal</option>
                                <option value="transfer" <?php if ($typeFilter === 'transfer')
                                    echo 'selected'; ?>>
                                    Transfer</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="from">From date</label>
                            <input type="date" id="from" name="from"
                                value="<?php echo htmlspecialchars($fromDate, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="to">To date</label>
                            <input type="date" id="to" name="to"
                                value="<?php echo htmlspecialchars($toDate, ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn">Apply</button>
                            <a href="transactions.php" class="btn secondary"
                                style="display:inline-block;text-align:center;margin-top:4px;">Reset</a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card">
                <div class="card-title">Transactions</div>
                <div class="card-subtitle">
                    Showing <?php echo $total ? ($offset + 1) : 0; ?>–<?php echo min($offset + $perPage, $total); ?> of
                    <?php echo $total; ?> records.[web:4]
                </div>

                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Account</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transactions)): ?>
                                <tr>
                                    <td colspan="5">No transactions found for the selected criteria.[web:335]</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($transactions as $t): ?>
                                    <tr>
                                        <td><?php echo date('Y-m-d H:i', strtotime($t['created_at'])); ?></td>
                                        <td class="info-small">
                                            <?php echo htmlspecialchars($t['account_number'], ENT_QUOTES, 'UTF-8'); ?>
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
                                        <td class="info-small">
                                            <?php echo htmlspecialchars($t['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($pages > 1): ?>
                    <div class="pagination">
                        <?php for ($p = 1; $p <= $pages; $p++): ?>
                            <?php
                            $query = $_GET;
                            $query['page'] = $p;
                            $url = 'transactions.php?' . http_build_query($query);
                            ?>
                            <a href="<?php echo htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>"
                                class="page-link <?php echo $p === $page ? 'active' : ''; ?>">
                                <?php echo $p; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>