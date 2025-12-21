<?php
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'consumer')
    header("Location: index.php");

$role = $_SESSION['role']; // manager or staff
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Manager Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>

<body>
    <nav class="navbar">
        <h2>Panel: <?php echo ucfirst($role); ?></h2>
        <div>
            <a href="profile.php" class="btn" style="background: #2e8b57;">My Profile</a>
        </div>
        <div>
            <button id="theme-toggle" class="btn">Theme</button>
            <a href="index.php" class="btn" style="background:red;">Logout</a>
        </div>
    </nav>

    <div class="container">
        <?php if ($role == 'manager'): ?>
            <div class="card">
                <h3>Pending Account / Service Approvals</h3>
                <table>
                    <tr>
                        <th>User ID</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                    <?php
                    $res = $conn->query("SELECT * FROM requests WHERE status='pending'");
                    while ($row = $res->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $row['user_id']; ?></td>
                            <td><?php echo $row['request_type']; ?></td>
                            <td><?php echo $row['request_date']; ?></td>
                            <td>
                                <a href="actions.php?approve_request=1&id=<?php echo $row['id']; ?>&uid=<?php echo $row['user_id']; ?>&type=<?php echo $row['request_type']; ?>"
                                    class="btn">Approve</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($role == 'manager'): ?>
            <div class="card">
                <h3>Pending Loan Applications</h3>
                <table>
                    <tr>
                        <th>User ID</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Justification (Accuracy Check)</th>
                        <th>Action</th>
                    </tr>
                    <?php
                    $loans = $conn->query("SELECT * FROM loans WHERE status='pending'");
                    while ($l = $loans->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $l['user_id']; ?></td>
                            <td><?php echo $l['loan_type']; ?></td>
                            <td><?php echo $l['amount']; ?></td>
                            <td><?php echo $l['justification']; ?></td>
                            <td>
                                <a href="actions.php?approve_loan=1&id=<?php echo $l['id']; ?>" class="btn">Approve</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        <?php endif; ?>

        <div class="card">
            <h3>All Registered Users</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Status</th>
                </tr>
                <?php
                $users = $conn->query("SELECT * FROM users");
                while ($u = $users->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo $u['name']; ?></td>
                        <td><?php echo $u['role']; ?></td>
                        <td><?php echo $u['status']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>

</html>