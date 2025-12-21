<?php
require 'config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['attempt_id'])) {
    header('Location: index.php');
    exit;
}

$attemptId = (int) $_POST['attempt_id'];
$answers = $_POST['answers'] ?? [];

// Ensure attempt belongs to user
$stmt = $pdo->prepare('SELECT * FROM quiz_attempts WHERE id = ? AND user_id = ?');
$stmt->execute([$attemptId, $_SESSION['user']['id']]);
$attempt = $stmt->fetch();
if (!$attempt) {
    header('Location: index.php');
    exit;
}
// Ensure attempt not already completed

$questionIds = $_SESSION['current_attempt_question_ids'] ?? [];
if (!$questionIds) {
    header('Location: index.php');
    exit;
}
// Fetch questions for grading 
$placeholders = implode(',', array_fill(0, count($questionIds), '?'));
$stmt = $pdo->prepare("SELECT * FROM questions WHERE id IN ($placeholders)");
$stmt->execute($questionIds);
$questions = $stmt->fetchAll();
$questionById = [];
foreach ($questions as $q) {
    $questionById[$q['id']] = $q;
}
// Grade answers

$score = 0;
$total = count($questionIds);
$storedAnswers = [];
// process each question
foreach ($questionIds as $qid) {
    $selected = $answers[$qid] ?? null;
    $correct = $questionById[$qid]['correct_option'] ?? null;
    $isCorrect = $selected && $selected === $correct ? 1 : 0;
    if ($isCorrect)
        $score++;

    $storedAnswers[] = [
        'question_id' => $qid,
        'selected_option' => $selected,
        'is_correct' => $isCorrect
    ];
}

// store answers
$pdo->beginTransaction();
$stmt = $pdo->prepare('INSERT INTO attempt_answers (attempt_id, question_id, selected_option, is_correct) VALUES (?,?,?,?)');
foreach ($storedAnswers as $row) {
    $stmt->execute([
        $attemptId,
        $row['question_id'],
        $row['selected_option'],
        $row['is_correct']
    ]);
}

// update attempt summary (time taken is approximate client-side; here left 0 or you can pass via hidden input)
$upd = $pdo->prepare('UPDATE quiz_attempts SET score = ?, completed_at = NOW() WHERE id = ?');
$upd->execute([$score, $attemptId]);
$pdo->commit();

// clean up session
unset($_SESSION['current_attempt_id'], $_SESSION['current_attempt_question_ids']);

include 'header.php';
?>
<div class="card">
    <h1>Your Results</h1>
    <p>You scored <strong><?php echo $score; ?></strong> out of <strong><?php echo $total; ?></strong>.</p>
</div>

<div class="card">
    <h2>Review Answers</h2>
    <?php foreach ($questionIds as $index => $qid): ?>
        <?php $q = $questionById[$qid]; ?>
        <?php
        $selected = $answers[$qid] ?? null;
        $correct = $q['correct_option'];
        $userCorrect = $selected && $selected === $correct;
        ?>
        <div style="margin-bottom:1rem;">
            <p><strong><?php echo 'Q' . ($index + 1) . '. ' . htmlspecialchars($q['question_text']); ?></strong></p>
            <ul class="quiz-options">
                <?php foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                    <?php
                    $field = 'option_' . strtolower($opt);
                    $isCorrect = $opt === $correct;
                    $isSelected = $opt === $selected;
                    ?>
                    <li>
                        <span
                            class="<?php echo $isCorrect ? 'correct-answer' : ($isSelected && !$isCorrect ? 'incorrect-answer' : ''); ?>">
                            <?php echo $opt; ?>. <?php echo htmlspecialchars($q[$field]); ?>
                            <?php if ($isCorrect): ?> (Correct)<?php endif; ?>
                            <?php if ($isSelected && !$isCorrect): ?> (Your choice)<?php endif; ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
</div>
<?php include 'footer.php'; ?>