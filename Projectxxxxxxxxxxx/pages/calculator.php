<?php
require_once '../config/session.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Calculator</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
</head>

<body>
    <nav>
        <button id="theme-toggle">Toggle Light/Dark</button>
        <a href="converter.php">Converter</a>
        <a href="profile.php">Profile</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>
    <div class="calc-container">
        <!-- Calculator UI and JS here -->
    </div>
    <script src="../assets/js/calculator.js"></script>
    <script src="../includes/theme-toggle.js"></script>
</body>

</html>