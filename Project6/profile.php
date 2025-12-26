<?php
include 'db.php';
if (!isset($_SESSION['user_id']))
    header("Location: index.php");

$uid = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id='$uid'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>My Profile</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>

<body>
    <nav class="navbar">
        <h2>My Profile</h2>
        <?php $dash = ($_SESSION['role'] == 'consumer') ? 'consumer_dashboard.php' : 'manager_dashboard.php'; ?>
        <a href="<?php echo $dash; ?>" class="btn">Back to Dashboard</a>
    </nav>

    <div class="container">
        <div class="card">
            <div
                style="display: flex; gap: 20px; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 20px;">
                <img src="<?php echo ($user['profile_pic'] && $user['profile_pic'] != 'default.png') ? 'uploads/' . $user['profile_pic'] : 'https://via.placeholder.com/150'; ?>"
                    style="width:150px; height:150px; border-radius:50%; object-fit:cover; border:3px solid var(--accent);">
                <div>
                    <h1><?php echo $user['name']; ?></h1>
                    <p>Role: <?php echo ucfirst($user['role']); ?></p>
                    <p>Email: <?php echo $user['email']; ?></p>
                </div>
            </div>

            <form action="actions.php" method="POST" enctype="multipart/form-data" style="margin-top:20px;">
                <h3>Edit Details</h3>
                <label>Change Photo</label>
                <input type="file" name="profile_pic">

                <label>Phone</label>
                <input type="text" name="phone" value="<?php echo $user['phone']; ?>" required>

                <label>Address</label>
                <textarea name="address" rows="2" required><?php echo $user['address']; ?></textarea>

                <label>Qualification</label>
                <input type="text" name="qualification" value="<?php echo $user['qualification']; ?>" required>

                <button type="submit" name="update_profile" class="btn">Save Changes</button>
            </form>
        </div>
    </div>
</body>

</html>