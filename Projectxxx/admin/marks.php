<?php
// admin/marks.php
require __DIR__ . '/../includes/auth.php';
require_admin();
require __DIR__ . '/../includes/db.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';

$msg = '';
$error = '';
$students = $pdo->query('SELECT id, student_id, name FROM students ORDER BY name')->fetchAll();
$subjects = $pdo->query('SELECT id, code, name, max_marks FROM subjects ORDER BY code')->fetchAll();

if (is_post()) {
    if (!csrf_verify($_POST['csrf'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            $student_pk = (int) $_POST['student_pk'];
            $subject_id = (int) $_POST['subject_id'];
            $marks_obtained = (int) $_POST['marks_obtained'];
            $exam_name = trim($_POST['exam_name']);
            $term = trim($_POST['term']);
            if ($marks_obtained < 0)
                $marks_obtained = 0;
            $max = (int) $pdo->query('SELECT max_marks FROM subjects WHERE id=' . $subject_id)->fetch()['max_marks'];
            if ($marks_obtained > $max) {
                $error = 'Marks exceed max marks for subject.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO marks (student_id, subject_id, marks_obtained, exam_name, term) VALUES (:sid,:sub,:m,:exam,:term)');
                try {
                    $stmt->execute(['sid' => $student_pk, 'sub' => $subject_id, 'm' => $marks_obtained, 'exam' => $exam_name, 'term' => $term]);
                    $msg = 'Marks saved.';
                } catch (PDOException $ex) {
                    $error = 'Duplicate entry for this subject/exam/term.';
                }
            }
        } elseif ($action === 'update') {
            $id = (int) $_POST['id'];
            $marks_obtained = (int) $_POST['marks_obtained'];
            $subject_id = (int) $_POST['subject_id'];
            $max = (int) $pdo->query('SELECT max_marks FROM subjects WHERE id=' . $subject_id)->fetch()['max_marks'];
            if ($marks_obtained < 0 || $marks_obtained > $max) {
                $error = 'Invalid marks.';
            } else {
                $pdo->prepare('UPDATE marks SET marks_obtained=:m WHERE id=:id')->execute(['m' => $marks_obtained, 'id' => $id]);
                $msg = 'Marks updated.';
            }
        } elseif ($action === 'delete') {
            $id = (int) $_POST['id'];
            $pdo->prepare('DELETE FROM marks WHERE id=:id')->execute(['id' => $id]);
            $msg = 'Entry deleted.';
        }
    }
}

$filter_exam = trim($_GET['exam'] ?? '');
$filter_term = trim($_GET['term'] ?? '');
$query = 'SELECT m.id, s.student_id, s.name, sub.code, sub.name AS subject_name, sub.max_marks, m.marks_obtained, m.exam_name, m.term
          FROM marks m
          JOIN students s ON m.student_id = s.id
          JOIN subjects sub ON m.subject_id = sub.id';
$params = [];
$where = [];
if ($filter_exam) {
    $where[] = 'm.exam_name = :exam';
    $params['exam'] = $filter_exam;
}
if ($filter_term) {
    $where[] = 'm.term = :term';
    $params['term'] = $filter_term;
}
if ($where)
    $query .= ' WHERE ' . implode(' AND ', $where);
$query .= ' ORDER BY s.name, sub.code';
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$rows = $stmt->fetchAll();

require __DIR__ . '/../includes/header.php';
?>
<h2>Manage Marks</h2>
<?php if ($msg): ?>
    <div class="alert success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($error): ?>
    <div class="alert error"><?= e($error) ?></div><?php endif; ?>

<section class="card">
    <h3>Add Marks</h3>
    <form method="post" class="grid">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">
        <div>
            <label>Student</label>
            <select name="student_pk" required>
                <option value="">Select</option>
                <?php foreach ($students as $st): ?>
                    <option value="<?= e($st['id']) ?>"><?= e($st['name']) ?> (<?= e($st['student_id']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Subject</label>
            <select name="subject_id" required>
                <option value="">Select</option>
                <?php foreach ($subjects as $su): ?>
                    <option value="<?= e($su['id']) ?>"><?= e($su['code']) ?> - <?= e($su['name']) ?> (Max:
                        <?= e($su['max_marks']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Marks Obtained</label>
            <input type="number" name="marks_obtained" min="0" required>
        </div>
        <div>
            <label>Exam Name</label>
            <input name="exam_name" required>
        </div>
        <div>
            <label>Term</label>
            <input name="term" required>
        </div>
        <button class="btn" type="submit">Save</button>
    </form>
</section>

<section class="card">
    <h3>Filter</h3>
    <form method="get" class="grid">
        <div><label>Exam</label><input name="exam" value="<?= e($filter_exam) ?>"></div>
        <div><label>Term</label><input name="term" value="<?= e($filter_term) ?>"></div>
        <button class="btn" type="submit">Apply</button>
        <a class="btn ghost" href="marks.php">Reset</a>
    </form>
</section>

<section class="card">
    <h3>Marks Entries</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Student</th>
                <th>Subject</th>
                <th>Exam</th>
                <th>Term</th>
                <th>Marks</th>
                <th>Max</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= e($r['name']) ?> (<?= e($r['student_id']) ?>)</td>
                    <td><?= e($r['code']) ?> - <?= e($r['subject_name']) ?></td>
                    <td><?= e($r['exam_name']) ?></td>
                    <td><?= e($r['term']) ?></td>
                    <td>
                        <form method="post" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= e($r['id']) ?>">
                            <input type="hidden" name="subject_id" value="<?= e($r['id']) ?>">
                            <!-- mitigated by max check fetch -->
                            <input type="number" name="marks_obtained" value="<?= e($r['marks_obtained']) ?>" min="0"
                                class="small-input">
                            <button class="btn small" type="submit">Save</button>
                        </form>
                    </td>
                    <td><?= e($r['max_marks']) ?></td>
                    <td>
                        <form method="post" onsubmit="return confirm('Delete entry?')" class="inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= e($r['id']) ?>">
                            <button class="btn small danger" type="submit">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>