<?php
// admin/dashboard.php
require __DIR__ . '/../includes/auth.php';
require_admin();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/header.php';

$stats = [
    'students' => (int) $pdo->query('SELECT COUNT(*) AS c FROM students')->fetch()['c'],
    'subjects' => (int) $pdo->query('SELECT COUNT(*) AS c FROM subjects')->fetch()['c'],
    'entries' => (int) $pdo->query('SELECT COUNT(*) AS c FROM marks')->fetch()['c'],
];
?>
<h2>Admin Dashboard</h2>
<div class="grid">
    <div class="card"><strong>Total Students:</strong> <?= $stats['students'] ?></div>
    <div class="card"><strong>Total Subjects:</strong> <?= $stats['subjects'] ?></div>
    <div class="card"><strong>Marks Entries:</strong> <?= $stats['entries'] ?></div>
</div>
<div class="actions">
    <a class="btn" href="students.php">Manage Students</a>
    <a class="btn" href="subjects.php">Manage Subjects</a>
    <a class="btn" href="marks.php">Manage Marks</a>
    <a class="btn secondary" href="export.php">Export/Backup</a>
    <a class="btn ghost" href="profile.php">Profile</a>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>