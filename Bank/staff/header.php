<?php
// staff/header.php
if (!isset($conn)) {
    require_once '../config.php';
}
checkRole(ROLE_STAFF);

$currentUser = $_SESSION['user'] ?? null;
$currentPath = basename($_SERVER['PHP_SELF']);
?>
<header class="staff-topbar">
    <style>
        .staff-topbar {
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

        .staff-brand {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .staff-brand-logo {
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

        .staff-brand-text {
            font-size: 15px;
            font-weight: 600;
        }

        .staff-nav {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
        }

        .staff-nav a {
            color: var(--text-secondary, #c0c0c0);
            text-decoration: none;
            padding: 6px 9px;
            border-radius: 999px;
        }

        .staff-nav a:hover {
            background: var(--hover-bg, #202729);
            color: var(--primary, #2d5f82);
        }

        .staff-nav a.active {
            background: var(--primary, #2d5f82);
            color: #fff;
        }

        .staff-topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            color: var(--text-secondary, #c0c0c0);
        }

        .staff-user-name {
            font-weight: 500;
        }

        .staff-theme-toggle {
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

        .staff-logout-link {
            color: var(--danger, #c91f2f);
            text-decoration: none;
            padding: 4px 8px;
            border-radius: 999px;
            border: 1px solid transparent;
        }

        .staff-logout-link:hover {
            border-color: var(--danger, #c91f2f);
            background: rgba(201, 31, 47, 0.08);
        }

        @media (max-width: 720px) {
            .staff-nav {
                display: none;
            }
        }
    </style>

    <div class="staff-brand">
        <div class="staff-brand-logo">SB</div>
        <div class="staff-brand-text">Staff Panel</div>
    </div>

    <nav class="staff-nav">
        <a href="dashboard.php" class="<?php echo $currentPath === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
        <a href="../admin/approve-accounts.php"
            class="<?php echo $currentPath === 'approve-accounts.php' ? 'active' : ''; ?>">Accounts</a>
        <a href="../admin/approve-requests.php"
            class="<?php echo $currentPath === 'approve-requests.php' ? 'active' : ''; ?>">ATM / Cheques</a>
        <a href="../admin/approve-loans.php"
            class="<?php echo $currentPath === 'approve-loans.php' ? 'active' : ''; ?>">Loans</a>
        <a href="../admin/view-transactions.php"
            class="<?php echo $currentPath === 'view-transactions.php' ? 'active' : ''; ?>">Transactions</a>
    </nav>

    <div class="staff-topbar-right">
        <?php if ($currentUser): ?>
            <span class="staff-user-name">
                <?php echo htmlspecialchars($currentUser['full_name'] ?? $currentUser['username'], ENT_QUOTES, 'UTF-8'); ?>
            </span>
            <span style="opacity:0.7;">(<?php echo htmlspecialchars($currentUser['role'], ENT_QUOTES, 'UTF-8'); ?>)</span>
        <?php endif; ?>
        <button type="button" class="staff-theme-toggle" id="staffThemeToggle">🌙</button>
        <a href="../logout.php" class="staff-logout-link">Logout</a>
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

            const btn = document.getElementById('staffThemeToggle');
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