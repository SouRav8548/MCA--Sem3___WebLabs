<?php
require_once __DIR__ . '/../config/db.php';
include 'partials/header.php';
include 'partials/nav.php';
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
// Session Timeout (30 mins)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();
?>
<div class="container">
    <div style="margin-bottom: 2rem;">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user']['name']); ?>!</h1>
        <p style="color: var(--text-muted);">Access your tools below.</p>
    </div>

    <div class="grid grid-3">
        <div class="card">
            <h3 class="card-title"><i class="fa-solid fa-calculator"></i> Calculator</h3>
            <p>Advanced scientific and basic modes.</p>
            <a class="btn" href="calculator.php" style="width: 100%; margin-top: 1rem;">Open</a>
        </div>
        <div class="card">
            <h3 class="card-title"><i class="fa-solid fa-right-left"></i> Converter</h3>
            <p>Currency, Weight, Length, Number Systems.</p>
            <a class="btn" href="converter.php" style="width: 100%; margin-top: 1rem;">Open</a>
        </div>
        <div class="card">
            <h3 class="card-title"><i class="fa-solid fa-user-gear"></i> Profile</h3>
            <p>Manage your account settings.</p>
            <a class="btn accent" href="profile.php" style="width: 100%; margin-top: 1rem;">Manage</a>
        </div>
    </div>
</div>
<?php include 'partials/footer.php'; ?>