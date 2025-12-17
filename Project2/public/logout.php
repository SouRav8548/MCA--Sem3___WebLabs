<?php
require_once __DIR__ . '/../config/db.php';
session_start();

if (!empty($_SESSION['user'])) {
    $pdo->prepare('UPDATE users SET remember_token = NULL WHERE id = ?')->execute([$_SESSION['user']['id']]);
}

$_SESSION = [];
session_destroy();
setcookie('remember', '', time() - 3600, '/', '', false, true);
header('Location: login.php');
exit;
?>