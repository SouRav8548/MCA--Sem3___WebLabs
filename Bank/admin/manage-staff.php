<?php
require_once '../config.php';
checkAnyRole([ROLE_ADMIN, ROLE_MANAGER]);

$message = '';
$error = '';

// Handle staff create / update / delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post('action');

    if ($action === 'create') {
        $username = sanitizeInput(post('username'));
        $email = sanitizeInput(post('email'));
        $full_name = sanitizeInput(post('full_name'));
        $phone = sanitizeInput(post('phone'));
        $status = sanitizeInput(post('status', 'active'));
        $password = post('password');
        $role = ROLE_STAFF;

        if ($username === '' || $email === '' || $password === '') {
            $error = 'Username, email and password are required.';
        } elseif (!validateEmail($email)) {
            $error = 'Invalid email format.';
        } else {
            // Unique username/email
            $existing = fetchOne(
                $conn,
                "SELECT id FROM users WHERE (username = ? OR email = ?) AND role = ? LIMIT 1",
                'sss',
                [$username, $email, $role]
            );
            if ($existing) {
                $error = 'A staff account with this username or email already exists.';
            } else {
                $hash = hashPassword($password);
                $stmt = $conn->prepare("
                    INSERT INTO users (username, email, full_name, phone, password, role, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->bind_param('sssssss', $username, $email, $full_name, $phone, $hash, $role, $status);
                if ($stmt->execute()) {
                    $message = 'Staff account created successfully.';
                    logActivity($conn, (int) $_SESSION['user']['id'], 'create_staff', "Created staff $username");
                } else {
                    $error = 'Failed to create staff account.';
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'update') {
        $id = (int) post('id');
        $full_name = sanitizeInput(post('full_name'));
        $email = sanitizeInput(post('email'));
        $phone = sanitizeInput(post('phone'));
        $status = sanitizeInput(post('status'));
        $password = post('password');

        if ($id <= 0) {
            $error = 'Invalid staff id.';
        } elseif ($email === '' || !validateEmail($email)) {
            $error = 'Valid email is required.';
        } else {
            // Ensure email unique across staff (and manager if you decide)
            $existing = fetchOne(
                $conn,
                "SELECT id FROM users WHERE email = ? AND id <> ? AND role = ? LIMIT 1",
                'sis',
                [$email, $id, ROLE_STAFF]
            );
            if ($existing) {
                $error = 'Another staff member already uses this email.';
            } else {
                if ($password !== '') {
                    $hash = hashPassword($password);
                    $stmt = $conn->prepare("
                        UPDATE users
                        SET full_name = ?, email = ?, phone = ?, status = ?, password = ?
                        WHERE id = ? AND role = ?
                    ");
                    $stmt->bind_param('sssssis', $full_name, $email, $phone, $status, $hash, $id, ROLE_STAFF);
                } else {
                    $stmt = $conn->prepare("
                        UPDATE users
                        SET full_name = ?, email = ?, phone = ?, status = ?
                        WHERE id = ? AND role = ?
                    ");
                    $stmt->bind_param('ssssiss', $full_name, $email, $phone, $status, $id, ROLE_STAFF);
                }

                if ($stmt->execute()) {
                    $message = 'Staff account updated successfully.';
                    logActivity($conn, (int) $_SESSION['user']['id'], 'update_staff', "Updated staff ID $id");
                } else {
                    $error = 'Failed to update staff account.';
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'delete') {
        $id = (int) post('id');

        if ($id <= 0) {
            $error = 'Invalid staff id.';
        } elseif ($id === (int) $_SESSION['user']['id']) {
            $error = 'You cannot delete your own account.';
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = ?");
            $stmt->bind_param('is', $id, $role = ROLE_STAFF);
            if ($stmt->execute()) {
                $message = 'Staff account deleted successfully.';
                logActivity($conn, (int) $_SESSION['user']['id'], 'delete_staff', "Deleted staff ID $id");
            } else {
                $error = 'Failed to delete staff account.';
            }
            $stmt->close();
        }
    }
}

// Fetch only staff users
$staffList = fetchAll(
    $conn,
    "SELECT * FROM users WHERE role = ? ORDER BY created_at DESC",
    's',
    [ROLE_STAFF]
);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Manage Staff - SecureBank</title>
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
            margin-bottom: 14px;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            gap: 10px;
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

        .badge-inactive {
            background: rgba(201, 31, 47, 0.18);
            color: var(--danger, #c91f2f);
        }

        .actions {
            display: flex;
            gap: 6px;
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
            max-width: 420px;
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
            font-size: 13px;
        }

        label {
            display: block;
            margin-bottom: 4px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
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

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 8px;
        }
    </style>
</head>

<body>
    <div class="page">
        <?php include 'header.php'; ?>

        <div class="content">
            <h1>Manage Staff</h1>
            <p class="subtitle">Create and manage staff user accounts for branch operations.</p>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <div class="toolbar">
                <button class="btn" onclick="openCreateModal()">+ New Staff</button>
                <button class="btn btn-secondary" onclick="window.location.reload()">Refresh</button>
            </div>

            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username / Name</th>
                            <th>Email / Phone</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($staffList)): ?>
                            <tr>
                                <td colspan="6">No staff users found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($staffList as $s): ?>
                                <tr>
                                    <td><?php echo (int) $s['id']; ?></td>
                                    <td>
                                        <div><?php echo htmlspecialchars($s['username'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div style="font-size:11px;color:var(--text-secondary,#c0c0c0);">
                                            <?php echo htmlspecialchars($s['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($s['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div style="font-size:11px;color:var(--text-secondary,#c0c0c0);">
                                            <?php echo htmlspecialchars($s['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $badge = 'badge-inactive';
                                        if ($s['status'] === 'active')
                                            $badge = 'badge-active';
                                        if ($s['status'] === 'pending')
                                            $badge = 'badge-pending';
                                        ?>
                                        <span class="badge <?php echo $badge; ?>">
                                            <?php echo htmlspecialchars($s['status'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($s['created_at']); ?></td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn-sm" onclick="openEditModal(
                                            <?php echo (int) $s['id']; ?>,
                                            '<?php echo htmlspecialchars($s['username'], ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($s['full_name'], ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($s['email'], ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($s['phone'], ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($s['status'], ENT_QUOTES, 'UTF-8'); ?>'
                                        )">
                                                Edit
                                            </button>
                                            <?php if ((int) $s['id'] !== (int) $_SESSION['user']['id']): ?>
                                                <button class="btn-sm btn-sm-danger"
                                                    onclick="confirmDelete(<?php echo (int) $s['id']; ?>)">
                                                    Delete
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

    <!-- Modal -->
    <div class="modal-backdrop" id="staffModalBackdrop">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title" id="modalTitle">New Staff</div>
                <button class="modal-close" type="button" onclick="closeModal()">✕</button>
            </div>
            <form method="post" id="staffForm">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="staffId">

                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" name="username" id="usernameInput" required>
                </div>
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" name="full_name" id="fullNameInput">
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" name="email" id="emailInput" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" name="phone" id="phoneInput">
                </div>
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select name="status" id="statusInput">
                        <option value="active">Active</option>
                        <option value="pending">Pending</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Password <span id="passwordLabelExtra">*</span></label>
                    <input type="password" name="password" id="passwordInput">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const backdrop = document.getElementById('staffModalBackdrop');
        const form = document.getElementById('staffForm');
        const titleEl = document.getElementById('modalTitle');
        const actionEl = document.getElementById('formAction');
        const staffIdEl = document.getElementById('staffId');
        const usernameInput = document.getElementById('usernameInput');
        const fullNameInput = document.getElementById('fullNameInput');
        const emailInput = document.getElementById('emailInput');
        const phoneInput = document.getElementById('phoneInput');
        const statusInput = document.getElementById('statusInput');
        const passwordInput = document.getElementById('passwordInput');
        const passwordLabelExtra = document.getElementById('passwordLabelExtra');

        function openCreateModal() {
            form.reset();
            titleEl.textContent = 'New Staff';
            actionEl.value = 'create';
            staffIdEl.value = '';
            usernameInput.disabled = false;
            passwordLabelExtra.textContent = '*';
            backdrop.classList.add('active');
        }

        function openEditModal(id, username, fullName, email, phone, status) {
            form.reset();
            titleEl.textContent = 'Edit Staff';
            actionEl.value = 'update';
            staffIdEl.value = id;
            usernameInput.value = username;
            usernameInput.disabled = true;
            fullNameInput.value = fullName;
            emailInput.value = email;
            phoneInput.value = phone;
            statusInput.value = status;
            passwordInput.value = '';
            passwordLabelExtra.textContent = '(leave blank to keep existing)';
            backdrop.classList.add('active');
        }

        function closeModal() {
            backdrop.classList.remove('active');
        }

        window.addEventListener('click', function (e) {
            if (e.target === backdrop) {
                closeModal();
            }
        });

        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this staff account?')) {
                const f = document.createElement('form');
                f.method = 'post';
                f.innerHTML = '<input type="hidden" name="action" value="delete">' +
                    '<input type="hidden" name="id" value="' + id + '">';
                document.body.appendChild(f);
                f.submit();
            }
        }
    </script>
</body>

</html>