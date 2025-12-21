<?php
require 'config.php';
include 'header.php';
?>
<div class="card">
    <h1>Welcome to QuizPlatform</h1>
    <p>Register or log in to take a timed multiple-choice quiz. Questions are randomly selected each time.</p>
    <?php if (!empty($_SESSION['user'])): ?>
        <a href="quiz.php" class="btn btn-primary">Start Quiz</a>
    <?php else: ?>
        <a href="login.php" class="btn btn-primary">Login to start</a>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>