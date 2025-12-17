<?php
require_once __DIR__ . '/../config/db.php';
include 'partials/header.php';
include 'partials/nav.php';

if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user']['id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    if ($name) {
        $pdo->prepare('UPDATE users SET name = ? WHERE id = ?')->execute([$name, $userId]);
        $_SESSION['user']['name'] = $name;
        $success = 'Profile updated.';
    } else {
        $error = 'Name cannot be empty.';
    }
}

$u = $pdo->prepare('SELECT name, email, verified FROM users WHERE id = ?');
$u->execute([$userId]);
$user = $u->fetch();
?>
<div class="container" style="max-width: 600px;">
    <div class="card">
        <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 2rem;">
            <div
                style="width: 60px; height: 60px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: white; font-weight: bold;">
                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
            </div>
            <div>
                <h2 style="margin:0;"><?php echo htmlspecialchars($user['name']); ?></h2>
                <span style="color: var(--text-muted);"><?php echo htmlspecialchars($user['email']); ?></span>
            </div>
        </div>

        <?php if ($success)
            echo "<div class='success'>$success</div>"; ?>
        <?php if ($error)
            echo "<div class='error'>$error</div>"; ?>

        <form class="form" method="post">
            <div class="grid grid-2">
                <div><label>Full Name</label><input name="name" value="<?php echo htmlspecialchars($user['name']); ?>">
                </div>
                <div>
                    <label>Status</label>
                    <div
                        style="padding: 0.85rem; background: var(--input-bg); border-radius: 0.5rem; border: 1px solid var(--border);">
                        <?php echo $user['verified'] ? '<span style="color:var(--success)">Verified</span>' : '<span style="color:var(--error)">Unverified</span>'; ?>
                    </div>
                </div>
            </div>
            <div class="actions"><button class="btn" type="submit">Save Changes</button></div>
        </form>
    </div>
</div>
<?php include 'partials/footer.php'; ?>