<?php
require_once 'config.php';

// If already logged in, redirect based on role
if (isLoggedIn()) {
    $role = $_SESSION['user']['role'] ?? '';

    switch ($role) {
        case 'admin':
        case 'manager':
            header('Location: admin/dashboard.php');
            exit;
        case 'staff':
            header('Location: staff/dashboard.php');
            exit;
        case 'customer':
            header('Location: customer/dashboard.php');
            exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecureBank - Modern Banking System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: var(--bg-primary, #131a1a);
            color: var(--text-primary, #e0e0e0);
        }

        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-secondary, #181f20);
            border-bottom: 1px solid var(--border-color, #2a3334);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-logo {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: linear-gradient(135deg, #2d5f82, #8b6f47);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #fff;
            font-size: 18px;
        }

        .brand-title {
            font-size: 20px;
            font-weight: 700;
        }

        nav a {
            color: var(--text-secondary, #c0c0c0);
            text-decoration: none;
            margin-left: 20px;
            font-size: 14px;
        }

        nav a:hover {
            color: var(--primary, #2d5f82);
        }

        .cta-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .btn {
            border-radius: 20px;
            padding: 8px 18px;
            font-size: 14px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary, #2d5f82);
            color: #fff;
            border-color: var(--primary, #2d5f82);
        }

        .btn-primary:hover {
            background: var(--secondary, #8b6f47);
            border-color: var(--secondary, #8b6f47);
        }

        .btn-outline {
            background: transparent;
            color: var(--text-primary, #e0e0e0);
            border-color: var(--border-color, #2a3334);
        }

        .btn-outline:hover {
            border-color: var(--primary, #2d5f82);
            color: var(--primary, #2d5f82);
        }

        .theme-toggle {
            background: transparent;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            border: 1px solid var(--border-color, #2a3334);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-secondary, #c0c0c0);
        }

        main {
            flex: 1;
        }

        .hero {
            max-width: 1100px;
            margin: 40px auto 20px;
            padding: 0 20px;
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr);
            gap: 40px;
            align-items: center;
        }

        .hero-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .hero-subtitle {
            color: var(--text-secondary, #c0c0c0);
            margin-bottom: 20px;
            font-size: 15px;
        }

        .hero-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .badge {
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid var(--border-color, #2a3334);
            background: rgba(45, 95, 130, 0.15);
            color: var(--primary, #2d5f82);
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
        }

        .hero-note {
            font-size: 12px;
            color: var(--text-secondary, #c0c0c0);
        }

        .hero-card {
            background: radial-gradient(circle at top, rgba(45, 95, 130, 0.4), transparent 55%),
                radial-gradient(circle at bottom, rgba(139, 111, 71, 0.3), transparent 55%),
                var(--bg-secondary, #181f20);
            border-radius: 16px;
            padding: 20px;
            border: 1px solid rgba(255, 255, 255, 0.04);
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.6);
        }

        .hero-metric-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .metric-card {
            background: rgba(0, 0, 0, 0.25);
            border-radius: 10px;
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .metric-label {
            font-size: 11px;
            color: var(--text-secondary, #c0c0c0);
        }

        .metric-value {
            font-size: 16px;
            font-weight: 600;
            margin-top: 4px;
        }

        .metric-tag {
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 999px;
            background: rgba(46, 184, 111, 0.12);
            color: var(--success, #2eb86f);
            display: inline-block;
            margin-top: 6px;
        }

        .hero-mini-list {
            font-size: 12px;
            margin: 0;
            padding-left: 18px;
            color: var(--text-secondary, #c0c0c0);
        }

        .hero-mini-list li+li {
            margin-top: 4px;
        }

        .section {
            max-width: 1100px;
            margin: 10px auto 40px;
            padding: 0 20px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .section-subtitle {
            color: var(--text-secondary, #c0c0c0);
            font-size: 13px;
            margin-bottom: 20px;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 16px;
        }

        .feature-card {
            background: var(--bg-secondary, #181f20);
            border-radius: 12px;
            padding: 16px;
            border: 1px solid var(--border-color, #2a3334);
        }

        .feature-icon {
            font-size: 20px;
            margin-bottom: 6px;
        }

        .feature-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .feature-text {
            font-size: 13px;
            color: var(--text-secondary, #c0c0c0);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 16px;
        }

        .stat-card {
            border-radius: 12px;
            padding: 16px;
            border: 1px solid var(--border-color, #2a3334);
            background: var(--bg-secondary, #181f20);
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-secondary, #c0c0c0);
        }

        .stat-value {
            font-size: 20px;
            font-weight: 700;
            margin-top: 4px;
        }

        footer {
            border-top: 1px solid var(--border-color, #2a3334);
            padding: 12px 20px;
            font-size: 12px;
            color: var(--text-secondary, #c0c0c0);
            text-align: center;
            background: var(--bg-secondary, #181f20);
        }

        @media (max-width: 768px) {
            .hero {
                grid-template-columns: 1fr;
                margin-top: 20px;
            }

            header {
                padding: 10px 15px;
            }

            nav {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="page-wrapper">

        <header>
            <div class="brand">
                <div class="brand-logo">SB</div>
                <div class="brand-title">SecureBank</div>
            </div>

            <nav>
                <a href="#features">Features</a>
                <a href="#roles">User Roles</a>
                <a href="#security">Security</a>
            </nav>

            <div class="cta-buttons">
                <button class="theme-toggle" type="button" id="themeToggle" title="Toggle theme">
                    🌙
                </button>
                <a href="login.php" class="btn btn-outline">Login</a>
                <a href="register.php" class="btn btn-primary">Open Account</a>
            </div>
        </header>

        <main>
            <section class="hero">
                <div>
                    <div class="hero-badges">
                        <span class="badge">13 tables · 4 classes</span>
                        <span class="badge">Bcrypt authentication</span>
                        <span class="badge">Dark / Light theme</span>
                    </div>
                    <h1 class="hero-title">Full Banking System Starter for Admin, Staff & Customers</h1>
                    <p class="hero-subtitle">
                        Multi‑role internet banking system with ready login, registration,
                        dashboard structure, theming and secure database schema. Connect, extend, and deploy on XAMPP in
                        minutes.
                    </p>

                    <div class="hero-actions">
                        <a href="login.php" class="btn btn-primary">Login as Admin</a>
                        <a href="register.php" class="btn btn-outline">Create Customer Account</a>
                    </div>
                    <div class="hero-note">
                        Default admin: <strong>admin / admin123</strong> (after importing
                        <code>database/banking_system.sql</code>).
                    </div>
                </div>

                <div class="hero-card">
                    <div class="hero-metric-grid">
                        <div class="metric-card">
                            <div class="metric-label">Database Objects</div>
                            <div class="metric-value">13 Tables</div>
                            <span class="metric-tag">Normalized schema</span>
                        </div>
                        <div class="metric-card">
                            <div class="metric-label">Portal Layers</div>
                            <div class="metric-value">3 Roles</div>
                            <span class="metric-tag">Admin · Staff · Customer</span>
                        </div>
                        <div class="metric-card">
                            <div class="metric-label">Security</div>
                            <div class="metric-value">Bcrypt</div>
                            <span class="metric-tag">Prepared statements</span>
                        </div>
                        <div class="metric-card">
                            <div class="metric-label">Styling</div>
                            <div class="metric-value">1000+ CSS</div>
                            <span class="metric-tag">Dark / Light</span>
                        </div>
                    </div>

                    <ul class="hero-mini-list">
                        <li>Config + functions + auth utilities ready to use</li>
                        <li>Skeleton pages for admin, staff and customer portals</li>
                        <li>Upload structure for KYC, loan and document handling</li>
                    </ul>
                </div>
            </section>

            <section id="features" class="section">
                <h2 class="section-title">Core Features Included</h2>
                <p class="section-subtitle">
                    Start from a working base instead of a blank page — plug in business logic and UI on top of a
                    consistent structure.
                </p>

                <div class="feature-grid">
                    <div class="feature-card">
                        <div class="feature-icon">🔐</div>
                        <div class="feature-title">Authentication & Sessions</div>
                        <div class="feature-text">
                            Login, registration, logout, role checks and session helpers are centralized
                            in <code>config.php</code> and <code>functions.php</code>.
                        </div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🏦</div>
                        <div class="feature-title">Admin & Staff Portals</div>
                        <div class="feature-text">
                            Admin and staff directories are pre‑wired for dashboards, approvals, management
                            pages and activity logs.
                        </div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">👤</div>
                        <div class="feature-title">Customer Banking Area</div>
                        <div class="feature-text">
                            Customer section includes placeholders for accounts, deposits, loans, requests
                            and profile management.
                        </div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🎨</div>
                        <div class="feature-title">Unified Theme System</div>
                        <div class="feature-text">
                            Dark first design with smooth toggle, shared CSS tokens and responsive layout
                            driven by <code>css/style.css</code>.
                        </div>
                    </div>
                </div>
            </section>

            <section id="roles" class="section">
                <h2 class="section-title">Role‑Based Portals</h2>
                <p class="section-subtitle">
                    Each role gets its own folder, navigation header and dashboard to keep responsibilities separated.
                </p>

                <div class="feature-grid">
                    <div class="feature-card">
                        <div class="feature-icon">🛠️</div>
                        <div class="feature-title">Admin / Manager</div>
                        <div class="feature-text">
                            Manage users, staff, branches, approvals, reports and audit trail via files inside
                            <code>admin/</code>.
                        </div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📋</div>
                        <div class="feature-title">Staff</div>
                        <div class="feature-text">
                            Handle customer onboarding, document verification and request processing through
                            the <code>staff/</code> area.
                        </div>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">💳</div>
                        <div class="feature-title">Customer</div>
                        <div class="feature-text">
                            Allow customers to view balances, transactions, deposits, loans and service
                            requests under <code>customer/</code>.
                        </div>
                    </div>
                </div>
            </section>

            <section id="security" class="section">
                <h2 class="section-title">Secure by Design</h2>
                <p class="section-subtitle">
                    The backend is prepared for production‑grade security practices from day one.
                </p>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-label">Password Handling</div>
                        <div class="stat-value">Bcrypt</div>
                        <div class="stat-label">Password hashing & verification helpers.</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Database Access</div>
                        <div class="stat-value">Prepared</div>
                        <div class="stat-label">All access via parameterized queries.</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Input Validation</div>
                        <div class="stat-value">Sanitized</div>
                        <div class="stat-label">Centralized sanitization & validators.</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Audit Trail</div>
                        <div class="stat-value">Logged</div>
                        <div class="stat-label">Activity stored in dedicated table.</div>
                    </div>
                </div>
            </section>
        </main>

        <footer>
            SecureBank Demo · Banking System Starter · Import <code>database/banking_system.sql</code> then login as
            <strong>admin / admin123</strong>.
        </footer>
    </div>

    <script>
        // Simple theme toggle synced with localStorage
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
                if (current === 'light') {
                    btn.textContent = '☀️';
                } else {
                    btn.textContent = '🌙';
                }
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
    <script src="js/utils.js"></script>
    <script src="js/theme.js"></script>
    <script src="js/validation.js"></script>
    <script src="js/main.js"></script>
</body>

</html>