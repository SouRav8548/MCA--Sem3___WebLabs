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
        <div>
            <?php
            $dashboard = ($_SESSION['role'] == 'consumer') ? 'consumer_dashboard.php' : 'manager_dashboard.php';
            ?>
            <a href="<?php echo $dashboard; ?>" class="btn">Back to Dashboard</a>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <div
                style="display: flex; align-items: center; gap: 20px; border-bottom: 1px solid var(--border); padding-bottom: 20px; margin-bottom: 20px;">
                <div>
                    <?php
                    $img = !empty($user['profile_pic']) && $user['profile_pic'] != 'default.png' ? 'uploads/' . $user['profile_pic'] : 'https://via.placeholder.com/150';
                    ?>
                    <img src="<?php echo $img; ?>"
                        style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 3px solid var(--accent);">
                </div>
                <div>
                    <h1><?php echo $user['name']; ?></h1>
                    <p style="color: var(--text-secondary);">Role: <?php echo ucfirst($user['role']); ?></p>
                    <?php if ($user['role'] == 'consumer'): ?>
                        <h3 style="background: var(--accent); color: #fff; padding: 5px 10px; display: inline-block;">
                            Account No: <?php echo $user['account_number']; ?></h3>
                    <?php endif; ?>
                </div>
            </div>

            <form action="actions.php" method="POST" enctype="multipart/form-data">
                <h3>Edit Details</h3>

                <label>Upload New Photo:</label>
                <input type="file" name="profile_pic" accept="image/*">

                <label>Mobile Number:</label>
                <input type="text" name="phone" value="<?php echo $user['phone']; ?>" required>

                <label>Address:</label>
                <textarea name="address" rows="2" required><?php echo $user['address']; ?></textarea>

                <label>Qualification:</label>
                <input type="text" name="qualification" value="<?php echo $user['qualification']; ?>" required>

                <label>Email (Cannot Change):</label>
                <input type="text" value="<?php echo $user['email']; ?>" disabled style="opacity: 0.6;">

                <label>PAN Number (Cannot Change):</label>
                <input type="text" value="<?php echo $user['pan_number']; ?>" disabled style="opacity: 0.6;">

                <button type="submit" name="update_profile" class="btn">Save Changes</button>
            </form>
        </div>
    </div>
</body>

</html>