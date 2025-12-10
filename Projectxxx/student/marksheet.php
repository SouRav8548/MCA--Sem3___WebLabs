<?php
// student/marksheet.php
require __DIR__ . '/../includes/auth.php';
require_student();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/header.php';

$student_id = (int) $_SESSION['student_id'];
$student = $pdo->prepare('SELECT student_id, name, dob, email FROM students WHERE id=:id');
$student->execute(['id' => $student_id]);
$student = $student->fetch();

$exam = $_GET['exam'] ?? '';
$term = $_GET['term'] ?? '';

$q = 'SELECT sub.code, sub.name AS subject_name, sub.max_marks, m.marks_obtained, m.exam_name, m.term
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
$grade = '';
$perc = $total_max ? round(($total_obt / $total_max) * 100, 2) : 0;
if ($perc >= 90)
    $grade = 'A+';
elseif ($perc >= 75)
    $grade = 'A';
elseif ($perc >= 60)
    $grade = 'B';
elseif ($perc >= 50)
    $grade = 'C';
elseif ($perc >= 40)
    $grade = 'D';
else
    $grade = 'F';
?>
<div class="marksheet">
    <div class="marksheet-header">
        <h2>Marksheet</h2>
        <div class="student-meta">
            <p><strong>ID:</strong> <?= e($student['student_id']) ?></p>
            <p><strong>Name:</strong> <?= e($student['name']) ?></p>
            <p><strong>DOB:</strong> <?= e($student['dob']) ?></p>
            <?php if ($exam): ?>
                <p><strong>Exam:</strong> <?= e($exam) ?></p><?php endif; ?>
            <?php if ($term): ?>
                <p><strong>Term:</strong> <?= e($term) ?></p><?php endif; ?>
        </div>
    </div>

    <table class="table marks">
        <thead>
            <tr>
                <th>Subject Code</th>
                <th>Subject</th>
                <th>Max Marks</th>
                <th>Marks Obtained</th>
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
            <tr>
                <th colspan="2">Grade</th>
                <th colspan="2"><?= e($grade) ?></th>
            </tr>
        </tfoot>
    </table>

    <div class="actions print-hide">
        <a class="btn" href="download_pdf.php?exam=<?= urlencode($exam) ?>&term=<?= urlencode($term) ?>">Download
            PDF</a>
        <a class="btn secondary"
            href="download_excel.php?exam=<?= urlencode($exam) ?>&term=<?= urlencode($term) ?>">Download Excel</a>
        <a class="btn ghost" href="download_csv.php?exam=<?= urlencode($exam) ?>&term=<?= urlencode($term) ?>">Download
            CSV</a>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>