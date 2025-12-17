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
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>My Accounts - SecureBank</title>
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

        .table-card {
            background: var(--bg-secondary, #181f20);
            border-radius: 12px;
            padding: 14px 12px 12px;
            border: 1px solid var(--border-color, #2a3334);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
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

        .badge-active {
            background: rgba(46, 184, 111, 0.18);
            color: var(--success, #2eb86f);
        }

        .badge-closed {
            background: rgba(201, 31, 47, 0.18);
            color: var(--danger, #c91f2f);
        }

        .info-small {
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
            <h1>My Accounts</h1>
            <p class="subtitle">
                All bank accounts registered under your profile, with current status, branch and balance
                details.[web:291][web:7]
            </p>

            <div class="table-card">
                <div class="table-header">
                    <div>
                        <div class="table-title">Account List</div>
                        <div class="table-subtitle">
                            Select an account to view detailed statement or make transfers.[web:407]
                        </div>
                    </div>
                </div>

                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th>Type / Branch</th>
                                <th>Status</th>
                                <th>Balance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($accounts)): ?>
                                <tr>
                                    <td colspan="5">You do not have any active accounts yet.[web:407]</td>
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
                                        <td>
                                            <a class="link-sm"
                                                href="transactions.php?account=<?php echo urlencode($a['account_number']); ?>">
                                                View statement
                                            </a>
                                            &nbsp;·&nbsp;
                                            <a class="link-sm"
                                                href="transfer.php?from=<?php echo urlencode($a['account_number']); ?>">
                                                Transfer from this
                                            </a>
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