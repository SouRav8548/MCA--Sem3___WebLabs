<?php
include '../config/db_connect.php';
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $roll_no = $_POST['roll_no'];
    $email = $_POST['email'];
    $pass = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    // Simple file upload for profile pic
    $photo = "default.png";
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = "user_" . time() . "." . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], "../assets/uploads/" . $filename);
        $photo = $filename;
    }

    $stmt = $conn->prepare("INSERT INTO students (name, email, password, roll_no, photo) VALUES (?, ?, ?, ?, ?)");

    // --- DEBUGGING START ---
    if ($stmt === false) {
        die("<h3>Database Error:</h3> " . $conn->error);
    }
    // --- DEBUGGING END ---

    $stmt->bind_param("sssss", $name, $email, $pass, $roll_no, $photo);

    if ($stmt->execute()) {
        header("Location: login.php?msg=registered");
    } else {
        // Show the ACTUAL database error
        $error = "Database Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Student Registration</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="glass-container" style="max-width: 500px;">
        <h2 class="text-center">Student Registration</h2>
        <?php if (isset($error))
            echo "<p style='color:red; text-align:center;'>$error</p>"; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group"><input type="text" name="name" placeholder="Full Name" required></div>
            <div class="form-group"><input type="text" name="roll_no" placeholder="Roll Number" required></div>
            <div class="form-group"><input type="email" name="email" placeholder="Email Address" required></div>
            <div class="form-group"><input type="password" name="password" placeholder="Password" required></div>
            <div class="form-group">
                <label>Profile Photo</label>
                <input type="file" name="photo" accept="image/*" style="padding: 9px;">
            </div>
            <button type="submit" name="register" class="btn btn-primary" style="width:100%">Register</button>
        </form>
        <p class="text-center mt-2">Already have an account? <a href="login.php"
                style="color:var(--accent-color)">Login</a></p>
    </div>
</body>

</html>