<nav class="navbar">
    <div class="nav-brand">
        <i class="fa-solid fa-layer-group"><a href="index.php"></i> Smart [CC] Tools</a>
    </div>
    <div class="nav-links">
        <?php if (!empty($_SESSION['user'])): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="calculator.php">Calculator</a>
            <a href="converter.php">Converter</a>
            <a href="profile.php">Profile</a>
            <a href="logout.php" style="color: var(--error);">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="btn small">Sign up</a>
        <?php endif; ?>
        <button id="themeToggle" class="btn accent small"><i class="fa-solid fa-sun"></i></button>
    </div>
</nav>