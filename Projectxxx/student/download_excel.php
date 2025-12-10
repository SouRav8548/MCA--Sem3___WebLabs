<?php
// student/download_excel.php
require __DIR__ . '/../includes/auth.php';
require_student();
require __DIR__ . '/../includes/db.php';

$exam = $_GET['exam'] ?? '';
$term = $_GET['term'] ?? '';
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="marksheet.xls"');

echo "<table border='1'><tr><th>Subject Code</th><th>Subject</th><th>Max Marks</th><th>Marks Obtained</th><th>Exam</th><th>Term</th></tr>";

$q = 'SELECT sub.code, sub.name AS subject_name, sub.max_marks, m.marks_obtained, m.exam_name, m.term
      FROM marks m JOIN subjects sub ON sub.id=m.subject_id WHERE m.student_id=:sid';
$params = ['sid' => (int) $_SESSION['student_id']];
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

foreach ($stmt as $r) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($r['code']) . "</td>";
    echo "<td>" . htmlspecialchars($r['subject_name']) . "</td>";
    echo "<td>" . htmlspecialchars($r['max_marks']) . "</td>";
    echo "<td>" . htmlspecialchars($r['marks_obtained']) . "</td>";
    echo "<td>" . htmlspecialchars($r['exam_name']) . "</td>";
    echo "<td>" . htmlspecialchars($r['term']) . "</td>";
    echo "</tr>";
}
echo "</table>";
