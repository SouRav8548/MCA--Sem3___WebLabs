<?php
require_once '../config/session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
</head>

<body>
    <nav class="nav-bar">
        <div class="logo">Smart Calc Suite</div>
        <div class="nav-links">
            <a href="calculator.php">Calculator</a>
            <a href="converter.php">Converter</a>
            <a href="profile.php">Profile</a>
            <a href="../auth/logout.php">Logout</a>
            <button id="theme-toggle">Light/Dark</button>
        </div>
    </nav>
    <div class="container">
        <h2>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p>Select a feature from the navigation bar.</p>
    </div>
    <script src="../includes/theme-toggle.js"></script>
</body>

</html>