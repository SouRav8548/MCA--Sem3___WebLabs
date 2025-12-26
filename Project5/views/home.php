<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MVC Factorial Calculator</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <nav class="navbar">
        <div class="logo">MVC Calculator</div>
        <div class="nav-links">
            <button id="theme-toggle">🌙 Dark Mode</button>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h1>Factorial Calculator</h1>
            <p>Enter a non-negative integer to calculate its factorial.</p>

            <form method="POST" action="index.php">
                <div class="form-group">
                    <input type="number" name="number" placeholder="Enter number (e.g., 5)" required
                        value="<?php echo isset($input) ? $input : ''; ?>">
                </div>
                <button type="submit" class="btn-submit">Calculate</button>
            </form>

            <?php if ($result !== ''): ?>
                <div class="result-box">
                    <strong>Result:</strong>
                    <span><?php echo $result; ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="js/app.js"></script>
</body>

</html>