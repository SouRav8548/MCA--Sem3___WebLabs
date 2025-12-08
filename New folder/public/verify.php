<?php
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';


$token = $_GET['token'] ?? null;
$ok = false;
if ($token) {
$ok = verify_user_token($token);
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Verify</title></head>
<body>
<?php if ($ok): ?>
<p>Your email is verified. <a href="login.php">Login</a></p>
<?php else: ?>
<p>Invalid or expired token.</p>
<?php endif; ?>
</body>
</html>