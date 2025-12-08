<?php
// admin_profile.php
require 'config.php';
require_admin(); // only admins can access

$adminId = $_SESSION['user']['id'];
$errors = [];
$success = '';

// Fetch current admin data
$stmt = $pdo->prepare('SELECT id, name, email, phone, dob FROM users WHERE id = ? AND role = "admin"');
$stmt->execute([$adminId]);
$admin = $stmt->fetch();

if (!$admin) {
    die('Admin not found.');
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dob = trim($_POST['dob'] ?? '');

    if ($name === '') {
        $errors[] = 'Full name is required.';
    }

    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if ($phone !== '' && !preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) {
        $errors[] = 'Phone number format is invalid.';
    }

    if ($dob !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
        $errors[] = 'Date of birth must be in YYYY-MM-DD format.';
    }

    // unique email among all users except this admin
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
        $stmt->execute([$email, $adminId]);
        if ($stmt->fetch()) {
            $errors[] = 'This email address is already in use.';
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, phone = ?, dob = ? WHERE id = ?');
        $stmt->execute([$name, $email, $phone ?: null, $dob ?: null, $adminId]);

        $admin['name'] = $name;
        $admin['email'] = $email;
        $admin['phone'] = $phone;
        $admin['dob'] = $dob;

        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;

        $success = 'Admin profile updated successfully.';
    }
}

// Optional: simple admin stats (example: number of questions)
$stmt = $pdo->query('SELECT COUNT(*) AS total_q FROM questions');
$stats = $stmt->fetch();

include 'header.php';
?>

<style>
    .admin-profile-header {
        margin-bottom: 1rem;
    }

    .admin-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 0.5rem;
    }

    .admin-stat-card {
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        background: rgba(78, 159, 255, 0.08);
        border: 1px solid rgba(78, 159, 255, 0.4);
        min-width: 180px;
    }

    .admin-stat-label {
        font-size: 0.85rem;
        color: rgba(244, 245, 251, 0.75);
    }

    .admin-stat-value {
        font-size: 1.2rem;
        font-weight: 600;
    }
</style>

<div class="card">
    <div class="admin-profile-header">
        <h1>Admin Profile</h1>
        <p>Update your admin account details and view basic system stats.</p>
    </div>

    <?php if ($success): ?>
        <p class="correct-answer"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    <?php if ($errors): ?>
        <ul>
            <?php foreach ($errors as $e): ?>
                <li class="incorrect-answer"><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- Basic admin stats -->
    <div class="admin-stats">
        <div class="admin-stat-card">
            <div class="admin-stat-label">Total Questions</div>
            <div class="admin-stat-value">
                <?php echo htmlspecialchars($stats['total_q'] ?? 0); ?>
            </div>
        </div>
    </div>

    <h2 style="margin-top:1.5rem;">Edit Admin Information</h2>
    <form method="post" novalidate>
        <div class="form-group">
            <label for="name">Full name</label>
            <input id="name" name="name" type="text" required value="<?php echo htmlspecialchars($admin['name']); ?>">
        </div>

        <div class="form-group">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" required
                value="<?php echo htmlspecialchars($admin['email']); ?>">
        </div>

        <div class="form-group">
            <label for="phone">Phone number</label>
            <input id="phone" name="phone" type="text" value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>"
                placeholder="+91 *x*x*x*x*x">
        </div>

        <div class="form-group">
            <label for="dob">Date of birth</label>
            <input id="dob" name="dob" type="date" value="<?php echo htmlspecialchars($admin['dob'] ?? ''); ?>">
        </div>

        <button type="submit" class="btn btn-primary">Save changes</button>
    </form>
</div>

<?php include 'footer.php'; ?>