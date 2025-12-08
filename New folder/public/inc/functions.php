<?php
// inc/functions.php


function generateToken($len = 48) {
return bin2hex(random_bytes($len));
}


function sanitize($v) {
return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
}


function require_login() {
session_start();
if (empty($_SESSION['user_id'])) {
header('Location: login.php');
exit;
}
}


// CSRF token helpers
function csrf_token() {
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
return $_SESSION['csrf'];
}


function csrf_check($token) {
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
return hash_equals($_SESSION['csrf'] ?? '', $token ?? '');
}