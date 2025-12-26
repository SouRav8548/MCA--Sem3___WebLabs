<?php
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'consumer')
    header("Location: index.php");

$uid = $_SESSION['user_id'];

// Fetch User Data
$user_query = $conn->query("SELECT * FROM users WHERE id='$uid'");
if (!$user_query) {
    die("Database Error: " . $conn->error);
}
$user = $user_query->fetch_assoc();

// FD Total Calculation
// We use a try-catch style logic by checking if query returns object
$fd_total = 0.00;
$fd_query = "SELECT SUM(amount) as total FROM deposits WHERE user_id='$uid' AND type='FD'";
$fd_res = $conn->query($fd_query);

if ($fd_res) {
    $row = $fd_res->fetch_assoc();
    $fd_total = $row['total'] ?? 0.00;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Consumer Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
    <style>
        .history-table td {
            padding: 8px;
            font-size: 0.9rem;
            border-bottom: 1px solid #333;
        }

        .history-table th {
            padding: 8px;
            font-size: 0.8rem;
            text-align: left;
            color: #888;
        }

        .credit {
            color: #2e8b57;
            font-weight: bold;
        }

        .debit {
            color: #b22222;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <h2>Welcome, <?php echo $user['name']; ?></h2>
        <div>
            <a href="profile.php" class="btn" style="background: #2e8b57;">Profile</a>
            <button id="theme-toggle" class="btn">Theme</button>
            <a href="index.php" class="btn" style="background:red;">Logout</a>
        </div>
    </nav>

    <div class="container">

        <!-- ROW 1: Balances -->
        <div class="grid-2">
            <div class="card">
                <h3>Savings Balance</h3>
                <h1 style="color: var(--accent);">₹<?php echo number_format($user['balance'], 2); ?></h1>
                <p>A/C No: <b><?php echo $user['account_number']; ?></b></p>
            </div>
            <div class="card">
                <h3>FD Balance</h3>
                <h1 style="color: var(--success);">₹<?php echo number_format($fd_total, 2); ?></h1>
                <form action="actions.php" method="POST" style="margin-top:10px; display:flex; gap:5px;">
                    <input type="number" name="amount" placeholder="Amount (₹)" required style="width:70%;">
                    <button type="submit" name="create_fd" class="btn">Create FD</button>
                </form>
            </div>
        </div>

        <!-- ROW 2: Transfer & Transaction History -->
        <div class="grid-2">

            <!-- LEFT: Transfer Money -->
            <div class="card">
                <h3>Transfer Money</h3>
                <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 15px;">
                    Secure transfer.
                </p>
                <form action="actions.php" method="POST" style="display:flex; flex-direction:column; gap:15px;">
                    <div>
                        <label>Receiver Account Number</label>
                        <input type="number" name="receiver_acc" placeholder="e.g. 1029384756" required>
                    </div>
                    <div>
                        <label>Amount (₹)</label>
                        <input type="number" name="amount" placeholder="₹ 0.00" required>
                    </div>
                    <button type="submit" name="transfer_money" class="btn" style="width: 100%;">Send Money</button>
                </form>
            </div>

            <!-- RIGHT: Transaction History -->
            <div class="card" style="overflow-x: auto;">
                <h3>Recent Transactions</h3>
                <table class="history-table" style="width:100%; border-collapse: collapse;">
                    <tr>
                        <th>Date</th>
                        <th>Details</th>
                        <th>Amount</th>
                    </tr>
                    <?php
                    // Fetch recent 5 transactions
                    $hist_sql = "SELECT t.*, s.name as sender_name, r.name as receiver_name 
                                 FROM transactions t 
                                 LEFT JOIN users s ON t.sender_id = s.id 
                                 LEFT JOIN users r ON t.receiver_id = r.id 
                                 WHERE t.sender_id='$uid' OR t.receiver_id='$uid' 
                                 ORDER BY t.created_at DESC LIMIT 5";

                    $hist_res = $conn->query($hist_sql);

                    if ($hist_res && $hist_res->num_rows > 0) {
                        while ($row = $hist_res->fetch_assoc()) {
                            $is_sender = ($row['sender_id'] == $uid);
                            $date = date('d M', strtotime($row['created_at']));

                            // Determine display text and color
                            if ($row['type'] == 'cash_deposit') {
                                $desc = "Cash Deposit";
                                $class = "credit";
                                $sign = "+";
                            } elseif ($is_sender) {
                                $desc = "To: " . ($row['receiver_name'] ?? 'Unknown');
                                $class = "debit";
                                $sign = "-";
                            } else {
                                $desc = "From: " . ($row['sender_name'] ?? 'Unknown');
                                $class = "credit";
                                $sign = "+";
                            }

                            echo "<tr>
                                    <td>$date</td>
                                    <td>$desc</td>
                                    <td class='$class'>$sign ₹" . number_format($row['amount'], 2) . "</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3' style='text-align:center; padding:10px;'>No transactions yet</td></tr>";
                    }
                    ?>
                </table>
            </div>
        </div>

        <!-- ROW 3: Services -->
        <div class="grid-2">
            <div class="card">
                <h3>Request Instruments</h3>
                <form action="actions.php" method="POST">
                    <select name="service_type">
                        <option value="cheque_book">Cheque Book</option>
                        <option value="atm_card">ATM Card</option>
                    </select>
                    <button type="submit" name="request_service" class="btn" style="margin-top:10px;">Request</button>
                </form>

                <h4 style="margin-top:15px; border-top:1px solid #333; padding-top:10px;">Approved Downloads</h4>
                <ul style="padding-left: 20px;">
                    <?php
                    $reqs = $conn->query("SELECT * FROM requests WHERE user_id='$uid' AND status='approved' AND request_type IN ('cheque_book', 'atm_card')");
                    if ($reqs) {
                        while ($r = $reqs->fetch_assoc()) {
                            $link = ($r['request_type'] == 'cheque_book') ? 'cheque' : 'atm';
                            echo "<li style='margin:5px 0;'>" . ucfirst(str_replace('_', ' ', $r['request_type'])) . " 
                                  <a href='view_instruments.php?type=$link' target='_blank' class='btn' style='font-size:0.7rem; padding:2px 5px; margin-left:5px;'>Print</a></li>";
                        }
                    }
                    ?>
                </ul>
            </div>

            <div class="card">
                <h3>Apply Loan</h3>
                <form method="POST" action="actions.php">
                    <select name="loan_type">
                        <option value="Home">Home Loan</option>
                        <option value="Car">Car Loan</option>
                        <option value="Personal">Personal Loan</option>
                        <option value="Device">Device Loan</option>
                        <option value="Gold">Gold Loan</option>
                        <option value="Education">Education Loan</option>
                    </select>
                    <input type="number" name="amount" placeholder="Amount (₹)" required style="margin-top:10px;">
                    <textarea name="justification" placeholder="Reason for loan"
                        style="width:100%; margin-top:10px; padding:10px; height:60px;"></textarea>

                    <button type="submit" name="apply_loan" class="btn" style="margin-top:10px; width:100%;">Apply
                        Now</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>