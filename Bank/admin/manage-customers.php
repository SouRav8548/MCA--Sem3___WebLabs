<?php
require_once '../config.php';
checkAnyRole([ROLE_ADMIN, ROLE_MANAGER]);

$message = '';
$error = '';

// Simple search term
$search = sanitizeInput(get('q', ''));

// Handle approve / reject / update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post('action');
    $id = (int) post('id');

    if ($id <= 0) {
        $error = 'Invalid customer id.';
    } else {
        if ($action === 'update') {
            $full_name = sanitizeInput(post('full_name'));
            $email = sanitizeInput(post('email'));
            $phone = sanitizeInput(post('phone'));
            $status = sanitizeInput(post('status'));

            if ($full_name === '' || $email === '') {
                $error = 'Name and email are required.';
            } elseif (!validateEmail($email)) {
                $error = 'Invalid email format.';
            } else {
                // ensure email unique among customers
                $existing = fetchOne(
                    $conn,
                    "SELECT id FROM users WHERE email = ? AND id <> ? AND role = 'customer' LIMIT 1",
                    'si',
                    [$email, $id]
                );
                if ($existing) {
                    $error = 'Another customer already uses this email.';
                } else {
                    $stmt = $conn->prepare("
                        UPDATE users
                        SET full_name = ?, email = ?, phone = ?, status = ?
                        WHERE id = ? AND role = 'customer'
                    ");
                    $stmt->bind_param('ssssi', $full_name, $email, $phone, $status, $id);
                    if ($stmt->execute()) {
                        $message = 'Customer updated successfully.';
                        logActivity($conn, (int) $_SESSION['user']['id'], 'update_customer', "Updated customer ID $id");
                    } else {
                        $error = 'Failed to update customer.';
                    }
                    $stmt->close();
                }
            }
        } elseif ($action === 'approve' || $action === 'reject') {
            $newStatus = $action === 'approve' ? 'active' : 'rejected';
            $stmt = $conn->prepare("
                UPDATE users
                SET status = ?
                WHERE id = ? AND role = 'customer'
            ");
            $stmt->bind_param('si', $newStatus, $id);
            if ($stmt->execute()) {
                $message = $action === 'approve'
                    ? 'Customer account approved.'
                    : 'Customer account rejected.';
                $activityAction = $action === 'approve' ? 'approve_customer' : 'reject_customer';
                logActivity($conn, (int) $_SESSION['user']['id'], $activityAction, ucfirst($newStatus) . " customer ID $id");
            } else {
                $error = 'Failed to update customer status.';
            }
            $stmt->close();
        }
    }
}

// Build query with optional search
$params = [];
$types = '';
$sql = "
    SELECT u.*, cp.aadhar, cp.pan, cp.address, cp.city, cp.state, cp.pincode
    FROM users u
    LEFT JOIN customer_profile cp ON cp.user_id = u.id
    WHERE u.role = 'customer'
";

if ($search !== '') {
    $sql .= " AND (u.username LIKE CONCAT('%', ?, '%')
                OR u.full_name LIKE CONCAT('%', ?, '%')
                OR u.email LIKE CONCAT('%', ?, '%')
                OR u.phone LIKE CONCAT('%', ?, '%'))";
    $types .= 'ssss';
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
}

$sql .= " ORDER BY u.created_at DESC";

$customers = fetchAll($conn, $sql, $types, $params);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Manage Customers - SecureBank</title>
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

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .search-box {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .search-input {
            padding: 7px 9px;
            border-radius: 999px;
            border: 1px solid var(--border-color, #2a3334);
            background: var(--bg-secondary, #181f20);
            color: var(--text-primary, #e0e0e0);
            font-size: 12px;
            min-width: 220px;
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

        .badge-active {
            background: rgba(46, 184, 111, 0.18);
            color: var(--success, #2eb86f);
        }

        .badge-pending {
            background: rgba(244, 165, 66, 0.18);
            color: var(--warning, #f4a542);
        }

        .badge-rejected {
            background: rgba(201, 31, 47, 0.18);
            color: var(--danger, #c91f2f);
        }

        .badge-inactive {
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

        .btn-sm:hover {
            border-color: var(--primary, #2d5f82);
            color: var(--primary, #2d5f82);
        }

        .btn-sm-success {
            border-color: var(--success, #2eb86f);
            color: var(--success, #2eb86f);
        }

        .btn-sm-danger {
            border-color: var(--danger, #c91f2f);
            color: var(--danger, #c91f2f);
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
            max-width: 480px;
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

        .form-group {
            margin-bottom: 10px;
        }

        label {
            display: block;
            margin-bottom: 4px;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        select {
            width: 100%;
            padding: 7px 8px;
            border-radius: 8px;
            border: 1px solid var(--border-color, #2a3334);
            background: var(--bg-primary, #131a1a);
            color: var(--text-primary, #e0e0e0);
            font-size: 13px;
        }

        textarea {
            width: 100%;
            padding: 7px 8px;
            border-radius: 8px;
            border: 1px solid var(--border-color, #2a3334);
            background: var(--bg-primary, #131a1a);
            color: var(--text-primary, #e0e0e0);
            font-size: 13px;
            resize: vertical;
            min-height: 70px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 8px;
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
    </style>
</head>

<body>
    <div class="page">
        <?php include 'header.php'; ?>

        <div class="content">
            <h1>Manage Customers</h1>
            <p class="subtitle">View, edit, approve or reject customer accounts and inspect KYC details.</p>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <div class="toolbar">
                <form method="get" class="search-box">
                    <input type="text" name="q" class="search-input"
                        placeholder="Search by name, username, email or phone"
                        value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-secondary">Search</button>
                </form>
                <button class="btn btn-secondary" onclick="window.location='manage-customers.php'">Clear</button>
            </div>

            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Email / Phone</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($customers)): ?>
                            <tr>
                                <td colspan="6">No customers found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($customers as $c): ?>
                                <tr>
                                    <td><?php echo (int) $c['id']; ?></td>
                                    <td>
                                        <div>
                                            <?php echo htmlspecialchars($c['full_name'] ?: $c['username'], ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                        <div style="font-size:11px;color:var(--text-secondary,#c0c0c0);">
                                            @<?php echo htmlspecialchars($c['username'], ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($c['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div style="font-size:11px;color:var(--text-secondary,#c0c0c0);">
                                            <?php echo htmlspecialchars($c['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = 'badge-inactive';
                                        if ($c['status'] === 'active')
                                            $badgeClass = 'badge-active';
                                        if ($c['status'] === 'pending')
                                            $badgeClass = 'badge-pending';
                                        if ($c['status'] === 'rejected')
                                            $badgeClass = 'badge-rejected';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>">
                                            <?php echo htmlspecialchars($c['status'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($c['created_at']); ?></td>
                                    <td>
                                        <div class="actions">
                                            <button type="button" class="btn-sm" onclick="openEditModal(
                                            <?php echo (int) $c['id']; ?>,
                                            '<?php echo htmlspecialchars($c['full_name'], ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($c['email'], ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($c['phone'], ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($c['status'], ENT_QUOTES, 'UTF-8'); ?>'
                                        )">
                                                Edit
                                            </button>
                                            <button type="button" class="btn-sm" onclick="openProfileModal(
                                            '<?php echo htmlspecialchars($c['full_name'] ?: $c['username'], ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($c['aadhar'] ?? '', ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($c['pan'] ?? '', ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($c['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($c['city'] ?? '', ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($c['state'] ?? '', ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($c['pincode'] ?? '', ENT_QUOTES, 'UTF-8'); ?>'
                                        )">
                                                View KYC
                                            </button>
                                            <?php if ($c['status'] === 'pending'): ?>
                                                <button type="button" class="btn-sm btn-sm-success"
                                                    onclick="submitStatus(<?php echo (int) $c['id']; ?>, 'approve')">
                                                    Approve
                                                </button>
                                                <button type="button" class="btn-sm btn-sm-danger"
                                                    onclick="submitStatus(<?php echo (int) $c['id']; ?>, 'reject')">
                                                    Reject
                                                </button>
                                            <?php endif; ?>
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

    <!-- Edit Modal -->
    <div class="modal-backdrop" id="editModalBackdrop">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">Edit Customer</div>
                <button class="modal-close" type="button" onclick="closeEditModal()">✕</button>
            </div>
            <form method="post" id="editForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="editId">

                <div class="form-group">
                    <label for="editFullName">Full Name *</label>
                    <input type="text" id="editFullName" name="full_name" required>
                </div>
                <div class="form-group">
                    <label for="editEmail">Email *</label>
                    <input type="email" id="editEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="editPhone">Phone</label>
                    <input type="tel" id="editPhone" name="phone">
                </div>
                <div class="form-group">
                    <label for="editStatus">Status *</label>
                    <select id="editStatus" name="status">
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="inactive">Inactive</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Profile / KYC Modal -->
    <div class="modal-backdrop" id="profileModalBackdrop">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title" id="profileTitle">Customer Profile</div>
                <button class="modal-close" type="button" onclick="closeProfileModal()">✕</button>
            </div>
            <div class="modal-body">
                <div class="kv-row">
                    <div class="kv-label">Aadhaar</div>
                    <div class="kv-value" id="kycAadhar"></div>
                </div>
                <div class="kv-row">
                    <div class="kv-label">PAN</div>
                    <div class="kv-value" id="kycPan"></div>
                </div>
                <div class="kv-row">
                    <div class="kv-label">Address</div>
                    <div class="kv-value" id="kycAddress"></div>
                </div>
                <div class="kv-row">
                    <div class="kv-label">City</div>
                    <div class="kv-value" id="kycCity"></div>
                </div>
                <div class="kv-row">
                    <div class="kv-label">State</div>
                    <div class="kv-value" id="kycState"></div>
                </div>
                <div class="kv-row">
                    <div class="kv-label">Pincode</div>
                    <div class="kv-value" id="kycPincode"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const editBackdrop = document.getElementById('editModalBackdrop');
        const profileBackdrop = document.getElementById('profileModalBackdrop');

        function openEditModal(id, fullName, email, phone, status) {
            document.getElementById('editId').value = id;
            document.getElementById('editFullName').value = fullName;
            document.getElementById('editEmail').value = email;
            document.getElementById('editPhone').value = phone;
            document.getElementById('editStatus').value = status;
            editBackdrop.classList.add('active');
        }

        function closeEditModal() {
            editBackdrop.classList.remove('active');
        }

        function openProfileModal(name, aadhar, pan, address, city, state, pincode) {
            document.getElementById('profileTitle').textContent = name + ' - KYC';
            document.getElementById('kycAadhar').textContent = aadhar || '—';
            document.getElementById('kycPan').textContent = pan || '—';
            document.getElementById('kycAddress').textContent = address || '—';
            document.getElementById('kycCity').textContent = city || '—';
            document.getElementById('kycState').textContent = state || '—';
            document.getElementById('kycPincode').textContent = pincode || '—';
            profileBackdrop.classList.add('active');
        }

        function closeProfileModal() {
            profileBackdrop.classList.remove('active');
        }

        window.addEventListener('click', function (e) {
            if (e.target === editBackdrop) closeEditModal();
            if (e.target === profileBackdrop) closeProfileModal();
        });

        // Submit approve/reject via hidden form
        function submitStatus(id, action) {
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