<?php
include 'db.php';
if (!isset($_SESSION['user_id']))
    header("Location: index.php");

$type = $_GET['type'] ?? 'cheque';
$uid = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id='$uid'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Print Instrument</title>
    <style>
        body {
            background: #ccc;
            font-family: 'Courier New', monospace;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .btn {
            padding: 10px 20px;
            background: #333;
            color: white;
            cursor: pointer;
            margin-bottom: 20px;
            border: none;
        }

        /* Cheque: 19:6 Ratio */
        .cheque {
            width: 950px;
            height: 300px;
            background: #fdfbf7;
            border: 1px solid #000;
            padding: 20px;
            position: relative;
            background-image: repeating-linear-gradient(45deg, #f0f0f0 25%, transparent 25%, transparent 75%, #f0f0f0 75%, #f0f0f0), repeating-linear-gradient(45deg, #f0f0f0 25%, #fdfbf7 25%, #fdfbf7 75%, #f0f0f0 75%, #f0f0f0);
            background-position: 0 0, 10px 10px;
            background-size: 20px 20px;
        }

        .cheque-header {
            display: flex;
            justify-content: space-between;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .cheque-logo {
            font-size: 24px;
            font-weight: bold;
            color: #000080;
        }

        .cheque-date {
            border: 1px solid #000;
            padding: 5px;
            letter-spacing: 5px;
        }

        .cheque-amount-box {
            border: 2px solid #000;
            padding: 10px;
            width: 200px;
            text-align: right;
            position: absolute;
            right: 20px;
            top: 120px;
            background: #fff;
            font-size: 18px;
        }

        .cheque-sign {
            position: absolute;
            bottom: 20px;
            right: 20px;
            text-align: center;
            width: 200px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        .micr {
            font-family: 'OCR A Std', monospace;
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            letter-spacing: 3px;
        }

        /* ATM: 4:2.5 Ratio */
        .atm-card {
            width: 425px;
            height: 270px;
            background: linear-gradient(135deg, #000080, #1a1a1a);
            border-radius: 15px;
            color: #fff;
            padding: 25px;
            position: relative;
            font-family: 'Arial', sans-serif;
        }

        .chip {
            width: 50px;
            height: 35px;
            background: #d4af37;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .card-number {
            font-size: 28px;
            letter-spacing: 2px;
            text-shadow: 1px 1px 2px #000;
            margin-bottom: 15px;
            font-family: 'Courier New', monospace;
        }

        .card-expiry {
            position: absolute;
            bottom: 25px;
            right: 25px;
            text-align: right;
        }

        @media print {
            .btn {
                display: none;
            }

            body {
                background: white;
            }
        }
    </style>
</head>

<body>
    <button onclick="window.print()" class="btn">Print / Save as PDF</button>

    <?php if ($type == 'cheque'): ?>
        <div class="cheque" id="printableArea">
            <div class="cheque-header">
                <div class="cheque-logo">TechBank Systems</div>
                <div class="cheque-date">DD MM YYYY</div>
            </div>
            <div style="font-size: 18px; margin-bottom: 15px;">Pay
                ___________________________________________________________</div>
            <div style="font-size: 18px;">Rupees _______________________________________________________</div>
            <div class="cheque-amount-box">₹ ____________</div>
            <div style="margin-top: 40px; font-size: 14px;">A/C No: <b><?php echo $user['account_number']; ?></b></div>
            <div class="cheque-sign"><?php echo $user['name']; ?><br>(Authorized Signatory)</div>
            <div class="micr">|| 000<?php echo rand(100, 999); ?> || 700200<?php echo rand(10, 99); ?> |: 345987 || 22</div>
        </div>
    <?php endif; ?>

    <?php if ($type == 'atm'): ?>
        <div class="atm-card" id="printableArea">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <div style="font-weight:bold; font-style:italic;">TechBank Platinum</div>
                <div style="font-size:12px;">Debit</div>
            </div>
            <div class="chip"></div>
            <div class="card-number"><?php echo chunk_split("4590" . substr($user['account_number'], 0, 12), 4, ' '); ?>
            </div>
            <div style="margin-top: 20px; display:flex; justify-content:space-between; align-items:flex-end;">
                <div style="text-transform: uppercase; font-size: 14px;"><?php echo $user['name']; ?></div>
                <div class="card-expiry"><span style="font-size:10px; display:block;">VALID
                        THRU</span>12/<?php echo date('y') + 5; ?></div>
            </div>
        </div>
    <?php endif; ?>
</body>

</html>