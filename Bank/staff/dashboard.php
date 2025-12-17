<?php
require_once '../config.php';
checkRole(ROLE_STAFF);
include 'header.php';
$user = $_SESSION['user'] ?? null;

// Simple staff metrics
$totalCustomers = fetchOne($conn, "SELECT COUNT(*) AS c FROM users WHERE role = 'customer'", '')['c'] ?? 0;
$pendingAccounts = fetchOne($conn, "SELECT COUNT(*) AS c FROM users WHERE role = 'customer' AND status = 'pending'", '')['c'] ?? 0;
$pendingLoans = fetchOne($conn, "SELECT COUNT(*) AS c FROM loans WHERE status = 'pending'", '')['c'] ?? 0;
$pendingAtmRequests = fetchOne($conn, "SELECT COUNT(*) AS c FROM atm_card_requests WHERE status = 'pending'", '')['c'] ?? 0;
$pendingChqRequests = fetchOne($conn, "SELECT COUNT(*) AS c FROM cheque_book_requests WHERE status = 'pending'", '')['c'] ?? 0;

// Recent activity limited to items staff usually cares about (log may contain all)
$recentTransactions = fetchAll(
    $conn,
    "SELECT t.*, a.account_number, u.full_name, u.username
     FROM transactions t
     LEFT JOIN accounts a ON a.id = t.account_id
     LEFT JOIN users u ON u.id = a.user_id
     ORDER BY t.created_at DESC
     LIMIT 10"
);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard - SecureBank</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg-primary, #131a1a);
            color: var(--text-primary, #e0e0e0);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .layout {
            display: grid;
            grid-template-columns: 220px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: var(--bg-secondary, #181f20);
            border-right: 1px solid var(--border-color, #2a3334);
            padding: 18px 14px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
        }

        .brand-logo {
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: linear-gradient(135deg, #2d5f82, #8b6f47);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #fff;
            font-weight: 700;
        }

        .brand-text {
            font-size: 15px;
            font-weight: 600;
        }

        .sidebar-section-title {
            margin: 12px 4px 6px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-secondary, #999);
        }

        .sidebar-nav {
            list-style: none;
            margin: 0;
            padding: 0;
            font-size: 13px;
        }

        .sidebar-nav li {
            margin-bottom: 4px;
        }

        .sidebar-nav a {
            display: block;
            padding: 7px 9px;
            border-radius: 8px;
            text-decoration: none;
            color: var(--text-secondary, #c0c0c0);
        }

        .sidebar-nav a:hover {
            background: var(--hover-bg, #202729);
            color: var(--primary, #2d5f82);
        }

        .sidebar-nav a.active {
            background: var(--primary, #2d5f82);
            color: #fff;
        }

        .main {
            display: flex;
            flex-direction: column;
        }

        .topbar {
            padding: 10px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color, #2a3334);
            background: var(--bg-secondary, #181f20);
        }

        .topbar-title {
            font-size: 16px;
            font-weight: 600;
        }

        .topbar-subtitle {
            font-size: 12px;
            color: var(--text-secondary, #c0c0c0);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
            color: var(--text-secondary, #c0c0c0);
        }

        .theme-toggle {
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

        .main-inner {
            padding: 18px 18px 20px;
            flex: 1;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .stat-card {
            background: var(--bg-secondary, #181f20);
            border-radius: 12px;
            padding: 12px 11px;
            border: 1px solid var(--border-color, #2a3334);
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-secondary, #c0c0c0);
        }

        .stat-value {
            font-size: 19px;
            font-weight: 700;
            margin-top: 4px;
        }

        .stat-tag {
            font-size: 11px;
            margin-top: 4px;
            color: var(--primary, #2d5f82);
        }

        .panel-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(0, 1.1fr);
            gap: 16px;
        }

        .panel {
            background: var(--bg-secondary, #181f20);
            border-radius: 12px;
            border: 1px solid var(--border-color, #2a3334);
            padding: 14px 12px 12px;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .panel-title {
            font-size: 14px;
            font-weight: 600;
        }

        .panel-subtitle {
            font-size: 11px;
            color: var(--text-secondary, #c0c0c0);
        }

        .link-sm {
            font-size: 11px;
            color: var(--primary, #2d5f82);
            text-decoration: none;
        }

        .link-sm:hover {
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th,
        td {
            padding: 6px 4px;
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

        .badge-deposit {
            background: rgba(46, 184, 111, 0.18);
            color: var(--success, #2eb86f);
        }

        .badge-withdrawal {
            background: rgba(201, 31, 47, 0.18);
            color: var(--danger, #c91f2f);
        }

        .badge-transfer {
            background: rgba(244, 165, 66, 0.18);
            color: var(--warning, #f4a542);
        }

        .info-small {
            font-size: 11px;
            color: var(--text-secondary, #c0c0c0);
        }

        @media (max-width: 900px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }
        }

        @media (max-width: 640px) {
            .panel-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-logo">SB</div>
                <div class="brand-text">Staff Panel</div>
            </div>

            <div class="sidebar-section-title">Overview</div>
            <ul class="sidebar-nav">
                <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
            </ul>

            <div class="sidebar-section-title">Customers</div>
            <ul class="sidebar-nav">
                <li><a href="../admin/manage-customers.php">👤 Manage Customers</a></li>
                <li><a href="../admin/approve-accounts.php">✅ Approve Accounts</a></li>
            </ul>

            <div class="sidebar-section-title">Service Requests</div>
            <ul class="sidebar-nav">
                <li><a href="../admin/approve-requests.php">💳 ATM / Cheques</a></li>
                <li><a href="../admin/approve-loans.php">💰 Loans</a></li>
            </ul>

            <div class="sidebar-section-title">Transactions</div>
            <ul class="sidebar-nav">
                <li><a href="../admin/view-transactions.php">📄 View Transactions</a></li>
            </ul>

            <div class="sidebar-section-title">Session</div>
            <ul class="sidebar-nav">
                <li><a href="../logout.php">🚪 Logout</a></li>
            </ul>
        </aside>

        <div class="main">
            <div class="topbar">
                <div>
                    <div class="topbar-title">
                        Welcome,
                        <?php echo htmlspecialchars($user['full_name'] ?? $user['username'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="topbar-subtitle">
                        Staff tools for managing customers, approvals and day‑to‑day transactions.[web:344]
                    </div>
                </div>
                <div class="topbar-right">
                    <span>Theme</span>
                    <button type="button" class="theme-toggle" id="themeToggle">🌙</button>
                </div>
            </div>

            <div class="main-inner">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-label">Total Customers</div>
                        <div class="stat-value"><?php echo (int) $totalCustomers; ?></div>
                        <div class="stat-tag">All registered customers.[web:37]</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Pending Accounts</div>
                        <div class="stat-value"><?php echo (int) $pendingAccounts; ?></div>
                        <div class="stat-tag">New accounts awaiting approval.[web:90]</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Pending Loans</div>
                        <div class="stat-value"><?php echo (int) $pendingLoans; ?></div>
                        <div class="stat-tag">Loan applications to review.[web:273]</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">ATM Requests</div>
                        <div class="stat-value"><?php echo (int) $pendingAtmRequests; ?></div>
                        <div class="stat-tag">Pending card requests.[web:291]</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Cheque Requests</div>
                        <div class="stat-value"><?php echo (int) $pendingChqRequests; ?></div>
                        <div class="stat-tag">Pending cheque books.[web:296]</div>
                    </div>
                </div>

                <div class="panel-grid">
                    <div class="panel">
                        <div class="panel-header">
                            <div>
                                <div class="panel-title">Recent Transactions</div>
                                <div class="panel-subtitle">
                                    Latest 10 movements handled on all customer accounts.[web:90]
                                </div>
                            </div>
                            <a href="../admin/view-transactions.php" class="link-sm">View all</a>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Account / Customer</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentTransactions)): ?>
                                    <tr>
                                        <td colspan="4">No transactions recorded yet.[web:7]</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentTransactions as $t): ?>
                                        <tr>
                                            <td><?php echo formatDate($t['created_at']); ?></td>
                                            <td>
                                                <div>
                                                    <?php echo htmlspecialchars($t['account_number'] ?? '—', ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                                <div class="info-small">
                                                    <?php
                                                    $name = $t['full_name'] ?: $t['username'] ?: '';
                                                    echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                                                    ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php
                                                $badgeClass = 'badge-deposit';
                                                if ($t['type'] === 'withdrawal')
                                                    $badgeClass = 'badge-withdrawal';
                                                if ($t['type'] === 'transfer')
                                                    $badgeClass = 'badge-transfer';
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>">
                                                    <?php echo htmlspecialchars(ucfirst($t['type']), ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatCurrency((float) $t['amount']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="panel">
                        <div class="panel-header">
                            <div>
                                <div class="panel-title">Quick Actions</div>
                                <div class="panel-subtitle">
                                    Go directly to frequent workflows used by staff.[web:344]
                                </div>
                            </div>
                        </div>
                        <ul style="list-style:none;margin:0;padding:0;font-size:13px;">
                            <li style="margin-bottom:6px;">
                                <a class="link-sm" href="../admin/approve-accounts.php">Approve new customer
                                    accounts</a>
                            </li>
                            <li style="margin-bottom:6px;">
                                <a class="link-sm" href="../admin/approve-requests.php">Process ATM / cheque book
                                    requests</a>
                            </li>
                            <li style="margin-bottom:6px;">
                                <a class="link-sm" href="../admin/approve-loans.php">Review and approve loan
                                    applications</a>
                            </li>
                            <li style="margin-bottom:6px;">
                                <a class="link-sm" href="../admin/manage-customers.php">Update customer contact
                                    details</a>
                            </li>
                            <li>
                                <a class="link-sm" href="../admin/view-transactions.php">Search full transaction
                                    history</a>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Theme toggle for staff panel
        (function () {
            const root = document.documentElement;
            const saved = localStorage.getItem('theme');
            if (saved === 'light' || saved === 'dark') {
                root.setAttribute('data-theme', saved);
            } else {
                root.setAttribute('data-theme', 'dark');
            }

            const btn = document.getElementById('themeToggle');
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
</body>

</html>