<?php
// includes/csrf.php
function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}
function csrf_field()
{
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES) . '">';
}
function csrf_verify($token)
{
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token ?? '');
}
