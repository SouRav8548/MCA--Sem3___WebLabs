<?php
require_once __DIR__ . '/../config/db.php';
include 'partials/header.php';
include 'partials/nav.php';

if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
?>
<div class="container">
    <!-- calc-container class limits width nicely while keeping it centered -->
    <div class="card calc-container">
        <h2 class="card-title">Calculator</h2>

        <div class="tabbar">
            <button id="basicTab" class="active">Basic</button>
            <button id="scientificTab">Scientific</button>
        </div>

        <input id="calcDisplay" readonly value="0">

        <div id="calcRoot">
            <!-- JS injects keys here -->
        </div>

        <div style="text-align: center; margin-top: 1rem; color: var(--text-muted); font-size: 0.85rem;">
            <i class="fa-regular fa-keyboard"></i> Keyboard Support Enabled
        </div>
    </div>
</div>
<script src="assets/js/calculator.js"></script>
<?php include 'partials/footer.php'; ?>