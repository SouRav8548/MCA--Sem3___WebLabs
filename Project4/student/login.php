<?php
include '../config/db_connect.php';

if (isset($_SESSION['student_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, password, name FROM students WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // --- DEBUGGING START ---
            echo "<h3>Debugging Data:</h3>";
            echo "Typed Password: " . $password . "<br>";
            echo "Stored Hash in DB: " . $user['password'] . "<br>";
            echo "Hash Length: " . strlen($user['password']) . "<br>";

            $check = password_verify($password, $user['password']);
            echo "Match Result: " . ($check ? "TRUE (Match)" : "FALSE (No Match)") . "<br>";
            sleep(10); // Stop here so you can read the output
            // --- DEBUGGING END ---
            $_SESSION['student_id'] = $user['id'];
            $_SESSION['student_name'] = $user['name'];
            header("Location: dashboard.php");
            exit();

        } else {
            $error = "Incorrect ------------------- Password";
        }
    } else {
        $error = "Email not found";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Student Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="glass-container" style="max-width: 400px; margin-top: 10vh;">
        <h2 class="text-center">Student Login</h2>
        <?php if ($error)
            echo "<p style='color: #ff4757; text-align: center;'>$error</p>"; ?>
        <?php if (isset($_GET['msg']))
            echo "<p style='color: #2ed573; text-align: center;'>Registration Successful! Please Login.</p>"; ?>

        <form method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
        <div class="text-center mt-2">
            <a href="register.php" style="color: var(--accent-color);">Create Account</a> |
            <a href="../index.php" style="color: var(--text-color);">Home</a>
        </div>
    </div>
</body>

</html>