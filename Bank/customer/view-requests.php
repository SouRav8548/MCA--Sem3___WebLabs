<?php
require_once '../config.php';
checkRole(ROLE_CUSTOMER);

$user = $_SESSION['user'] ?? null;
$userId = (int) $user['id'];

// Fetch ATM card requests
$atmRequests = fetchAll(
    $conn,
    "SELECT r.*, a.account_number
     FROM atm_card_requests r
     LEFT JOIN accounts a ON a.id = r.account_id
     WHERE r.user_id = ?
     ORDER BY r.created_at DESC",
    'i',
    [$userId]
);

// Fetch cheque book requests
$chequeRequests = fetchAll(
    $conn,
    "SELECT r.*, a.account_number
     FROM cheque_book_requests r
     LEFT JOIN accounts a ON a.id = r.account_id
     WHERE r.user_id = ?
     ORDER BY r.created_at DESC",
    'i',
    [$userId]
);

// Fetch FD requests
$fdRequests = fetchAll(
    $conn,
    "SELECT r.*, a.account_number
     FROM fd_requests r
     LEFT JOIN accounts a ON a.id = r.source_account_id
     WHERE r.user_id = ?
     ORDER BY r.created_at DESC",
    'i',
    [$userId]
);

// Fetch RD requests
$rdRequests = fetchAll(
    $conn,
    "SELECT r.*, a.account_number
     FROM rd_requests r
     LEFT JOIN accounts a ON a.id = r.source_account_id
     WHERE r.user_id = ?
     ORDER BY r.created_at DESC",
    'i',
    [$userId]
);

// Fetch account-opening requests
$accountOpenRequests = fetchAll(
    $conn,
    "SELECT r.*, b.name AS branch_name
     FROM account_open_requests r
     LEFT JOIN branches b ON b.id = r.branch_id
     WHERE r.user_id = ?
     ORDER BY r.created_at DESC",
    'i',
    [$userId]
);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>My Service Requests - SecureBank</title>
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

        .section {
            margin-bottom: 18px;
        }

        .card {
            background: var(--bg-secondary, #181f20);
            border-radius: 12px;
            padding: 12px 11px 10px;
            border: 1px solid var(--border-color, #2a3334);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 6px;
        }

        .card-title {
            font-size: 14px;
            font-weight: 600;
        }

        .card-subtitle {
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
            <h1>My Service Requests</h1>
            <p class="subtitle">
                Overview of all non‑financial requests you have placed, such as ATM cards, cheque books and deposit
                requests.[web:291][web:562]
            </p>

            <div class="section">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">ATM / Debit Card Requests</div>
                        <div class="card-subtitle">New or replacement card requests raised from internet
                            banking.[web:403]</div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Account</th>
                                <th>Card Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($atmRequests)): ?>
                                <tr>
                                    <td colspan="4">No ATM card requests found.[web:491]</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($atmRequests as $r): ?>
                                    <tr>
                                        <td><?php echo formatDate($r['created_at']); ?></td>
                                        <td class="info-small">
                                            <?php echo htmlspecialchars($r['account_number'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td class="info-small">
                                            <?php echo htmlspecialchars($r['card_type'], ENT_QUOTES, 'UTF-8'); ?>
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

            <div class="section">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Cheque Book Requests</div>
                        <div class="card-subtitle">Requests for new cheque books on savings/current
                            accounts.[web:291][web:490]</div>
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
                            <?php if (empty($chequeRequests)): ?>
                                <tr>
                                    <td colspan="4">No cheque book requests found.[web:296]</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($chequeRequests as $r): ?>
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

            <div class="section">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Fixed Deposit Requests</div>
                        <div class="card-subtitle">FD booking requests raised from your accounts.[web:4][web:564]</div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Account</th>
                                <th>Amount</th>
                                <th>Tenure</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($fdRequests)): ?>
                                <tr>
                                    <td colspan="5">No fixed deposit requests found.[web:561]</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($fdRequests as $r): ?>
                                    <tr>
                                        <td><?php echo formatDate($r['created_at']); ?></td>
                                        <td class="info-small">
                                            <?php echo htmlspecialchars($r['account_number'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td><?php echo formatCurrency((float) $r['amount']); ?></td>
                                        <td class="info-small"><?php echo (int) $r['tenure_months']; ?> months</td>
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

            <div class="section">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Recurring Deposit Requests</div>
                        <div class="card-subtitle">RD booking instructions with monthly installments.[web:538]</div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Account</th>
                                <th>Monthly Amt</th>
                                <th>Tenure</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rdRequests)): ?>
                                <tr>
                                    <td colspan="5">No recurring deposit requests found.[web:541]</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($rdRequests as $r): ?>
                                    <tr>
                                        <td><?php echo formatDate($r['created_at']); ?></td>
                                        <td class="info-small">
                                            <?php echo htmlspecialchars($r['account_number'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td><?php echo formatCurrency((float) $r['monthly_amount']); ?></td>
                                        <td class="info-small"><?php echo (int) $r['tenure_months']; ?> months</td>
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

            <div class="section">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Account Opening Requests</div>
                        <div class="card-subtitle">New account opening requests placed through internet
                            banking.[web:291]</div>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Branch</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($accountOpenRequests)): ?>
                                <tr>
                                    <td colspan="4">No account opening requests found.[web:561]</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($accountOpenRequests as $r): ?>
                                    <tr>
                                        <td><?php echo formatDate($r['created_at']); ?></td>
                                        <td class="info-small">
                                            <?php echo htmlspecialchars(ucfirst($r['account_type']), ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td class="info-small">
                                            <?php echo htmlspecialchars($r['branch_name'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
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
    </div>
</body>

</html>