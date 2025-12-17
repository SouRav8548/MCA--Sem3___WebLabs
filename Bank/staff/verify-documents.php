<?php
require_once '../config.php';
checkRole(ROLE_STAFF);

$message = '';
$error = '';

// Handle approve / reject of KYC
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post('action');  // approve / reject
    $userId = (int) post('user_id');
    $remarks = trim(post('remarks'));

    if ($userId <= 0 || !in_array($action, ['approve', 'reject'], true)) {
        $error = 'Invalid request.';
    } else {
        $newStatus = $action === 'approve' ? 'approved' : 'rejected';

        $stmt = $conn->prepare("
            UPDATE customer_profile
            SET kyc_status = ?, kyc_remarks = ?, kyc_verified_by = ?, kyc_verified_at = NOW()
            WHERE user_id = ? AND (kyc_status IS NULL OR kyc_status = 'pending')
        ");
        $staffId = (int) $_SESSION['user']['id'];
        $stmt->bind_param('ssii', $newStatus, $remarks, $staffId, $userId);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $message = 'KYC marked as ' . $newStatus . ' successfully.';
            logActivity(
                $conn,
                $staffId,
                $action === 'approve' ? 'staff_approve_kyc' : 'staff_reject_kyc',
                strtoupper($newStatus) . " KYC for user ID $userId"
            );
        } else {
            $error = 'No pending KYC found or update failed.';
        }
        $stmt->close();
    }
}

// Fetch customers with kyc_status pending or null
$pendingKyc = fetchAll(
    $conn,
    "SELECT u.id, u.full_name, u.username, u.email, u.phone,
            cp.aadhar, cp.pan, cp.address, cp.city, cp.state, cp.pincode,
            cp.kyc_status, cp.kyc_remarks, cp.document_front, cp.document_back, cp.created_at
     FROM users u
     JOIN customer_profile cp ON cp.user_id = u.id
     WHERE u.role = 'customer'
       AND (cp.kyc_status IS NULL OR cp.kyc_status = 'pending')
     ORDER BY cp.created_at ASC"
);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Staff · Verify Documents</title>
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

        .info-small {
            font-size: 11px;
            color: var(--text-secondary, #c0c0c0);
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

        .doc-links a {
            color: var(--primary, #2d5f82);
            font-size: 11px;
            text-decoration: none;
            margin-right: 6px;
        }

        .doc-links a:hover {
            text-decoration: underline;
        }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 200;
        }

        .modal-backdrop.active {
            display: flex;
        }

        .modal {
            background: var(--bg-secondary, #181f20);
            border-radius: 12px;
            border: 1px solid var(--border-color, #2a3334);
            padding: 16px 16px 14px;
            width: 100%;
            max-width: 460px;
            font-size: 13px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .modal-title {
            font-size: 15px;
            font-weight: 600;
        }

        .modal-close {
            border: none;
            background: transparent;
            color: var(--text-secondary, #c0c0c0);
            cursor: pointer;
        }

        .kv-row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 5px;
        }

        .kv-label {
            font-size: 12px;
            color: var(--text-secondary, #c0c0c0);
            width: 35%;
        }

        .kv-value {
            font-size: 13px;
            width: 65%;
        }

        textarea {
            width: 100%;
            min-height: 70px;
            resize: vertical;
            padding: 7px 8px;
            border-radius: 8px;
            border: 1px solid var(--border-color, #2a3334);
            background: var(--bg-primary, #131a1a);
            color: var(--text-primary, #e0e0e0);
            font-size: 13px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 8px;
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
    </style>
</head>

<body>
    <div class="page">
        <?php include 'header.php'; ?>

        <div class="content">
            <h1>Verify Customer Documents</h1>
            <p class="subtitle">
                Review KYC information and uploaded documents, then mark each profile as approved or rejected with
                remarks.[web:389][web:390]
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
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>KYC</th>
                            <th>Address</th>
                            <th>Documents</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pendingKyc)): ?>
                            <tr>
                                <td colspan="8">No pending KYC documents to verify.[web:398]</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendingKyc as $c): ?>
                                <tr>
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
                                        <div class="info-small">
                                            <?php echo htmlspecialchars($c['address'] ?? '—', ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div class="info-small">
                                            <?php echo htmlspecialchars($c['city'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>,
                                            <?php echo htmlspecialchars($c['state'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                            <?php echo htmlspecialchars($c['pincode'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    </td>
                                    <td class="doc-links">
                                        <?php if (!empty($c['document_front'])): ?>
                                            <a href="<?php echo htmlspecialchars($c['document_front'], ENT_QUOTES, 'UTF-8'); ?>"
                                                target="_blank">Front</a>
                                        <?php endif; ?>
                                        <?php if (!empty($c['document_back'])): ?>
                                            <a href="<?php echo htmlspecialchars($c['document_back'], ENT_QUOTES, 'UTF-8'); ?>"
                                                target="_blank">Back</a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-pending">
                                            <?php echo htmlspecialchars($c['kyc_status'] ?? 'pending', ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($c['created_at']); ?></td>
                                    <td>
                                        <div class="actions">
                                            <button type="button" class="btn-sm btn-sm-success"
                                                onclick="openDecisionModal(<?php echo (int) $c['id']; ?>, 'approve', '<?php echo htmlspecialchars($c['full_name'] ?: $c['username'], ENT_QUOTES, 'UTF-8'); ?>')">
                                                Approve
                                            </button>
                                            <button type="button" class="btn-sm btn-sm-danger"
                                                onclick="openDecisionModal(<?php echo (int) $c['id']; ?>, 'reject', '<?php echo htmlspecialchars($c['full_name'] ?: $c['username'], ENT_QUOTES, 'UTF-8'); ?>')">
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

    <!-- Decision Modal -->
    <div class="modal-backdrop" id="decisionBackdrop">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title" id="decisionTitle">Verify KYC</div>
                <button class="modal-close" type="button" onclick="closeDecisionModal()">✕</button>
            </div>
            <form method="post" id="decisionForm">
                <input type="hidden" name="user_id" id="decisionUserId">
                <input type="hidden" name="action" id="decisionAction">

                <div class="kv-row">
                    <div class="kv-label">Customer</div>
                    <div class="kv-value" id="decisionCustomer"></div>
                </div>
                <div class="form-group" style="margin-top:8px;">
                    <label for="remarks">Remarks (optional, required when rejecting)</label>
                    <textarea id="remarks" name="remarks" placeholder="Add a note about this decision"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeDecisionModal()">Cancel</button>
                    <button type="submit" class="btn" id="decisionSubmitBtn">Confirm</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const decisionBackdrop = document.getElementById('decisionBackdrop');
        const decisionTitle = document.getElementById('decisionTitle');
        const decisionCustomer = document.getElementById('decisionCustomer');
        const decisionUserId = document.getElementById('decisionUserId');
        const decisionAction = document.getElementById('decisionAction');
        const decisionSubmitBtn = document.getElementById('decisionSubmitBtn');
        const remarksField = document.getElementById('remarks');

        function openDecisionModal(userId, action, customerName) {
            decisionUserId.value = userId;
            decisionAction.value = action;
            decisionCustomer.textContent = customerName;
            remarksField.value = '';

            if (action === 'approve') {
                decisionTitle.textContent = 'Approve KYC';
                decisionSubmitBtn.textContent = 'Approve';
            } else {
                decisionTitle.textContent = 'Reject KYC';
                decisionSubmitBtn.textContent = 'Reject';
            }

            decisionBackdrop.classList.add('active');
        }

        function closeDecisionModal() {
            decisionBackdrop.classList.remove('active');
        }

        document.getElementById('decisionForm').addEventListener('submit', function (e) {
            if (decisionAction.value === 'reject' && remarksField.value.trim() === '') {
                e.preventDefault();
                alert('Please provide a reason for rejection.');
            }
        });

        window.addEventListener('click', function (e) {
            if (e.target === decisionBackdrop) {
                closeDecisionModal();
            }
        });
    </script>
</body>

</html>