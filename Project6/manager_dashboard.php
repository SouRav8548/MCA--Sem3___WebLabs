<?php
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] == 'consumer')
    header("Location: index.php");

$role = $_SESSION['role'];
$uid = $_SESSION['user_id'];

// Get Current User Info (for Staff Balance)
$me = $conn->query("SELECT * FROM users WHERE id='$uid'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title><?php echo ucfirst($role); ?> Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>

<body>
    <nav class="navbar">
        <h2>Panel: <?php echo ucfirst($role); ?></h2>
        <div>
            <a href="profile.php" class="btn" style="background: #2e8b57;">Profile</a>
            <button id="theme-toggle" class="btn">Theme</button>
            <a href="index.php" class="btn" style="background:red;">Logout</a>
        </div>
    </nav>

    <div class="container">

        <!-- ================= STAFF VIEW ================= -->
        <?php if ($role == 'staff'): ?>
            <!-- Staff Balance Card -->
            <div class="card"
                style="border-left: 5px solid var(--accent); background: linear-gradient(to right, #2c3e50, #000);">
                <h3 style="color: #fff;">My Operating Balance (Float)</h3>
                <h1 style="color: var(--text-primary); font-size: 3rem;">₹<?php echo number_format($me['balance'], 2); ?>
                </h1>
                <p style="color: #ccc;">You can process deposits up to this amount.</p>
            </div>

            <!-- Transaction Processor -->
            <div class="card">
                <h3>Process Customer Transaction</h3>
                <form action="actions.php" method="POST" class="grid-2"
                    style="grid-template-columns: 1fr 1fr 1fr; align-items: end;">
                    <div>
                        <label>Customer A/C Number</label>
                        <input type="number" name="customer_acc" required>
                    </div>
                    <div>
                        <label>Amount</label>
                        <input type="number" name="amount" placeholder="₹" required>
                    </div>
                    <div>
                        <label>Transaction Type</label>
                        <select name="txn_type" required>
                            <option value="deposit">Deposit (In)</option>
                            <option value="withdrawal">Withdrawal (Out)</option>
                        </select>
                    </div>
                    <button type="submit" name="process_staff_transaction" class="btn"
                        style="grid-column: span 3; margin-top: 10px;">Process Transaction</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- ================= MANAGER VIEW ================= -->
        <?php if ($role == 'manager'): ?>
            <!-- Staff Management Section -->
            <div class="card" style="border-left: 5px solid #d2691e;">
                <h3>Staff Fund Management</h3>
                <div style="overflow-x:auto;">
                    <table>
                        <tr>
                            <th>Staff Name</th>
                            <th>Current Float</th>
                            <th>Manage Funds</th>
                        </tr>
                        <?php
                        $staff_list = $conn->query("SELECT * FROM users WHERE role='staff'");
                        while ($s = $staff_list->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?php echo $s['name']; ?></td>
                                <td><b>₹<?php echo number_format($s['balance'], 2); ?></b></td>
                                <td>
                                    <form action="actions.php" method="POST" style="display:flex; gap:5px;">
                                        <input type="hidden" name="staff_id" value="<?php echo $s['id']; ?>">
                                        <input type="number" name="amount" placeholder="Amount"
                                            style="width:100px; padding:5px; margin:0;" required>
                                        <select name="action" style="padding:5px; margin:0;">
                                            <option value="load">Add (Load)</option>
                                            <option value="collect">Remove (Collect)</option>
                                        </select>
                                        <button type="submit" name="manage_staff_funds" class="btn"
                                            style="font-size:0.8rem; padding: 5px 10px;">Go</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            </div>

            <!-- Global User List -->
            <div class="card">
                <h3>Master User List</h3>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Role</th>
                        <th>Name</th>
                        <th>A/C No</th>
                        <th>Balance</th>
                        <th>Status</th>
                    </tr>
                    <?php
                    $all_users = $conn->query("SELECT * FROM users ORDER BY role DESC");
                    while ($u = $all_users->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $u['id']; ?></td>
                            <td><?php echo ucfirst($u['role']); ?></td>
                            <td><?php echo $u['name']; ?></td>
                            <td><?php echo $u['account_number'] ? $u['account_number'] : '-'; ?></td>
                            <td>₹<?php echo number_format($u['balance'], 2); ?></td>
                            <td><?php echo ucfirst($u['status']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <!-- Global Transaction Log -->
            <div class="card">
                <h3>Global Transaction History</h3>
                <table>
                    <tr>
                        <th>Time</th>
                        <th>Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Amount</th>
                    </tr>
                    <?php
                    $log = $conn->query("SELECT t.*, s.name as s_name, r.name as r_name FROM transactions t LEFT JOIN users s ON t.sender_id=s.id LEFT JOIN users r ON t.receiver_id=r.id ORDER BY t.created_at DESC LIMIT 10");
                    while ($l = $log->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $l['created_at']; ?></td>
                            <td><?php echo strtoupper(str_replace('_', ' ', $l['type'])); ?></td>
                            <td><?php echo $l['s_name'] ? $l['s_name'] : 'System'; ?></td>
                            <td><?php echo $l['r_name'] ? $l['r_name'] : 'System'; ?></td>
                            <td>₹<?php echo number_format($l['amount'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>

            <!-- Loan Requests -->
            <div class="card">
                <h3>Pending Loans</h3>
                <table>
                    <tr>
                        <th>User</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Reason</th>
                        <th>Action</th>
                    </tr>
                    <?php
                    $loans = $conn->query("SELECT * FROM loans WHERE status='pending'");
                    while ($l = $loans->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?php echo $l['user_id']; ?></td>
                            <td><?php echo $l['loan_type']; ?></td>
                            <td>₹<?php echo $l['amount']; ?></td>
                            <td><?php echo $l['justification']; ?></td>
                            <td>
                                <a href="actions.php?approve_loan=1&id=<?php echo $l['id']; ?>" class="btn"
                                    style="background:green;">Approve</a>
                                <a href="actions.php?reject_loan=1&id=<?php echo $l['id']; ?>" class="btn"
                                    style="background:red;">Reject</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            </div>
        <?php endif; ?>

        <!-- Shared: Pending Approvals (Account/Cheque) -->
        <div class="card">
            <h3>Pending Account/Service Requests</h3>
            <table>
                <tr>
                    <th>User ID</th>
                    <th>Type</th>
                    <th>Action</th>
                </tr>
                <?php
                $reqs = $conn->query("SELECT * FROM requests WHERE status='pending'");
                while ($r = $reqs->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?php echo $r['user_id']; ?></td>
                        <td><?php echo $r['request_type']; ?></td>
                        <td>
                            <a href="actions.php?approve_request=1&id=<?php echo $r['id']; ?>&uid=<?php echo $r['user_id']; ?>&type=<?php echo $r['request_type']; ?>"
                                class="btn" style="background:green;">Approve</a>
                            <a href="actions.php?reject_request=1&id=<?php echo $r['id']; ?>&uid=<?php echo $r['user_id']; ?>&type=<?php echo $r['request_type']; ?>"
                                class="btn" style="background:red;">Reject</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

    </div>
</body>

</html>