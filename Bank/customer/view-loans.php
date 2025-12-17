<?php
require_once '../config.php';
checkRole(ROLE_CUSTOMER);

$user = $_SESSION['user'] ?? null;
$userId = (int) $user['id'];

// Fetch customer loans/requests
$loans = fetchAll(
    $conn,
    "SELECT *
     FROM loans
     WHERE user_id = ?
     ORDER BY created_at DESC",
    'i',
    [$userId]
);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>My Loans - SecureBank</title>
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

        .badge-rejected,
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
            <h1>My Loans</h1>
            <p class="subtitle">
                View all your loan applications and active loans with status, amount, tenure and type.[web:512][web:517]
            </p>

            <div class="card">
                <div class="card-title">Loan Summary</div>
                <div class="card-subtitle">
                    Includes pending applications as well as approved or closed loans.[web:552]
                </div>

                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Tenure</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($loans)): ?>
                                <tr>
                                    <td colspan="6">You do not have any loan applications or active loans yet.[web:374]</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($loans as $loan): ?>
                                    <tr>
                                        <td><?php echo formatDate($loan['created_at']); ?></td>
                                        <td class="info-small">
                                            <?php echo htmlspecialchars(ucfirst($loan['loan_type']), ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                        <td><?php echo formatCurrency((float) $loan['amount']); ?></td>
                                        <td class="info-small">
                                            <?php echo (int) $loan['tenure_months']; ?> months
                                        </td>
                                        <td>
                                            <?php
                                            $status = $loan['status'];
                                            $badgeClass = 'badge-pending';
                                            if ($status === 'approved' || $status === 'active')
                                                $badgeClass = 'badge-approved';
                                            if ($status === 'rejected' || $status === 'closed')
                                                $badgeClass = 'badge-closed';
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>">
                                                <?php echo htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </td>
                                        <td class="info-small">
                                            <?php echo htmlspecialchars($loan['notes'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="info-small" style="margin-top:8px;">
                    For a detailed repayment schedule or statement, contact the branch or view the dedicated loan
                    statement page if available.[web:517]
                </div>
            </div>
        </div>
    </div>
</body>

</html>