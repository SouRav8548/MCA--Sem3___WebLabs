<?php
// customer/header.php
if (!isset($conn)) {
    require_once '../config.php';
}
checkRole(ROLE_CUSTOMER);

$currentUser = $_SESSION['user'] ?? null;
$currentPath = basename($_SERVER['PHP_SELF']);
?>
<header class="cust-topbar">
    <style>
        .cust-topbar {
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

        .cust-brand {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cust-brand-logo {
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

        .cust-brand-text {
            font-size: 15px;
            font-weight: 600;
        }

        .cust-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .cust-nav a {
            color: var(--text-secondary, #c0c0c0);
            text-decoration: none;
            padding: 6px 9px;
            border-radius: 999px;
        }

        .cust-nav a:hover {
            background: var(--hover-bg, #202729);
            color: var(--primary, #2d5f82);
        }

        .cust-nav a.active {
            background: var(--primary, #2d5f82);
            color: #fff;
        }

        .cust-right {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            color: var(--text-secondary, #c0c0c0);
        }

        .cust-user-name {
            font-weight: 500;
        }

        .cust-theme-toggle {
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

        .cust-logout-link {
            color: var(--danger, #c91f2f);
            text-decoration: none;
            padding: 4px 8px;
            border-radius: 999px;
            border: 1px solid transparent;
        }

        .cust-logout-link:hover {
            border-color: var(--danger, #c91f2f);
            background: rgba(201, 31, 47, 0.08);
        }

        @media (max-width: 760px) {
            .cust-nav {
                display: none;
            }
        }
    </style>

    <div class="cust-brand">
        <div class="cust-brand-logo">SB</div>
        <div class="cust-brand-text">SecureBank</div>
    </div>

    <nav class="cust-nav">
        <a href="dashboard.php" class="<?php echo $currentPath === 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a>
        <a href="accounts.php" class="<?php echo $currentPath === 'accounts.php' ? 'active' : ''; ?>">Accounts</a>
        <a href="transactions.php"
            class="<?php echo $currentPath === 'transactions.php' ? 'active' : ''; ?>">Transactions</a>
        <a href="transfer.php" class="<?php echo $currentPath === 'transfer.php' ? 'active' : ''; ?>">Transfer</a>
        <a href="requests.php" class="<?php echo $currentPath === 'requests.php' ? 'active' : ''; ?>">Requests</a>
        <a href="profile.php" class="<?php echo $currentPath === 'profile.php' ? 'active' : ''; ?>">Profile</a>
    </nav>

    <div class="cust-right">
        <?php if ($currentUser): ?>
            <span class="cust-user-name">
                <?php echo htmlspecialchars($currentUser['full_name'] ?? $currentUser['username'], ENT_QUOTES, 'UTF-8'); ?>
            </span>
        <?php endif; ?>
        <button type="button" class="cust-theme-toggle" id="custThemeToggle">🌙</button>
        <a href="../logout.php" class="cust-logout-link">Logout</a>
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

            const btn = document.getElementById('custThemeToggle');
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