<?php
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/functions.php';


session_start();
$logged_in = !empty($_SESSION['user_id']);


// Auto-login via rememberme cookie
if (!$logged_in && !empty($_COOKIE['rememberme'])) {
    list($uid, $token) = explode(':', $_COOKIE['rememberme']);
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND remember_token = ? LIMIT 1');
    $stmt->execute([$uid, $token]);
    $u = $stmt->fetch();
    if ($u) {
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['LAST_ACTIVITY'] = time();
        $logged_in = true;
    }
}


include __DIR__ . '/assets/templates/header.html';
?>


<div class="app-shell">
    <nav>
        <a href="index.php" class="brand">CalcApp</a>
        <div class="nav-actions">
            <button id="theme-toggle">Toggle theme</button>
            <?php if ($logged_in): ?>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="signup.php">Sign up</a>
            <?php endif; ?>
        </div>
    </nav>


    <main>
        <section id="calculator-section">
            <h2>Calculator</h2>
            <div class="mode-toggle">
                <label><input type="radio" name="calc-mode" value="basic" checked> Basic</label>
                <label><input type="radio" name="calc-mode" value="scientific"> Scientific</label>
                <label><input type="radio" name="calc-mode" value="binary"> Binary/Bitwise</label>
            </div>


            <div id="basic-calc" class="calc-panel">
                <!-- basic calculator UI -->
                <input id="display" readonly>
                <div id="basic-buttons"></div>
            </div>


            <div id="scientific-calc" class="calc-panel" style="display:none;">
                <!-- scientific UI -->
                <input id="sdisplay" readonly>
                <div id="scientific-buttons"></div>
            </div>


            <div id="binary-calc" class="calc-panel" style="display:none;">
                <!-- binary UI -->
                <textarea id="bdisplay" readonly></textarea>
                <div id="binary-buttons"></div>
            </div>
            <?php include __DIR__ . '/assets/templates/footer.html'; ?>