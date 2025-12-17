<?php
require_once '../config.php';
checkAnyRole([ROLE_ADMIN, ROLE_MANAGER]);

$message = '';
$error = '';

// Handle approve / reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post('action');
    $id = (int) post('id');

    if ($id <= 0) {
        $error = 'Invalid loan id.';
    } else {
        if ($action === 'approve' || $action === 'reject') {
            $newStatus = $action === 'approve' ? 'approved' : 'rejected';

            $stmt = $conn->prepare("
                UPDATE loans
                SET status = ?
                WHERE id = ? AND status = 'pending'
            ");
            $stmt->bind_param('si', $newStatus, $id);
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $message = $action === 'approve'
                    ? 'Loan application approved successfully.'
                    : 'Loan application rejected successfully.';
                $act = $action === 'approve' ? 'approve_loan' : 'reject_loan';
                logActivity($conn, (int) $_SESSION['user']['id'], $act, ucfirst($newStatus) . " loan ID $id");
            } else {
                $error = 'No pending loan found to update or operation failed.';
            }
            $stmt->close();
        }
    }
}

// Fetch pending loans with customer info
$pendingLoans = fetchAll(
    $conn,
    "SELECT l.*, u.full_name, u.username, u.email, u.phone
     FROM loans l
     LEFT JOIN users u ON u.id = l.user_id
     WHERE l.status = 'pending'
     ORDER BY l.created_at ASC"
);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Approve Loans - SecureBank</title>
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

        .badge-pending {
            background: rgba(244, 165, 66, 0.18);
            color: var(--warning, #f4a542);
        }

        .actions {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }

        .btn-sm {
            border-radius: 999px;
            padding: 3px 8px;
            border: 1px solid var(--border-color, #2a3334);
            background: transparent;
            color: var(--text-secondary, #c0c0c0);
            font-size: 11px;
            cursor: pointer;
        }

        .btn-sm-success {
            border-color: var(--success, #2eb86f);
            color: var(--success, #2eb86f);
        }

        .btn-sm-danger {
            border-color: var(--danger, #c91f2f);
            color: var(--danger, #c91f2f);
        }

        .btn-sm:hover {
            border-color: var(--primary, #2d5f82);
            color: var(--primary, #2d5f82);
        }

        .btn-sm-success:hover {
            border-color: var(--success, #2eb86f);
            color: var(--success, #2eb86f);
        }

        .btn-sm-danger:hover {
            border-color: var(--danger, #c91f2f);
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
            <h1>Approve Loan Applications</h1>
            <p class="subtitle">
                Review loan applications in pending status and decide to approve or reject based on amount, tenure, and
                customer details.[web:273][web:276]
            </p>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Loan Details</th>
                            <th>Status</th>
                            <th>Applied On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pendingLoans)): ?>
                            <tr>
                                <td colspan="7">No pending loan applications.[web:276]</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendingLoans as $loan): ?>
                                <tr>
                                    <td><?php echo (int) $loan['id']; ?></td>
                                    <td>
                                        <div>
                                            <?php echo htmlspecialchars($loan['full_name'] ?: $loan['username'], ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                        <div class="info-small">
                                            @<?php echo htmlspecialchars($loan['username'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($loan['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="info-small">
                                            <?php echo htmlspecialchars($loan['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="info-small">
                                            Amount: <?php echo formatCurrency((float) $loan['amount']); ?>
                                        </div>
                                        <div class="info-small">
                                            Tenure: <?php echo (int) $loan['tenure_months']; ?> months
                                        </div>
                                        <div class="info-small">
                                            Type:
                                            <?php echo htmlspecialchars($loan['loan_type'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                        <div class="info-small">
                                            Purpose:
                                            <?php echo htmlspecialchars($loan['purpose'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-pending">
                                            <?php echo htmlspecialchars($loan['status'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($loan['created_at']); ?></td>
                                    <td>
                                        <div class="actions">
                                            <button type="button" class="btn-sm btn-sm-success"
                                                onclick="submitDecision(<?php echo (int) $loan['id']; ?>, 'approve')">
                                                Approve
                                            </button>
                                            <button type="button" class="btn-sm btn-sm-danger"
                                                onclick="submitDecision(<?php echo (int) $loan['id']; ?>, 'reject')">
                                                Reject
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function submitDecision(id, action) {
            if (action === 'reject' && !confirm('Reject this loan application?')) {
                return;
            }
            const f = document.createElement('form');
            f.method = 'post';
            f.innerHTML =
                '<input type="hidden" name="action" value="' + action + '">' +
                '<input type="hidden" name="id" value="' + id + '">';
            document.body.appendChild(f);
            f.submit();
        }
    </script>
</body>

</html>