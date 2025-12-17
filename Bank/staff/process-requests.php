<?php
require_once '../config.php';
checkRole(ROLE_STAFF);

$message = '';
$error = '';

// Handle approve / reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post('action');          // approve / reject
    $id = (int) post('id');
    $reqType = post('req_type');        // atm / cheque

    if ($id <= 0 || !in_array($reqType, ['atm', 'cheque'], true)) {
        $error = 'Invalid request payload.';
    } else {
        if ($action === 'approve' || $action === 'reject') {
            $newStatus = $action === 'approve' ? 'approved' : 'rejected';

            if ($reqType === 'atm') {
                $stmt = $conn->prepare("
                    UPDATE atm_card_requests
                    SET status = ?, processed_by = ?, processed_at = NOW()
                    WHERE id = ? AND status = 'pending'
                ");
            } else {
                $stmt = $conn->prepare("
                    UPDATE cheque_book_requests
                    SET status = ?, processed_by = ?, processed_at = NOW()
                    WHERE id = ? AND status = 'pending'
                ");
            }

            $staffId = (int) $_SESSION['user']['id'];
            $stmt->bind_param('sii', $newStatus, $staffId, $id);
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $label = $reqType === 'atm' ? 'ATM card' : 'cheque book';
                $message = ucfirst($label) . ' request ' . $newStatus . ' successfully.';
                $act = $reqType === 'atm'
                    ? ($action === 'approve' ? 'staff_approve_atm' : 'staff_reject_atm')
                    : ($action === 'approve' ? 'staff_approve_cheque' : 'staff_reject_cheque');
                logActivity($conn, $staffId, $act, ucfirst($newStatus) . " {$label} request ID $id");
            } else {
                $error = 'No pending request found or operation failed.';
            }
            $stmt->close();
        }
    }
}

// Fetch pending ATM and cheque requests
$atmRequests = fetchAll(
    $conn,
    "SELECT r.*, u.full_name, u.username, u.email, u.phone
     FROM atm_card_requests r
     LEFT JOIN users u ON u.id = r.user_id
     WHERE r.status = 'pending'
     ORDER BY r.created_at ASC"
);

$chequeRequests = fetchAll(
    $conn,
    "SELECT r.*, u.full_name, u.username, u.email, u.phone
     FROM cheque_book_requests r
     LEFT JOIN users u ON u.id = r.user_id
     WHERE r.status = 'pending'
     ORDER BY r.created_at ASC"
);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Staff · Process Requests</title>
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

        h2 {
            font-size: 16px;
            margin: 16px 0 6px;
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
            <h1>Process Service Requests</h1>
            <p class="subtitle">
                Staff view to approve or reject pending ATM card and cheque book requests from
                customers.[web:291][web:39]
            </p>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <h2>ATM Card Requests</h2>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Account</th>
                            <th>Card Type</th>
                            <th>Status</th>
                            <th>Requested On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($atmRequests)): ?>
                            <tr>
                                <td colspan="8">No pending ATM card requests.[web:292]</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($atmRequests as $r): ?>
                                <tr>
                                    <td><?php echo (int) $r['id']; ?></td>
                                    <td>
                                        <div>
                                            <?php echo htmlspecialchars($r['full_name'] ?: $r['username'], ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                        <div class="info-small">
                                            @<?php echo htmlspecialchars($r['username'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($r['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="info-small">
                                            <?php echo htmlspecialchars($r['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td class="info-small">
                                        <?php echo htmlspecialchars($r['account_number'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="info-small">
                                        <?php echo htmlspecialchars($r['card_type'] ?? 'Standard', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-pending">
                                            <?php echo htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($r['created_at']); ?></td>
                                    <td>
                                        <div class="actions">
                                            <button type="button" class="btn-sm btn-sm-success"
                                                onclick="submitDecision(<?php echo (int) $r['id']; ?>, 'atm', 'approve')">
                                                Approve
                                            </button>
                                            <button type="button" class="btn-sm btn-sm-danger"
                                                onclick="submitDecision(<?php echo (int) $r['id']; ?>, 'atm', 'reject')">
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

            <h2>Cheque Book Requests</h2>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Account</th>
                            <th>Leaves</th>
                            <th>Status</th>
                            <th>Requested On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($chequeRequests)): ?>
                            <tr>
                                <td colspan="8">No pending cheque book requests.[web:296]</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($chequeRequests as $r): ?>
                                <tr>
                                    <td><?php echo (int) $r['id']; ?></td>
                                    <td>
                                        <div>
                                            <?php echo htmlspecialchars($r['full_name'] ?: $r['username'], ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                        <div class="info-small">
                                            @<?php echo htmlspecialchars($r['username'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($r['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="info-small">
                                            <?php echo htmlspecialchars($r['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                                    </td>
                                    <td class="info-small">
                                        <?php echo htmlspecialchars($r['account_number'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td class="info-small">
                                        <?php echo (int) ($r['leaves'] ?? 0); ?> leaves
                                    </td>
                                    <td>
                                        <span class="badge badge-pending">
                                            <?php echo htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($r['created_at']); ?></td>
                                    <td>
                                        <div class="actions">
                                            <button type="button" class="btn-sm btn-sm-success"
                                                onclick="submitDecision(<?php echo (int) $r['id']; ?>, 'cheque', 'approve')">
                                                Approve
                                            </button>
                                            <button type="button" class="btn-sm btn-sm-danger"
                                                onclick="submitDecision(<?php echo (int) $r['id']; ?>, 'cheque', 'reject')">
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
        function submitDecision(id, reqType, action) {
            if (action === 'reject' && !confirm('Reject this request?')) {
                return;
            }
            const f = document.createElement('form');
            f.method = 'post';
            f.innerHTML =
                '<input type="hidden" name="action" value="' + action + '">' +
                '<input type="hidden" name="req_type" value="' + reqType + '">' +
                '<input type="hidden" name="id" value="' + id + '">';
            document.body.appendChild(f);
            f.submit();
        }
    </script>
</body>

</html>