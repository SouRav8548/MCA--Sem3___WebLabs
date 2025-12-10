<?php
include '../config/db_connect.php';

$msg = "";
if (isset($_POST['check'])) {
    $email = $_POST['email'];
    $entered_pass = $_POST['password'];

    // 1. Check if user exists
    $stmt = $conn->prepare("SELECT * FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        $msg = "<span style='color:red'>User with this email not found in Database.</span>";
    } else {
        $stored_hash = $user['password'];
        $hash_len = strlen($stored_hash);

        $msg .= "<h3>Diagnostic Results:</h3>";
        $msg .= "<strong>1. Database Column Status:</strong><br>";

        // CHECK 1: Hash Length
        if ($hash_len < 60) {
            $msg .= "❌ <span style='color:red'>CRITICAL FAIL: Stored hash is too short ($hash_len chars).</span><br>";
            $msg .= "Your database column is STILL too small or this is an old account.<br>";
            $msg .= "Fix: Set column to VARCHAR(255) and register again.<br><br>";
        } else {
            $msg .= "✅ Hash length looks good ($hash_len chars).<br><br>";
        }

        // CHECK 2: Password Verification
        $msg .= "<strong>2. Password Verification:</strong><br>";
        if (password_verify($entered_pass, $stored_hash)) {
            $msg .= "✅ <span style='color:green'>SUCCESS! The password '$entered_pass' matches.</span><br>";
            $msg .= "If you can't login, your login.php code has a typo.";
        } else {
            $msg .= "❌ <span style='color:red'>FAIL: The password '$entered_pass' does not match the stored hash.</span><br>";
            $msg .= "Possibilities:<br>";
            $msg .= "- You made a typo during registration.<br>";
            $msg .= "- 'Caps Lock' was on.<br>";
            $msg .= "- The registration code saved the wrong variable.";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<body style="font-family: sans-serif; padding: 20px;">
    <h2>Login debugger</h2>
    <form method="POST">
        <input type="text" name="email" placeholder="Student Email" required style="padding:10px;">
        <input type="text" name="password" placeholder="Password you are trying" required style="padding:10px;">
        <button type="submit" name="check" style="padding:10px;">Check My Account</button>
    </form>
    <div style="background: #f0f0f0; padding: 20px; margin-top: 20px; border: 1px solid #ccc;">
        <?php echo $msg; ?>
    </div>
</body>

</html>