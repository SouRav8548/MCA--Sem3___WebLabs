<?php
// admin/export.php
require __DIR__ . '/../includes/auth.php';
require_admin();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/header.php';

$now = date('Ymd_His');

function export_csv($pdo, $file)
{
    $fh = fopen($file, 'w');
    fputcsv($fh, ['student_id', 'name', 'subject_code', 'subject_name', 'exam', 'term', 'marks_obtained', 'max_marks']);
    $stmt = $pdo->query('SELECT s.student_id, s.name, sub.code, sub.name AS subject_name, m.exam_name, m.term, m.marks_obtained, sub.max_marks
    FROM marks m
    JOIN students s ON s.id=m.student_id
    JOIN subjects sub ON sub.id=m.subject_id
    ORDER BY s.student_id, sub.code, m.exam_name, m.term');
    while ($row = $stmt->fetch()) {
        fputcsv($fh, $row);
    }
    fclose($fh);
}
function export_excel($pdo)
{
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="marks_export.xls"');
    echo "<table border='1'><tr>
        <th>Student ID</th><th>Name</th><th>Subject Code</th><th>Subject Name</th><th>Exam</th><th>Term</th><th>Marks</th><th>Max</th>
        </tr>";
    $stmt = $pdo->query('SELECT s.student_id, s.name, sub.code, sub.name AS subject_name, m.exam_name, m.term, m.marks_obtained, sub.max_marks
    FROM marks m
    JOIN students s ON s.id=m.student_id
    JOIN subjects sub ON sub.id=m.subject_id');
    foreach ($stmt as $row) {
        echo "<tr>";
        foreach ($row as $cell)
            echo "<td>" . htmlspecialchars($cell) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

if (isset($_GET['excel'])) {
    export_excel($pdo);
    exit;
} else {
    $path = __DIR__ . '/../storage/backups/marks_' . $now . '.csv';
    if (!is_dir(dirname($path))) {
        mkdir(dirname($path), 0775, true);
    }
    export_csv($pdo, $path);
}
?>
<h2>Export / Backup</h2>
<p>CSV backup saved at: <code>storage/backups/marks_<?= e($now) ?>.csv</code></p>
<div class="actions">
    <a class="btn" href="?excel=1">Download Excel</a>
    <a class="btn ghost" href="dashboard.php">Back to Dashboard</a>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>