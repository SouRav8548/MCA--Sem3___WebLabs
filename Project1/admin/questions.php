<?php
// admin/questions.php
require '../config.php';
require_admin();

$errors = [];
$success = '';
$id = $_GET['id'] ?? null;
$editing = false;

$question = [
    'question_text'   => '',
    'option_a'        => '',
    'option_b'        => '',
    'option_c'        => '',
    'option_d'        => '',
    'correct_option'  => 'A',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?: null;

    $question['question_text']  = trim($_POST['question_text'] ?? '');
    $question['option_a']       = trim($_POST['option_a'] ?? '');
    $question['option_b']       = trim($_POST['option_b'] ?? '');
    $question['option_c']       = trim($_POST['option_c'] ?? '');
    $question['option_d']       = trim($_POST['option_d'] ?? '');
    $question['correct_option'] = $_POST['correct_option'] ?? 'A';

    foreach (['question_text','option_a','option_b','option_c','option_d'] as $field) {
        if ($question[$field] === '') {
            $errors[] = 'All fields are required.';
            break;
        }
    }

    if (!in_array($question['correct_option'], ['A','B','C','D'], true)) {
        $errors[] = 'Correct option must be A, B, C, or D.';
    }

    if (!$errors) {
        if ($id) {
            $stmt = $pdo->prepare('UPDATE questions
                SET question_text = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_option = ?
                WHERE id = ?');
            $stmt->execute([
                $question['question_text'],
                $question['option_a'],
                $question['option_b'],
                $question['option_c'],
                $question['option_d'],
                $question['correct_option'],
                $id
            ]);
            $success = 'Question updated successfully.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO questions
                (question_text, option_a, option_b, option_c, option_d, correct_option)
                VALUES (?,?,?,?,?,?)');
            $stmt->execute([
                $question['question_text'],
                $question['option_a'],
                $question['option_b'],
                $question['option_c'],
                $question['option_d'],
                $question['correct_option']
            ]);
            $success = 'Question added successfully.';
            // reset form
            $question = [
                'question_text'   => '',
                'option_a'        => '',
                'option_b'        => '',
                'option_c'        => '',
                'option_d'        => '',
                'correct_option'  => 'A',
            ];
        }
    }
} elseif (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $stmt  = $pdo->prepare('DELETE FROM questions WHERE id = ?');
    $stmt->execute([$delId]);
    header('Location: questions.php');
    exit;
} elseif ($id) {
    $stmt = $pdo->prepare('SELECT * FROM questions WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
        $question = $row;
        $editing = true;
    }
}

// Load all questions for listing
$stmt = $pdo->query('SELECT * FROM questions ORDER BY id DESC');
$allQuestions = $stmt->fetchAll();

include '../header.php';
?>
<div class="card">
    <h1><?php echo $editing ? 'Edit Question' : 'Add New Question'; ?></h1>

    <?php if ($success): ?>
        <p class="correct-answer"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    <?php if ($errors): ?>
        <ul>
            <?php foreach ($errors as $e): ?>
                <li class="incorrect-answer"><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($id ?? ''); ?>">

        <div class="form-group">
            <label for="question_text">Question</label>
            <textarea id="question_text" name="question_text" rows="3" required><?php
                echo htmlspecialchars($question['question_text']);
            ?></textarea>
        </div>

        <div class="form-group">
            <label>Options</label>
            <input type="text" name="option_a" placeholder="Option A" required
                   value="<?php echo htmlspecialchars($question['option_a']); ?>">
            <input type="text" name="option_b" placeholder="Option B" required
                   value="<?php echo htmlspecialchars($question['option_b']); ?>">
            <input type="text" name="option_c" placeholder="Option C" required
                   value="<?php echo htmlspecialchars($question['option_c']); ?>">
            <input type="text" name="option_d" placeholder="Option D" required
                   value="<?php echo htmlspecialchars($question['option_d']); ?>">
        </div>

        <div class="form-group">
            <label for="correct_option">Correct option</label>
            <select id="correct_option" name="correct_option">
                <?php foreach (['A','B','C','D'] as $opt): ?>
                    <option value="<?php echo $opt; ?>"
                        <?php echo $question['correct_option'] === $opt ? 'selected' : ''; ?>>
                        <?php echo $opt; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            <?php echo $editing ? 'Save Changes' : 'Add Question'; ?>
        </button>
    </form>
</div>

<div class="card">
    <h2>All Questions</h2>
    <?php if (!$allQuestions): ?>
        <p>No questions found.</p>
    <?php else: ?>
        <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
            <thead>
            <tr>
                <th style="border-bottom:1px solid #ddd; text-align:left;">ID</th>
                <th style="border-bottom:1px solid #ddd; text-align:left;">Question</th>
                <th style="border-bottom:1px solid #ddd; text-align:left;">Correct</th>
                <th style="border-bottom:1px solid #ddd; text-align:left;">Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($allQuestions as $q): ?>
                <tr>
                    <td style="border-bottom:1px solid #f0f0f0;"><?php echo $q['id']; ?></td>
                    <td style="border-bottom:1px solid #f0f0f0;">
                        <?php echo htmlspecialchars(mb_strimwidth($q['question_text'], 0, 70, '…')); ?>
                    </td>
                    <td style="border-bottom:1px solid #f0f0f0;"><?php echo $q['correct_option']; ?></td>
                    <td style="border-bottom:1px solid #f0f0f0;">
                        <a class="btn btn-secondary" href="questions.php?id=<?php echo $q['id']; ?>">Edit</a>
                        <a class="btn btn-secondary"
                           href="questions.php?delete=<?php echo $q['id']; ?>"
                           onclick="return confirm('Delete this question?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php include '../footer.php'; ?>
