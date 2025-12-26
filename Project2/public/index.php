<?php
include 'partials/header.php';
include 'partials/nav.php';
?>
<div class="container">
    <div class="card" style="text-align: center; padding: 4rem 2rem;">
        <h1 class="card-title" style="font-size: 3rem;">Smart [CC] Tools </h1>
        <p style="font-size: 1.25rem; color: var(--text-muted); margin-bottom: 2rem;">
            A secure, modern suite of utilities featuring scientific calculators and multi-unit converters.
        </p>
        <div class="actions" style="justify-content: center;">
            <?php if (isset($_SESSION['user'])): ?>
                <a class="btn" href="dashboard.php">Go to Dashboard</a>
            <?php else: ?>
                <a class="btn" href="login.php">Login</a>
                <a class="btn accent" href="register.php">Create Account</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'partials/footer.php'; ?>