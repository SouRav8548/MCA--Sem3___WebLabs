<?php
// Ensure database connection is loaded correctly
require_once __DIR__ . '/../config/db.php';
include 'partials/header.php';
include 'partials/nav.php';

$msg = '';
$isSuccess = false;

// 1. Check if parameters exist
if (isset($_GET['email']) && isset($_GET['token'])) {

    // 2. Clean the inputs
    $email = trim($_GET['email']);
    $token = trim($_GET['token']);

    if (!empty($email) && !empty($token)) {
        try {
            // 3. Find user by EMAIL only first (to differentiate "User not found" vs "Token Wrong")
            $stmt = $pdo->prepare("SELECT id, verified, verification_token FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // 4. Check status
                if ($user['verified'] == 1) {
                    // Already Verified
                    $isSuccess = true;
                    $msg = "Your account is already active! You can log in now.";
                }
                // 5. Check Token Match
                // We use strict comparison (===) to ensure exact match
                elseif ($user['verification_token'] === $token) {
                    // SUCCESS: Update DB
                    $updateStmt = $pdo->prepare("UPDATE users SET verified = 1, verification_token = NULL WHERE id = ?");
                    $updateStmt->execute([$user['id']]);

                    $isSuccess = true;
                    $msg = "Verification successful! Your account is now active.";
                } else {
                    // Email found, but token didn't match
                    $msg = "Invalid verification link. The token does not match.";
                }
            } else {
                $msg = "No account found with that email address.";
            }
        } catch (Exception $e) {
            $msg = "Database error: " . $e->getMessage();
        }
    } else {
        $msg = "Invalid link: Email or Token is empty.";
    }
} else {
    $msg = "Missing verification parameters.";
}
?>

<div class="container" style="max-width: 500px; margin-top: 4rem;">
    <div class="card" style="text-align: center;">
        <h2 class="card-title">Account Verification</h2>

        <!-- Status Message -->
        <div class="<?php echo $isSuccess ? 'success' : 'error'; ?>" style="margin: 1.5rem 0; padding: 1rem;">
            <?php echo htmlspecialchars($msg); ?>
        </div>

        <!-- Buttons -->
        <?php if ($isSuccess): ?>
            <a href="login.php" class="btn" style="width: 100%; display: block;">Login Now</a>
        <?php else: ?>
            <p style="color: var(--text-muted); margin-bottom: 1rem;">
                If you are having trouble, please try signing up again.
            </p>
            <a href="register.php" class="btn accent" style="width: 100%; display: block;">Back to Sign Up</a>
        <?php endif; ?>
    </div>
</div>

<?php include 'partials/footer.php'; ?>