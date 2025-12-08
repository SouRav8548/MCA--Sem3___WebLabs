<?php
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
if (!csrf_check($_POST['csrf'] ?? '')) $errors[] = 'Bad request.';
$email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$password = $_POST['password'] ?? '';
$remember = !empty($_POST['remember']);


$user = find_user_by_email($email);
if (!$user || !password_verify($password, $user['password_hash'])) {
$errors[] = 'Invalid credentials.';
} elseif (!$user['verified']) {
$errors[] = 'Please verify your email before logging in.';
}


if (empty($errors)) {
// Login success
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['LAST_ACTIVITY'] = time();
// configurable timeout in seconds
$_SESSION['timeout'] = 1800; // 30 minutes default, change as needed


if ($remember) {
$token = generateToken(20);
$stmt = $pdo->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
$stmt->execute([$token, $user['id']]);
setcookie('rememberme', $user['id'] . ':' . $token, time() + (86400 * 30), '/', '', isset($_SERVER['HTTPS']), true);
}
header('Location: index.php');
exit;
}
}


include __DIR__ . '/assets/templates/header.html';
?>
<form method="post">
<input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
<label>Email <input type="email" name="email" required></label>
<label>Password <input type="password" name="password" required></label>
<label><input type="checkbox" name="remember"> Remember me</label>
<button type="submit">Login</button>
</form>


<?php include __DIR__ . '/assets/templates/footer.html'; ?>