<?php
require_once '../config.php';
checkAnyRole([ROLE_ADMIN, ROLE_MANAGER]);

// Handle actions
$message = '';
$error = '';

// Create / Update / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post('action');

    if ($action === 'create') {
        $username = sanitizeInput(post('username'));
        $email = sanitizeInput(post('email'));
        $full_name = sanitizeInput(post('full_name'));
        $phone = sanitizeInput(post('phone'));
        $role = sanitizeInput(post('role'));
        $status = sanitizeInput(post('status', 'active'));
        $password = post('password');

        if ($username === '' || $email === '' || $role === '' || $password === '') {
            $error = 'Username, email, role and password are required.';
        } elseif (!validateEmail($email)) {
            $error = 'Invalid email format.';
        } elseif (!in_array($role, [ROLE_ADMIN, ROLE_MANAGER, ROLE_STAFF, ROLE_CUSTOMER], true)) {
            $error = 'Invalid role selected.';
        } else {
            // Check unique
            $existing = fetchOne(
                $conn,
                "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1",
                'ss',
                [$username, $email]
            );
            if ($existing) {
                $error = 'Username or email already exists.';
            } else {
                $hash = hashPassword($password);
                $stmt = $conn->prepare("
                    INSERT INTO users (username, email, full_name, phone, password, role, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->bind_param('sssssss', $username, $email, $full_name, $phone, $hash, $role, $status);
                if ($stmt->execute()) {
                    $message = 'User created successfully.';
                    logActivity($conn, (int) $_SESSION['user']['id'], 'create_user', "Created user $username");
                } else {
                    $error = 'Failed to create user.';
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'update') {
        $id = (int) post('id');
        $full_name = sanitizeInput(post('full_name'));
        $email = sanitizeInput(post('email'));
        $phone = sanitizeInput(post('phone'));
        $role = sanitizeInput(post('role'));
        $status = sanitizeInput(post('status'));
        $password = post('password');

        if ($id <= 0) {
            $error = 'Invalid user id.';
        } elseif ($email === '' || !validateEmail($email)) {
            $error = 'Valid email is required.';
        } elseif (!in_array($role, [ROLE_ADMIN, ROLE_MANAGER, ROLE_STAFF, ROLE_CUSTOMER], true)) {
            $error = 'Invalid role selected.';
        } else {
            // Ensure unique email for other users
            $existing = fetchOne(
                $conn,
                "SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1",
                'si',
                [$email, $id]
            );
            if ($existing) {
                $error = 'Another user already has this email.';
            } else {
                if ($password !== '') {
                    $hash = hashPassword($password);
                    $stmt = $conn->prepare("
                        UPDATE users
                        SET full_name = ?, email = ?, phone = ?, role = ?, status = ?, password = ?
                        WHERE id = ?
                    ");
                    $stmt->bind_param('ssssssi', $full_name, $email, $phone, $role, $status, $hash, $id);
                } else {
                    $stmt = $conn->prepare("
                        UPDATE users
                        SET full_name = ?, email = ?, phone = ?, role = ?, status = ?
                        WHERE id = ?
                    ");
                    $stmt->bind_param('sssssi', $full_name, $email, $phone, $role, $status, $id);
                }

                if ($stmt->execute()) {
                    $message = 'User updated successfully.';
                    logActivity($conn, (int) $_SESSION['user']['id'], 'update_user', "Updated user ID $id");
                } else {
                    $error = 'Failed to update user.';
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'delete') {
        $id = (int) post('id');

        if ($id <= 0) {
            $error = 'Invalid user id.';
        } elseif ($id === (int) $_SESSION['user']['id']) {
            $error = 'You cannot delete your own account.';
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                $message = 'User deleted successfully.';
                logActivity($conn, (int) $_SESSION['user']['id'], 'delete_user', "Deleted user ID $id");
            } else {
                $error = 'Failed to delete user.';
            }
            $stmt->close();
        }
    }
}

// Fetch all users
$users = fetchAll($conn, "SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Manage Users - SecureBank</title>
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

        .badge-role {
            background: rgba(45, 95, 130, 0.18);
            color: var(--primary, #2d5f82);
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

        .modal-body {
            font-size: 13px;
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

        @media (max-width: 680px) {
            .content {
                padding: 12px;
            }

            table {
                font-size: 11px;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <?php include 'header.php'; ?>

        <div class="content">
            <h1>Manage Users</h1>
            <p class="subtitle">Create, update and deactivate users across all roles.</p>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <div class="toolbar">
                <button class="btn" onclick="openCreateModal()">+ New User</button>
                <button class="btn btn-secondary" onclick="window.location.reload()">Refresh</button>
            </div>

            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username / Name</th>
                            <th>Email / Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7">No users found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?php echo (int) $u['id']; ?></td>
                                    <td>
                                        <div><?php echo htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div style="font-size:11px;color:var(--text-secondary,#c0c0c0);">
                                            <?php echo htmlspecialchars($u['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div style="font-size:11px;color:var(--text-secondary,#c0c0c0);">
                                            <?php echo htmlspecialchars($u['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-role">
                                            <?php echo htmlspecialchars($u['role'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $statusBadge = 'badge-inactive';
                                        if ($u['status'] === 'active')
                                            $statusBadge = 'badge-active';
                                        if ($u['status'] === 'pending')
                                            $statusBadge = 'badge-pending';
                                        ?>
                                        <span class="badge <?php echo $statusBadge; ?>">
                                            <?php echo htmlspecialchars($u['status'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($u['created_at']); ?></td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn-sm" onclick="openEditModal(<?php echo (int) $u['id']; ?>,
                                            '<?php echo htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($u['phone'], ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($u['role'], ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($u['status'], ENT_QUOTES, 'UTF-8'); ?>'
                                        )">
                                                Edit
                                            </button>
                                            <?php if ((int) $u['id'] !== (int) $_SESSION['user']['id']): ?>
                                                <button class="btn-sm btn-sm-danger"
                                                    onclick="confirmDelete(<?php echo (int) $u['id']; ?>)">
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
    <div class="modal-backdrop" id="userModalBackdrop">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title" id="modalTitle">New User</div>
                <button class="modal-close" type="button" onclick="closeModal()">✕</button>
            </div>
            <div class="modal-body">
                <form method="post" id="userForm">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="id" id="userId">

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
                        <input type="text" name="phone" id="phoneInput">
                    </div>
                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select name="role" id="roleInput" required>
                            <option value="">Select role</option>
                            <option value="admin">Admin</option>
                            <option value="manager">Manager</option>
                            <option value="staff">Staff</option>
                            <option value="customer">Customer</option>
                        </select>
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
    </div>

    <script>
        const backdrop = document.getElementById('userModalBackdrop');
        const form = document.getElementById('userForm');
        const titleEl = document.getElementById('modalTitle');
        const actionEl = document.getElementById('formAction');
        const userIdEl = document.getElementById('userId');
        const usernameInput = document.getElementById('usernameInput');
        const fullNameInput = document.getElementById('fullNameInput');
        const emailInput = document.getElementById('emailInput');
        const phoneInput = document.getElementById('phoneInput');
        const roleInput = document.getElementById('roleInput');
        const statusInput = document.getElementById('statusInput');
        const passwordInput = document.getElementById('passwordInput');
        const passwordLabelExtra = document.getElementById('passwordLabelExtra');

        function openCreateModal() {
            form.reset();
            titleEl.textContent = 'New User';
            actionEl.value = 'create';
            userIdEl.value = '';
            usernameInput.disabled = false;
            passwordLabelExtra.textContent = '*';
            backdrop.classList.add('active');
        }

        function openEditModal(id, username, fullName, email, phone, role, status) {
            form.reset();
            titleEl.textContent = 'Edit User';
            actionEl.value = 'update';
            userIdEl.value = id;
            usernameInput.value = username;
            usernameInput.disabled = true;
            fullNameInput.value = fullName;
            emailInput.value = email;
            phoneInput.value = phone;
            roleInput.value = role;
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
            if (confirm('Are you sure you want to delete this user? This cannot be undone.')) {
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