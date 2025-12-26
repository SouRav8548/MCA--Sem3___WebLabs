<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>TechBank - Login</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>

<body>
    <nav class="navbar">
        <h1>TechBank System</h1>
        <button id="theme-toggle" class="btn">Theme</button>
    </nav>
    <div class="container" style="max-width: 400px; margin-top: 50px;">
        <div class="card">
            <h2>Login</h2>
            <form action="actions.php" method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login" class="btn" style="width:100%">Login</button>
            </form>
            <br>
            <p>New Consumer? <a href="register.php?role=consumer">Create Account</a></p>
            <p>New Staff? <a href="register.php?role=staff">Staff Registration</a></p>
        </div>
    </div>
</body>

</html>