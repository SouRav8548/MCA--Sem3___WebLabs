<?php
include '../config/db_connect.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // For production, use password_verify(). For this demo setup compatibility, checking plain text or hash.
    // Assuming you inserted the user 'admin' and pass 'admin123'
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_id'] = 1; // dummy id
        $_SESSION['admin_name'] = $username;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid Credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="glass-container" style="max-width: 400px; margin-top: 10vh;">
        <h2 class="text-center">Admin Portal</h2>
        <?php if ($error)
            echo "<p style='color: #ff4757; text-align: center;'>$error</p>"; ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
        <div class="text-center mt-2">
            <a href="../index.php" style="color: var(--text-color); font-size: 0.8rem;">Back to Home</a>
        </div>
    </div>
    <script src="../assets/js/script.js"></script>
</body>

</html>