<?php
session_start();

// If user is already logged in, go to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Welcome - Smart Calc Suite</title>
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <nav class="nav-bar">
        <div class="logo">Smart Calc Suite</div>
        <div class="nav-links">
            <a href="auth/login.php">Login</a>
            <a href="auth/signup.php">Sign Up</a>
            <button id="theme-toggle">Light/Dark</button>
        </div>
    </nav>

    <div class="container">
        <h1>Welcome to Smart Calc Suite</h1>
        <p>
            Login or sign up to access calculators, converters, and your profile.
        </p>
        <div class="home-actions">
            <a class="btn-primary" href="auth/login.php">Login</a>
            <a class="btn-secondary" href="auth/signup.php">Sign Up</a>
        </div>
    </div>

    <script src="includes/theme-toggle.js"></script>
</body>

</html>