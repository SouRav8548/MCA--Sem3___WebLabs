<?php
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'consumer')
    header("Location: index.php");

$uid = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id='$uid'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Consumer Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>

<body>
    <nav class="navbar">
        <h2>Welcome, <?php echo $user['name']; ?> (Consumer)</h2>
        <div class="card">
            <h3>Account Details</h3>
            <p><b>Account Number:</b> <?php echo $user['account_number']; ?></p>
            <h3>Current Balance (SB/CB)</h3>
            <h1 style="color: var(--accent);">$<?php echo number_format($user['balance'], 2); ?></h1>
        </div>
        <div>
            <a href="profile.php" class="btn" style="background: #2e8b57;">My Profile</a> <button id="theme-toggle"
                class="btn">Theme</button>
            <a href="index.php" class="btn" style="background:red;">Logout</a>
        </div>
        <div>
            <button id="theme-toggle" class="btn">Theme</button>
            <a href="index.php" class="btn" style="background:red;">Logout</a>
        </div>

    </nav>

    <div class="container">
        <div class="card">
            <h3>Current Balance (SB/CB)</h3>
            <h1 style="color: var(--accent);">$<?php echo number_format($user['balance'], 2); ?></h1>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="card">
                <h3>Request Services</h3>
                <form action="actions.php" method="POST">
                    <select name="service_type">
                        <option value="cheque_book">Cheque Book</option>
                        <option value="atm_card">ATM Card</option>
                    </select>
                    <button type="submit" name="request_service" class="btn">Request Service</button>
                </form>

                <h4>My Requests (Download PDF if Approved)</h4>
                <ul>
                    <?php
                    $reqs = $conn->query("SELECT * FROM requests WHERE user_id='$uid' AND request_type IN ('cheque_book', 'atm_card')");
                    while ($r = $reqs->fetch_assoc()) {
                        echo "<li>" . ucfirst(str_replace('_', ' ', $r['request_type'])) . " - <b>" . strtoupper($r['status']) . "</b>";
                        if ($r['status'] == 'approved') {
                            // Simulating PDF Download
                            echo " <button onclick='printDiv(\"certificate\")' style='font-size:10px;'>Download PDF</button>";
                        }
                        echo "</li>";
                    }
                    ?>
                </ul>
                <div id="certificate" style="display:none; border:5px solid black; padding:20px; text-align:center;">
                    <h1>OFFICIAL BANK DOCUMENT</h1>
                    <p>This certifies that the request for services has been approved.</p>
                    <p>Issued by TechBank Manager.</p>
                </div>
            </div>

            <div class="card">
                <h3>Apply for Loan</h3>
                <form action="actions.php" method="POST">
                    <select name="loan_type">
                        <option value="Home Loan">Home Loan</option>
                        <option value="Car Loan">Car Loan</option>
                        <option value="Education Loan">Education Loan</option>
                        <option value="Gold Loan">Gold Loan</option>
                    </select>
                    <input type="number" name="amount" placeholder="Amount Required" required>
                    <label>Genuine Question: Why do you need this loan & repayment plan?</label>
                    <textarea name="justification" rows="3" required></textarea>
                    <button type="submit" name="apply_loan" class="btn">Submit Application</button>
                </form>
            </div>
        </div>

        <div class="card">
            <h3>Investments (FD / RD)</h3>
            <p>Contact branch to open new FD/RD accounts.</p>
        </div>
    </div>
</body>

</html>