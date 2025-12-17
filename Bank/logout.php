<?php
require_once 'config.php';

// If user is logged in, log the logout activity before destroying session
if (isLoggedIn()) {
    $userId = (int) $_SESSION['user']['id'];
    if (isset($conn) && $conn instanceof mysqli) {
        logActivity($conn, $userId, 'logout', 'User logged out');
    }
}

// Clear all session data
$_SESSION = [];

// Destroy session cookie (if any)
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy the session
session_destroy();

// Optional: small flash message after redirect (new session)
session_start();
Message::addSuccess('You have been logged out successfully.');

// Redirect to login page
header('Location: login.php');
exit;
