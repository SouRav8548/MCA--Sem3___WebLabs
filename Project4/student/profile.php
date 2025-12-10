<?php
include '../config/db_connect.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['student_id'];
$msg = "";

// Update Logic
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Handle Photo Update
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = "user_" . time() . "." . $ext;
        move_uploaded_file($_FILES['photo']['tmp_name'], "../assets/uploads/" . $filename);
        $sql = "UPDATE students SET name=?, phone=?, address=?, photo=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $phone, $address, $filename, $id);
    } else {
        $sql = "UPDATE students SET name=?, phone=?, address=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $phone, $address, $id);
    }

    if ($stmt->execute()) {
        $msg = "Profile Updated Successfully!";
    } else {
        $msg = "Update Failed!";
    }
}

// Fetch current data
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Edit Profile</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <nav class="navbar">
        <div class="brand">My Profile</div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="glass-container">
        <h2 class="text-center">Edit Personal Information</h2>
        <?php if ($msg)
            echo "<p style='color: #2ed573; text-align: center;'>$msg</p>"; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="profile-pic-wrapper">
                <img src="../assets/uploads/<?php echo htmlspecialchars($user['photo']); ?>" alt="Profile">
            </div>

            <div class="form-group">
                <label>Change Photo</label>
                <input type="file" name="photo" accept="image/*" style="padding: 9px;">
            </div>

            <div class="flex-grid">
                <div class="flex-col">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>
                <div class="flex-col">
                    <label>Roll Number (Read Only)</label>
                    <input type="text" value="<?php echo htmlspecialchars($user['roll_no']); ?>" disabled
                        style="opacity: 0.5;">
                </div>
            </div>

            <div class="flex-grid">
                <div class="flex-col">
                    <label>Phone</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
                <div class="flex-col">
                    <label>Email (Read Only)</label>
                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled
                        style="opacity: 0.5;">
                </div>
            </div>

            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
            </div>

            <button type="submit" name="update_profile" class="btn btn-primary" style="width: 100%;">Update
                Profile</button>
        </form>
    </div>
</body>

</html>