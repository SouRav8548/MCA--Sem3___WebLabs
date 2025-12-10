<?php
include '../config/db_connect.php';

// 1. Security Check
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['student_id'];

// 2. Fetch Student Info
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// 3. Fetch Marks
$stmt_m = $conn->prepare("SELECT * FROM marks WHERE student_id = ?");
$stmt_m->bind_param("i", $id);
$stmt_m->execute();
$marks = $stmt_m->get_result()->fetch_assoc();

// 4. Calculate Percentage
$percentage = 0;
if ($marks && $marks['total'] > 0) {
    $percentage = ($marks['total'] / 500) * 100;
}

// 5. Generate Timestamp
date_default_timezone_set('Asia/Kolkata'); // Change to your timezone if needed
$timestamp = date("F j, Y, g:i a");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Marksheet_<?php echo $user['roll_no']; ?></title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body {
            background: #555;
            /* Dark background for the browser view */
            font-family: 'Times New Roman', serif;
            display: flex;
            justify-content: center;
            padding: 20px;
        }

        /* The A4 Paper Look */
        #invoice-pos {
            background: #FFF;
            width: 210mm;
            /* A4 Width */
            min-height: 296mm;
            /* A4 Height */
            padding: 20mm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            position: relative;
            box-sizing: border-box;
            /* Ensures padding doesn't increase width */
        }

        /* Header Section */
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .photo-box {
            width: 120px;
            height: 120px;
            border: 1px solid #333;
            margin: 0 auto 15px;
            padding: 5px;
        }

        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Student Details */
        .info-table {
            width: 100%;
            margin-bottom: 30px;
        }

        .info-table td {
            padding: 5px;
            font-size: 1.1rem;
        }

        /* Marks Table */
        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 50px;
        }

        .marks-table th,
        .marks-table td {
            border: 1px solid #000;
            padding: 12px;
            text-align: center;
        }

        .marks-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        /* Footer Section */
        .footer {
            margin-top: 10px;
            text-align: center;
            font-size: 0.9rem;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
            position: absolute;
            bottom: 20mm;
            left: 20mm;
            right: 20mm;
        }

        /* Action Buttons (Hide when printing/pdf) */
        .actions {
            position: fixed;
            top: 20px;
            right: 20px;
        }

        .btn {
            padding: 10px 20px;
            background: #00d2ff;
            color: #000;
            border: none;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>

<body>

    <!-- Download Button (Visible only on screen) -->
    <div class="actions">
        <button onclick="generatePDF()" class="btn">Download / Save as PDF</button>
        <a href="dashboard.php" class="btn" style="background: #fff;">Back</a>
    </div>

    <!-- The Actual Marksheet Content -->
    <div id="invoice-pos">

        <!-- 1. Header & Photo -->
        <div class="header">
            <h1>Official Marksheet from Sourav's Web</h1>
            <h3>Result Management System - Sample Test</h3>

            <div class="photo-box">
                <!-- Ensure path is correct relative to this file -->
                <img src="../assets/uploads/<?php echo $user['photo']; ?>" alt="Student Photo">
            </div>
            <h2><?php echo htmlspecialchars($user['name']); ?></h2>
        </div>

        <!-- 2. Roll Number & Info -->
        <table class="info-table">
            <tr>
                <td><strong>Roll Number:</strong> <?php echo htmlspecialchars($user['roll_no']); ?></td>
                <td style="text-align: right;"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                </td>
            </tr>
        </table>

        <!-- 3. Marks Tabular Format -->
        <?php if ($marks): ?>
            <table class="marks-table">
                <thead>
                    <tr>
                        <th style="text-align: left;">Subject Name</th>
                        <th>Max Marks</th>
                        <th>Obtained Marks</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="text-align: left;">Mathematics</td>
                        <td>100</td>
                        <td><?php echo $marks['subject_1']; ?></td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">Physics</td>
                        <td>100</td>
                        <td><?php echo $marks['subject_2']; ?></td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">Chemistry</td>
                        <td>100</td>
                        <td><?php echo $marks['subject_3']; ?></td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">English</td>
                        <td>100</td>
                        <td><?php echo $marks['subject_4']; ?></td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">Computer Science</td>
                        <td>100</td>
                        <td><?php echo $marks['subject_5']; ?></td>
                    </tr>

                    <!-- 4. Total, Percentage, Grade -->
                    <tr style="background: #f9f9f9; font-weight: bold;">
                        <td style="text-align: center;">GRAND TOTAL</td>
                        <td>500</td>
                        <td><?php echo $marks['total']; ?></td>
                    </tr>
                </tbody>
            </table>

            <div
                style="display: flex; justify-content: space-between; margin-top: 20px; font-size: 1.2rem; font-weight: bold; border: 2px solid #000; padding: 15px;">
                <div>Percentage: &nbsp <?php echo number_format($percentage, 2); ?>%</div>
                <div>Grade: &nbsp&nbsp<?php echo $marks['grade']; ?></div>
            </div>

        <?php else: ?>
            <p style="text-align: center; color: red;">Marks details not found.</p>
        <?php endif; ?>

        <!-- 5. Footer with Timestamp -->
        <div class="footer">
            <p>This is a computer-generated document.</p>
            <p><strong>Downloaded on:</strong> <?php echo $timestamp; ?></p>
        </div>

    </div>

    <script>
        function generatePDF() {
            window.scrollTo(0, 0);
            const element = document.getElementById('invoice-pos');
            var opt = {
                margin: 0,
                filename: 'Marksheet_<?php echo $user['roll_no']; ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
</body>

</html>