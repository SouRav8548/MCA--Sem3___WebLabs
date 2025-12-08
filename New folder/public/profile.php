<?php
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/functions.php';
require_once __DIR__ . '/inc/auth.php';


session_start();
if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT id,email,full_name FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST'){
if (!csrf_check($_POST['csrf'] ?? '')) $errors[]='Bad request';
$name = sanitize($_POST['full_name'] ?? '');
// update
if (empty($errors)){
$up = $pdo->prepare('UPDATE users SET full_name = ? WHERE id = ?');
$up->execute([$name, $user_id]);
header('Location: profile.php?updated=1'); exit;
}
}
include __DIR__ . '/assets/templates/header.html';
?>
<h1>Profile</h1>
<form method="post">
<input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
<label>Email: <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled></label>
<label>Full name: <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>"></label>
<button type="submit">Save</button>
</form>
<?php include __DIR__ . '/assets/templates/footer.html'; ?>