<?php
// header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Quiz Platform</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/question.css">

</head>
<body>
<header class="main-header">
    <nav class="navbar">
        <div class="nav-left">
            <a href="index.php" class="brand">QuizPlatform</a>
        </div>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">☰</button>
        <ul class="nav-links" id="navLinks">
            <li><a href="index.php">Home</a></li>

            <?php if (!empty($_SESSION['user'])): ?>
                <?php if ($_SESSION['user']['role'] === 'user'): ?>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="quiz.php">Quiz</a></li>
                <?php endif; ?>
                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <li><a href="admin/questions.php">Admin Panel</a></li>
                <?php endif; ?>
                <li><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">User Login</a></li>
                <li><a href="register.php">Register</a></li>
                <li><a href="admin_login.php">Admin Login</a></li>
            <?php endif; ?>

            <!-- Timer (for quiz), shown/hidden by JS -->
            <li class="timer-item" id="timerWrapper">
                <span id="quizTimerDisplay">20</span>
            </li>

            <li>
                <button type="button" id="themeToggle" class="icon-btn" title="Toggle light/dark">🌙</button>
            </li>
        </ul>
    </nav>
</header>
<main class="page-content">
