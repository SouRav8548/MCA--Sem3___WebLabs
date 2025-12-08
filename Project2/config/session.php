<?php
// Configure session timeout to 30 minutes
ini_set('session.gc_maxlifetime', 1800);
session_set_cookie_params(1800, '/', '', isset($_SERVER["HTTPS"]), true);
session_start();

if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) != 'login.php' && basename($_SERVER['PHP_SELF']) != 'signup.php') {
    header('Location: ../auth/login.php');
    exit;
}
?>