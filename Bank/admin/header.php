<?php
// admin/header.php
if (!isset($conn)) {
    require_once '../config.php';
}
checkAnyRole([ROLE_ADMIN, ROLE_MANAGER]);

$currentUser = $_SESSION['user'] ?? null;
$currentPath = basename($_SERVER['PHP_SELF']);
?>
<header class="admin-topbar">
    <style>
        .admin-topbar {
            padding: 10px 16px;
            border-bottom: 1px solid var(--border-color, #2a3334);
            background: var(--bg-secondary, #181f20);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .admin-brand {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .admin-brand-logo {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: linear-gradient(135deg, #2d5f82, #8b6f47);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
        }

        .admin-brand-text {
            font-size: 15px;
            font-weight: 600;
        }

        .admin-nav {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
        }

        .admin-nav a {
            color: var(--text-secondary, #c0c0c0);
            text-decoration: none;
            padding: 6px 9px;
            border-radius: 999px;
        }

        .admin-nav a:hover {
            background: var(--hover-bg, #202729);
            color: var(--primary, #2d5f82);
        }

        .admin-nav a.active {
            background: var(--primary, #2d5f82);
            color: #fff;
        }

        .admin-topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            color: var(--text-secondary, #c0c0c0);
        }

        .admin-user-name {
            font-weight: 500;
        }

        .admin-theme-toggle {
            background: transparent;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            border: 1px solid var(--border-color, #2a3334);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-secondary, #c0c0c0);
            font-size: 14px;
        }

        .admin-logout-link {
            color: var(--danger, #c91f2f);
            text-decoration: none;
            padding: 4px 8px;
            border-radius: 999px;
            border: 1px solid transparent;
        }

        .admin-logout-link:hover {
            border-color: var(--danger, #c91f2f);
            background: rgba(201, 31, 47, 0.08);
        }

        @media (max-width: 720px) {
            .admin-nav {
                display: none;
            }
        }
    </style>

    <div class="admin-brand">
        <div class="admin-brand-logo">SB</div>
        <div class="admin-brand-text">Admin Panel</div>
    </div>

    <nav class="admin-nav">
        <a href="dashboard.php" class="<?php echo $currentPath === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
        <a href="manage-users.php" class="<?php echo $currentPath === 'manage-users.php' ? 'active' : ''; ?>">Users</a>
        <a href="manage-customers.php"
            class="<?php echo $currentPath === 'manage-customers.php' ? 'active' : ''; ?>">Customers</a>
        <a href="approve-accounts.php"
            class="<?php echo $currentPath === 'approve-accounts.php' ? 'active' : ''; ?>">Approvals</a>
        <a href="reports.php" class="<?php echo $currentPath === 'reports.php' ? 'active' : ''; ?>">Reports</a>
    </nav>

    <div class="admin-topbar-right">
        <?php if ($currentUser): ?>
            <span class="admin-user-name">
                <?php echo htmlspecialchars($currentUser['full_name'] ?? $currentUser['username'], ENT_QUOTES, 'UTF-8'); ?>
            </span>
            <span style="opacity: 0.7;">
                (<?php echo htmlspecialchars($currentUser['role'], ENT_QUOTES, 'UTF-8'); ?>)
            </span>
        <?php endif; ?>
        <button type="button" class="admin-theme-toggle" id="adminThemeToggle">🌙</button>
        <a href="../logout.php" class="admin-logout-link">Logout</a>
    </div>

    <script>
        (function () {
            const root = document.documentElement;
            const saved = localStorage.getItem('theme');
            if (saved === 'light' || saved === 'dark') {
                root.setAttribute('data-theme', saved);
            } else {
                root.setAttribute('data-theme', 'dark');
            }

            const btn = document.getElementById('adminThemeToggle');
            const updateIcon = () => {
                const current = root.getAttribute('data-theme');
                btn.textContent = current === 'light' ? '☀️' : '🌙';
            };
            updateIcon();

            btn.addEventListener('click', function () {
                const current = root.getAttribute('data-theme');
                const next = current === 'dark' ? 'light' : 'dark';
                root.setAttribute('data-theme', next);
                localStorage.setItem('theme', next);
                updateIcon();
            });
        })();
    </script>
</header>