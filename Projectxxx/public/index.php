<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glass Blue Dark UI</title>
    <style>
        /* Base Reset & Body */
        body {
            margin: 0;
            padding: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: radial-gradient(circle at top left, #1e3a8a 0, #020617 45%, #000 100%);
            color: #e5e7eb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Ambient Background Glow */
        .ambient-glow {
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at 10% 20%, rgba(56, 189, 248, 0.20) 0, transparent 55%),
                radial-gradient(circle at 90% 80%, rgba(59, 130, 246, 0.25) 0, transparent 60%);
            pointer-events: none;
            z-index: -1;
        }

        /* Main Glass Card */
        .glass-card {
            width: min(420px, 92vw);
            padding: 28px 26px 24px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.90), rgba(15, 23, 42, 0.70));
            box-shadow:
                0 18px 40px rgba(15, 23, 42, 0.85),
                0 0 0 1px rgba(148, 163, 184, 0.20);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(59, 130, 246, 0.45);
        }

        /* Typography */
        .title {
            margin: 0 0 4px;
            font-size: 1.5rem;
            letter-spacing: 0.03em;
            color: #eff6ff;
        }

        .subtitle {
            margin: 0 0 18px;
            font-size: 0.9rem;
            color: #9ca3af;
        }

        /* Form Layout */
        .login-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .input-label {
            font-size: 0.78rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #6b7280;
            display: block;
        }

        /* Add margin-top to password label specifically if needed, 
           or rely on the gap in flex container (which usually handles it).
           The original code had margin-top:4px on the second label. */
        .input-label+.input-label {
            margin-top: 4px;
        }

        /* Input Fields */
        .input-field {
            margin-top: 5px;
            width: 100%;
            padding: 10px 11px;
            border-radius: 11px;
            border: 1px solid rgba(148, 163, 184, 0.55);
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(15, 23, 42, 0.75));
            color: #e5e7eb;
            font-size: 0.9rem;
            outline: none;
            box-shadow: 0 0 0 1px rgba(15, 23, 42, 0.9);
            box-sizing: border-box;
            transition:
                border-color 160ms ease,
                box-shadow 160ms ease,
                background 160ms ease;
        }

        /* Input Focus State */
        .input-field:focus {
            border-color: #38bdf8;
            box-shadow:
                0 0 0 1px rgba(56, 189, 248, 0.9),
                0 0 20px rgba(56, 189, 248, 0.25);
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.98), rgba(15, 23, 42, 0.85));
        }

        /* Submit Button */
        .submit-btn {
            margin-top: 10px;
            width: 100%;
            padding: 10px 14px;
            border-radius: 999px;
            border: none;
            outline: none;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            background: linear-gradient(135deg, #38bdf8, #2563eb);
            color: #0b1120;
            box-shadow:
                0 12px 30px rgba(37, 99, 235, 0.60),
                0 0 0 1px rgba(15, 23, 42, 1);
            transition:
                transform 140ms ease,
                box-shadow 140ms ease,
                filter 140ms ease;
        }

        /* Button Hover State */
        .submit-btn:hover {
            filter: brightness(1.08);
            box-shadow:
                0 14px 36px rgba(37, 99, 235, 0.75),
                0 0 0 1px rgba(15, 23, 42, 1);
        }

        /* Button Active/Click State */
        .submit-btn:active {
            transform: translateY(1px) scale(0.99);
        }

        /* Footer Links */
        .card-footer {
            margin-top: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.78rem;
            color: #6b7280;
        }

        .link-action {
            color: #38bdf8;
            cursor: pointer;
        }

        .link-action:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <!-- Background Ambient Glow -->
    <div class="ambient-glow"></div>

    <!-- Glass Card Container -->
    <div class="glass-card">
        <h1 class="title">Welcome</h1>
        <p class="subtitle">Sign in to continue to your dashboard.</p>

        <!-- Login Form -->
        <form method="post" action="" class="login-form">
            <label class="input-label">
                Email
                <input type="email" name="email" required class="input-field" />
            </label>

            <label class="input-label">
                Password
                <input type="password" name="password" required class="input-field" />
            </label>

            <button type="submit" class="submit-btn">
                Sign In
            </button>
        </form>

        <div class="card-footer">
            <span>Forgot password?</span>
            <span class="link-action">Create account</span>
        </div>
    </div>
</body>

</html>