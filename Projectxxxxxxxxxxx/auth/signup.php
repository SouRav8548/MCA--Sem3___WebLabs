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
    <!-- <link rel="stylesheet" href="style.css" /> -->
    <style>
        /* =========================================
   1. CSS Variables & Theme Setup
   ========================================= */
        :root {
            /* Core Brand Colors (Green Palette) */
            --primary-color: #2e7d32;
            /* Forest Green */
            --primary-hover: #1b5e20;
            /* Darker Green for hover */
            --secondary-color: #81c784;
            /* Light Green for accents */

            /* Functional Colors */
            --error-color: #d32f2f;
            /* Red for errors */
            --error-bg: #ffebee;
            /* Light red background */

            /* Light Mode Defaults (White/Green Theme) */
            --bg-color: #ffffff;
            --container-bg: #ffffff;
            /* White background for the form container */
            --text-main: #2c3e50;
            --text-muted: #546e7a;
            --border-color: #e0e0e0;
            --input-bg: #f9f9f9;
            --shadow-color: rgba(0, 0, 0, 0.1);
        }

        /* System Dark Mode Support (Optional) */
        @media (prefers-color-scheme: dark) {
            :root {
                --bg-color: #121212;
                --container-bg: #1e1e1e;
                --text-main: #e0e0e0;
                --text-muted: #b0bec5;
                --border-color: #333333;
                --input-bg: #2c2c2c;
                --shadow-color: rgba(0, 0, 0, 0.5);

                --primary-color: #4caf50;
                --primary-hover: #66bb6a;
                --error-color: #ef9a9a;
                --error-bg: #2c0b0e;
            }
        }

        /* =========================================
   2. Global Reset & Typography
   ========================================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 1rem;
        }

        /* =========================================
   3. Form Container (Login & Signup)
   ========================================= */
        .container {
            background-color: var(--container-bg);
            padding: 2.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 20px var(--shadow-color);
            width: 100%;
            max-width: 400px;
            text-align: center;
            border: 1px solid var(--border-color);
        }

        .container h2 {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-size: 2rem;
            font-weight: 700;
        }

        /* =========================================
   4. Form Elements
   ========================================= */
        form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        /* Updated to include text input for Username */
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background-color: var(--input-bg);
            color: var(--text-main);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.1);
            background-color: var(--container-bg);
        }

        button[type="submit"] {
            background-color: var(--primary-color);
            color: #ffffff;
            border: none;
            padding: 0.8rem;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        button[type="submit"]:hover {
            background-color: var(--primary-hover);
        }

        button[type="submit"]:active {
            transform: scale(0.98);
        }

        /* =========================================
   5. Error Messages & Links
   ========================================= */
        .error {
            background-color: var(--error-bg);
            color: var(--error-color);
            padding: 0.75rem;
            border-radius: 4px;
            border: 1px solid var(--error-color);
            margin-bottom: 1rem;
            font-size: 0.9rem;
            text-align: left;
        }

        .container p {
            font-size: 0.95rem;
            color: var(--text-muted);
        }

        .container p a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }

        .container p a:hover {
            text-decoration: underline;
            color: var(--primary-hover);
        }
    </style>
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