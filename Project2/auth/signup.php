<?php
session_start();
require_once '../config/db.php';
require_once '../config/session.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be 3+ characters.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $email, $hashed_password])) {
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['username'] = $username;
            header('Location: ../pages/dashboard.php');
            exit;
        } else {
            $error = "Username or email already exists.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sign Up</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
</head>

<body>
    <div class="container">
        <h2>Sign Up</h2>
        <?php if (isset($error))
            echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required minlength="3" />
            <input type="email" name="email" placeholder="Email" required />
            <input type="password" name="password" placeholder="Password (8+ chars)" required minlength="8" />
            <button type="submit">Sign Up</button>
        </form>
        <p><a href="login.php">Already have an account? Login</a></p>
    </div>
</body>

</html>