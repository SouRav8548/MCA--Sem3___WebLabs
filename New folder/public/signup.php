<?php


// TODO: verify captcha server-side (see api/captcha_verify.php) - for now assume pass


if (empty($errors)) {
    // Check exists
    $existing = find_user_by_email($email);
    if ($existing)
        $errors[] = 'Account with this email already exists.';
}


if (empty($errors)) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $token = generateToken(16);
    if (create_user($email, $hash, $full_name, $token)) {
        // Send verification email - in production use real mailer
        $verification_url = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/verify.php?token=' . urlencode($token);
        // Use mail() or an SMTP library. Here we just redirect to a success page.
        header('Location: signup.php?success=1');
        exit;
    }
}


include __DIR__ . '/assets/templates/header.html';
?>


<!-- HTML signup form -->
<form method="post" action="">
    <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
    <label>Email: <input type="email" name="email" required></label>
    <label>Full name: <input type="text" name="full_name"></label>
    <label>Password: <input type="password" name="password" required></label>
    <!-- Place reCAPTCHA widget here (site key required) -->
    <div class="g-recaptcha" data-sitekey="YOUR_RECAPTCHA_SITE_KEY"></div>
    <button type="submit">Sign up</button>
</form>


<?php include __DIR__ . '/assets/templates/footer.html'; ?>
```


> Note: `assets/templates/header.html` and footer are simple HTML wrappers. Replace reCAPTCHA site key.


---