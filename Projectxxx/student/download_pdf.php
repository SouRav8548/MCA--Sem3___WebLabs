<?php
// student/download_pdf.php
require __DIR__ . '/../includes/auth.php';
require_student();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/helpers.php';

$student_id = (int) $_SESSION['student_id'];
$exam = $_GET['exam'] ?? '';
$term = $_GET['term'] ?? '';

$stu = $pdo->prepare('SELECT student_id, name, dob FROM students WHERE id=:id');
$stu->execute(['id' => $student_id]);
$stu = $stu->fetch();

$q = 'SELECT sub.code, sub.name AS subject_name, sub.max_marks, m.marks_obtained
      FROM marks m JOIN subjects sub ON sub.id=m.subject_id WHERE m.student_id=:sid';
$params = ['sid' => $student_id];
if ($exam) {
    $q .= ' AND m.exam_name=:exam';
    $params['exam'] = $exam;
}
if ($term) {
    $q .= ' AND m.term=:term';
    $params['term'] = $term;
}
$q .= ' ORDER BY sub.code';
$stmt = $pdo->prepare($q);
$stmt->execute($params);
$rows = $stmt->fetchAll();
$total_obt = array_sum(array_column($rows, 'marks_obtained'));
$total_max = array_sum(array_column($rows, 'max_marks'));
$perc = $total_max ? round(($total_obt / $total_max) * 100, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Marksheet PDF</title>
    <link rel="stylesheet" href="/marksheet-portal/public/assets/css/styles.css">
    <style>
        @media print {
            .print-hide {
                display: none !important;
            }

            body {
                background: #fff;
            }

            .marksheet {
                box-shadow: none;
                border: none;
            }
        }

        .marksheet {
            max-width: 800px;
            margin: 24px auto;
            padding: 24px;
            background: #fff;
            border: 1px solid #ddd;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .hdr-right {
            text-align: right;
        }
    </style>
</head>

<body>
    <div class="marksheet">
        <div class="header">
            <div>
                <h2>Marksheet</h2>
                <p><strong>Student:</strong> <?= e($stu['name']) ?> (<?= e($stu['student_id']) ?>)</p>
                <p><strong>DOB:</strong> <?= e($stu['dob']) ?></p>
            </div>
            <div class="hdr-right">
                <?php if ($exam): ?>
                    <p><strong>Exam:</strong> <?= e($exam) ?></p><?php endif; ?>
                <?php if ($term): ?>
                    <p><strong>Term:</strong> <?= e($term) ?></p><?php endif; ?>
                <p><strong>Date:</strong> <?= date('Y-m-d') ?></p>
            </div>
        </div>
        <table class="table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Subject</th>
                    <th>Max</th>
                    <th>Obtained</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><?= e($r['code']) ?></td>
                        <td><?= e($r['subject_name']) ?></td>
                        <td><?= e($r['max_marks']) ?></td>
                        <td><?= e($r['marks_obtained']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2">Total</th>
                    <th><?= e($total_max) ?></th>
                    <th><?= e($total_obt) ?></th>
                </tr>
                <tr>
                    <th colspan="2">Percentage</th>
                    <th colspan="2"><?= e($perc) ?>%</th>
                </tr>
            </tfoot>
        </table>
        <div class="print-hide">
            <button class="btn" onclick="window.print()">Print / Save as PDF</button>
            <a class="btn ghost" href="/marksheet-portal/student/marksheet.php">Back</a>
        </div>
    </div>
</body>

</html>