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
        $error = 'Invalid customer id.';
    } else {
        if ($action === 'approve' || $action === 'reject') {
            $newStatus = $action === 'approve' ? 'active' : 'rejected';

            $stmt = $conn->prepare("
                UPDATE users
                SET status = ?
                WHERE id = ? AND role = 'customer' AND status = 'pending'
            ");
            $stmt->bind_param('si', $newStatus, $id);
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $message = $action === 'approve'
                    ? 'Customer account approved successfully.'
                    : 'Customer account rejected successfully.';
                $act = $action === 'approve' ? 'approve_customer' : 'reject_customer';
                logActivity($conn, (int) $_SESSION['user']['id'], $act, ucfirst($newStatus) . " customer ID $id");
            } else {
                $error = 'No pending account found to update or operation failed.';
            }
            $stmt->close();
        }
    }
}

// Fetch pending customer accounts with basic KYC
$pending = fetchAll(
    $conn,
    "SELECT u.*, cp.aadhar, cp.pan, cp.city, cp.state
     FROM users u
     LEFT JOIN customer_profile cp ON cp.user_id = u.id
     WHERE u.role = 'customer' AND u.status = 'pending'
     ORDER BY u.created_at ASC"
);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Approve Accounts - SecureBank</title>
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
            <h1>Approve Customer Accounts</h1>
            <p class="subtitle">
                Review new registrations and approve or reject them based on the submitted information and KYC
                details.[web:90][web:140]
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
                            <th>KYC</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Requested On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pending)): ?>
                            <tr>
                                <td colspan="8">No pending customer accounts to approve.[web:256]</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pending as $c): ?>
                                <tr>
                                    <td><?php echo (int) $c['id']; ?></td>
                                    <td>
                                        <div>
                                            <?php echo htmlspecialchars($c['full_name'] ?: $c['username'], ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                        <div class="info-small">
                                            @<?php echo htmlspecialchars($c['username'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($c['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="info-small">
                                            <?php echo htmlspecialchars($c['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td>
                                        <div class="info-small">Aadhaar:
                                            <?php echo htmlspecialchars($c['aadhar'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="info-small">PAN:
                                            <?php echo htmlspecialchars($c['pan'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($c['city'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="info-small">
                                            <?php echo htmlspecialchars($c['state'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge badge-pending">
                                            <?php echo htmlspecialchars($c['status'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($c['created_at']); ?></td>
                                    <td>
                                        <div class="actions">
                                            <button type="button" class="btn-sm btn-sm-success"
                                                onclick="submitDecision(<?php echo (int) $c['id']; ?>, 'approve')">
                                                Approve
                                            </button>
                                            <button type="button" class="btn-sm btn-sm-danger"
                                                onclick="submitDecision(<?php echo (int) $c['id']; ?>, 'reject')">
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
            if (action === 'reject' && !confirm('Reject this customer account?')) {
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