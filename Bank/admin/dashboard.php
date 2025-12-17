<?php
require_once '../config.php';
checkAnyRole([ROLE_ADMIN, ROLE_MANAGER]);

$user = $_SESSION['user'] ?? null;

// Quick stats
$totalUsers = fetchOne($conn, "SELECT COUNT(*) AS c FROM users", '')['c'] ?? 0;
$totalCustomers = fetchOne($conn, "SELECT COUNT(*) AS c FROM users WHERE role = 'customer'", '')['c'] ?? 0;
$pendingAccounts = fetchOne($conn, "SELECT COUNT(*) AS c FROM users WHERE role = 'customer' AND status = 'pending'", '')['c'] ?? 0;
$totalBranches = fetchOne($conn, "SELECT COUNT(*) AS c FROM branches", '')['c'] ?? 0;
$pendingLoans = fetchOne($conn, "SELECT COUNT(*) AS c FROM loans WHERE status = 'pending'", '')['c'] ?? 0;
$pendingRequests = fetchOne(
    $conn,
    "SELECT
        (SELECT COUNT(*) FROM atm_card_requests  WHERE status = 'pending') +
        (SELECT COUNT(*) FROM cheque_book_requests WHERE status = 'pending') AS c",
    ''
)['c'] ?? 0;

// Recent activity log
$recentActivity = fetchAll(
    $conn,
    "SELECT a.*, u.full_name, u.username
     FROM activity_log a
     LEFT JOIN users u ON u.id = a.user_id
     ORDER BY a.created_at DESC
     LIMIT 8"
);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SecureBank</title>
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
            grid-template-columns: 230px 1fr;
            min-height: 100vh;
        }

        .sidebar {
            background: var(--bg-secondary, #181f20);
            border-right: 1px solid var(--border-color, #2a3334);
            padding: 18px 14px;
        }

        .sidebar .brand {
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

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 10px 0 0;
            font-size: 13px;
        }

        .sidebar-nav li {
            margin-bottom: 4px;
        }

        .sidebar-nav a {
            display: block;
            padding: 7px 9px;
            border-radius: 8px;
            color: var(--text-secondary, #c0c0c0);
            text-decoration: none;
        }

        .sidebar-nav a:hover {
            background: var(--hover-bg, #202729);
            color: var(--primary, #2d5f82);
        }

        .sidebar-nav a.active {
            background: var(--primary, #2d5f82);
            color: #fff;
        }

        .sidebar-section-title {
            margin: 12px 4px 6px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-secondary, #999);
        }

        .main {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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

        .badge-green {
            background: rgba(46, 184, 111, 0.15);
            color: var(--success, #2eb86f);
        }

        .badge-orange {
            background: rgba(244, 165, 66, 0.15);
            color: var(--warning, #f4a542);
        }

        .badge-red {
            background: rgba(201, 31, 47, 0.15);
            color: var(--danger, #c91f2f);
        }

        .link-sm {
            font-size: 11px;
            color: var(--primary, #2d5f82);
            text-decoration: none;
        }

        .link-sm:hover {
            text-decoration: underline;
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
                <div class="brand-text">Admin Panel</div>
            </div>

            <div class="sidebar-section-title">Overview</div>
            <ul class="sidebar-nav">
                <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
            </ul>

            <div class="sidebar-section-title">Management</div>
            <ul class="sidebar-nav">
                <li><a href="manage-users.php">👥 Users</a></li>
                <li><a href="manage-staff.php">🧑‍💼 Staff</a></li>
                <li><a href="manage-customers.php">👤 Customers</a></li>
                <li><a href="manage-branches.php">🏦 Branches</a></li>
            </ul>

            <div class="sidebar-section-title">Approvals</div>
            <ul class="sidebar-nav">
                <li><a href="approve-accounts.php">✅ Accounts</a></li>
                <li><a href="approve-loans.php">💰 Loans</a></li>
                <li><a href="approve-requests.php">📨 ATM / Cheques</a></li>
            </ul>

            <div class="sidebar-section-title">Monitoring</div>
            <ul class="sidebar-nav">
                <li><a href="view-transactions.php">💳 Transactions</a></li>
                <li><a href="reports.php">📈 Reports</a></li>
                <li><a href="activity-log.php">📝 Activity Log</a></li>
            </ul>

            <div class="sidebar-section-title">Session</div>
            <ul class="sidebar-nav">
                <li><a href="../logout.php">🚪 Logout</a></li>
            </ul>
        </aside>

        <div class="main">
            <div class="topbar">
                <div>
                    <div class="topbar-title">Welcome,
                        <?php echo htmlspecialchars($user['full_name'] ?? $user['username'], ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                    <div class="topbar-subtitle">
                        Role: <?php echo htmlspecialchars($user['role'] ?? '', ENT_QUOTES, 'UTF-8'); ?> · Admin overview
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
                        <div class="stat-label">Total Users</div>
                        <div class="stat-value"><?php echo (int) $totalUsers; ?></div>
                        <div class="stat-tag">All roles</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Customers</div>
                        <div class="stat-value"><?php echo (int) $totalCustomers; ?></div>
                        <div class="stat-tag">Active + Pending</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Pending Customer Accounts</div>
                        <div class="stat-value"><?php echo (int) $pendingAccounts; ?></div>
                        <div class="stat-tag">Awaiting approval</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Branches</div>
                        <div class="stat-value"><?php echo (int) $totalBranches; ?></div>
                        <div class="stat-tag">Configured in DB</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Pending Loans</div>
                        <div class="stat-value"><?php echo (int) $pendingLoans; ?></div>
                        <div class="stat-tag">Loan applications</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Pending Service Requests</div>
                        <div class="stat-value"><?php echo (int) $pendingRequests; ?></div>
                        <div class="stat-tag">ATM + Cheques</div>
                    </div>
                </div>

                <div class="panel-grid">
                    <div class="panel">
                        <div class="panel-header">
                            <div>
                                <div class="panel-title">Recent Activity</div>
                                <div class="panel-subtitle">Latest system actions from all users</div>
                            </div>
                            <a href="activity-log.php" class="link-sm">View all</a>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Details</th>
                                    <th>When</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recentActivity)): ?>
                                    <tr>
                                        <td colspan="4">No activity recorded yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recentActivity as $row): ?>
                                        <tr>
                                            <td>
                                                <?php
                                                $name = $row['full_name'] ?: $row['username'] ?: 'System';
                                                echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['action'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td style="max-width: 200px;">
                                                <?php echo htmlspecialchars($row['details'], ENT_QUOTES, 'UTF-8'); ?>
                                            </td>
                                            <td><?php echo formatDate($row['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="panel">
                        <div class="panel-header">
                            <div>
                                <div class="panel-title">Approval Queue Snapshot</div>
                                <div class="panel-subtitle">Quick view of pending approvals</div>
                            </div>
                        </div>
                        <table>
                            <tbody>
                                <tr>
                                    <td>Customer Accounts</td>
                                    <td><span class="badge badge-orange"><?php echo (int) $pendingAccounts; ?>
                                            pending</span></td>
                                    <td><a href="approve-accounts.php" class="link-sm">Review</a></td>
                                </tr>
                                <tr>
                                    <td>Loan Applications</td>
                                    <td><span class="badge badge-orange"><?php echo (int) $pendingLoans; ?>
                                            pending</span></td>
                                    <td><a href="approve-loans.php" class="link-sm">Review</a></td>
                                </tr>
                                <tr>
                                    <td>ATM / Cheque Requests</td>
                                    <td><span class="badge badge-orange"><?php echo (int) $pendingRequests; ?>
                                            pending</span></td>
                                    <td><a href="approve-requests.php" class="link-sm">Review</a></td>
                                </tr>
                                <tr>
                                    <td colspan="3"
                                        style="font-size: 11px; color: var(--text-secondary, #c0c0c0); padding-top: 8px;">
                                        Use the links to go directly to approval pages. This view is read‑only.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Theme toggle
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