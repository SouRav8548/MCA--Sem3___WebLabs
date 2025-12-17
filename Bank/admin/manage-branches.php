<?php
require_once '../config.php';
checkAnyRole([ROLE_ADMIN, ROLE_MANAGER]);

$message = '';
$error = '';

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post('action');

    if ($action === 'create') {
        $name = sanitizeInput(post('name'));
        $code = strtoupper(sanitizeInput(post('code')));
        $city = sanitizeInput(post('city'));
        $state = sanitizeInput(post('state'));
        $ifsc = strtoupper(sanitizeInput(post('ifsc')));

        if ($name === '' || $code === '' || $ifsc === '') {
            $error = 'Branch name, code and IFSC are required.';
        } else {
            // Check unique code/IFSC
            $existing = fetchOne(
                $conn,
                "SELECT id FROM branches WHERE code = ? OR ifsc = ? LIMIT 1",
                'ss',
                [$code, $ifsc]
            );
            if ($existing) {
                $error = 'A branch with the same code or IFSC already exists.';
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO branches (name, code, city, state, ifsc, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())
                ");
                $stmt->bind_param('sssss', $name, $code, $city, $state, $ifsc);
                if ($stmt->execute()) {
                    $message = 'Branch created successfully.';
                    logActivity($conn, (int) $_SESSION['user']['id'], 'create_branch', "Created branch $code");
                } else {
                    $error = 'Failed to create branch.';
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'update') {
        $id = (int) post('id');
        $name = sanitizeInput(post('name'));
        $code = strtoupper(sanitizeInput(post('code')));
        $city = sanitizeInput(post('city'));
        $state = sanitizeInput(post('state'));
        $ifsc = strtoupper(sanitizeInput(post('ifsc')));

        if ($id <= 0) {
            $error = 'Invalid branch id.';
        } elseif ($name === '' || $code === '' || $ifsc === '') {
            $error = 'Branch name, code and IFSC are required.';
        } else {
            $existing = fetchOne(
                $conn,
                "SELECT id FROM branches WHERE (code = ? OR ifsc = ?) AND id <> ? LIMIT 1",
                'ssi',
                [$code, $ifsc, $id]
            );
            if ($existing) {
                $error = 'Another branch with the same code or IFSC already exists.';
            } else {
                $stmt = $conn->prepare("
                    UPDATE branches
                    SET name = ?, code = ?, city = ?, state = ?, ifsc = ?
                    WHERE id = ?
                ");
                $stmt->bind_param('sssssi', $name, $code, $city, $state, $ifsc, $id);
                if ($stmt->execute()) {
                    $message = 'Branch updated successfully.';
                    logActivity($conn, (int) $_SESSION['user']['id'], 'update_branch', "Updated branch ID $id");
                } else {
                    $error = 'Failed to update branch.';
                }
                $stmt->close();
            }
        }
    } elseif ($action === 'delete') {
        $id = (int) post('id');

        if ($id <= 0) {
            $error = 'Invalid branch id.';
        } else {
            $stmt = $conn->prepare("DELETE FROM branches WHERE id = ?");
            $stmt->bind_param('i', $id);
            if ($stmt->execute()) {
                $message = 'Branch deleted successfully.';
                logActivity($conn, (int) $_SESSION['user']['id'], 'delete_branch', "Deleted branch ID $id");
            } else {
                $error = 'Failed to delete branch. It may be referenced by accounts or customers.';
            }
            $stmt->close();
        }
    }
}

// Fetch branches
$branches = fetchAll($conn, "SELECT * FROM branches ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Manage Branches - SecureBank</title>
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
            gap: 10px;
            margin-bottom: 10px;
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

        input[type="text"] {
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
            <h1>Manage Branches</h1>
            <p class="subtitle">Create and maintain bank branches for account and customer mapping.[web:243]</p>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <div class="toolbar">
                <button class="btn" onclick="openCreateModal()">+ New Branch</button>
                <button class="btn btn-secondary" onclick="window.location.reload()">Refresh</button>
            </div>

            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Branch / Code</th>
                            <th>City / State</th>
                            <th>IFSC</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($branches)): ?>
                            <tr>
                                <td colspan="6">No branches found.[web:36]</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($branches as $b): ?>
                                <tr>
                                    <td><?php echo (int) $b['id']; ?></td>
                                    <td>
                                        <div><?php echo htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div style="font-size:11px;color:var(--text-secondary,#c0c0c0);">
                                            Code: <?php echo htmlspecialchars($b['code'], ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($b['city'], ENT_QUOTES, 'UTF-8'); ?></div>
                                        <div style="font-size:11px;color:var(--text-secondary,#c0c0c0);">
                                            <?php echo htmlspecialchars($b['state'], ENT_QUOTES, 'UTF-8'); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($b['ifsc'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo formatDate($b['created_at']); ?></td>
                                    <td>
                                        <div class="actions">
                                            <button type="button" class="btn-sm" onclick="openEditModal(
                                            <?php echo (int) $b['id']; ?>,
                                            '<?php echo htmlspecialchars($b['name'], ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($b['code'], ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($b['city'], ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($b['state'], ENT_QUOTES, 'UTF-8'); ?>',
                                            '<?php echo htmlspecialchars($b['ifsc'], ENT_QUOTES, 'UTF-8'); ?>'
                                        )">
                                                Edit
                                            </button>
                                            <button type="button" class="btn-sm btn-sm-danger"
                                                onclick="confirmDelete(<?php echo (int) $b['id']; ?>)">
                                                Delete
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

    <!-- Modal -->
    <div class="modal-backdrop" id="branchModalBackdrop">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title" id="modalTitle">New Branch</div>
                <button class="modal-close" type="button" onclick="closeModal()">✕</button>
            </div>
            <form method="post" id="branchForm">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="branchId">

                <div class="form-group">
                    <label for="nameInput">Branch Name *</label>
                    <input type="text" name="name" id="nameInput" required>
                </div>
                <div class="form-group">
                    <label for="codeInput">Branch Code *</label>
                    <input type="text" name="code" id="codeInput" required>
                </div>
                <div class="form-group">
                    <label for="cityInput">City</label>
                    <input type="text" name="city" id="cityInput">
                </div>
                <div class="form-group">
                    <label for="stateInput">State</label>
                    <input type="text" name="state" id="stateInput">
                </div>
                <div class="form-group">
                    <label for="ifscInput">IFSC *</label>
                    <input type="text" name="ifsc" id="ifscInput" required>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const backdrop = document.getElementById('branchModalBackdrop');
        const form = document.getElementById('branchForm');
        const titleEl = document.getElementById('modalTitle');
        const actionEl = document.getElementById('formAction');
        const branchIdEl = document.getElementById('branchId');
        const nameInput = document.getElementById('nameInput');
        const codeInput = document.getElementById('codeInput');
        const cityInput = document.getElementById('cityInput');
        const stateInput = document.getElementById('stateInput');
        const ifscInput = document.getElementById('ifscInput');

        function openCreateModal() {
            form.reset();
            titleEl.textContent = 'New Branch';
            actionEl.value = 'create';
            branchIdEl.value = '';
            codeInput.disabled = false;
            ifscInput.disabled = false;
            backdrop.classList.add('active');
        }

        function openEditModal(id, name, code, city, state, ifsc) {
            form.reset();
            titleEl.textContent = 'Edit Branch';
            actionEl.value = 'update';
            branchIdEl.value = id;
            nameInput.value = name;
            codeInput.value = code;
            cityInput.value = city;
            stateInput.value = state;
            ifscInput.value = ifsc;
            codeInput.disabled = false;
            ifscInput.disabled = false;
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
            if (confirm('Delete this branch? It may fail if accounts/customers reference it.')) {
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