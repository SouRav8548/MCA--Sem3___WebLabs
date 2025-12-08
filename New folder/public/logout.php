<?php
require_once __DIR__ . '/inc/functions.php';
session_start();
if (!empty($_SESSION['user_id'])) {
// clear session
session_unset();
session_destroy();
}
// clear cookie
setcookie('rememberme', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
header('Location: login.php');
exit;