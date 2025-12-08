<?php
// quiz.php
require 'config.php';
require_login();

// how many questions per attempt and time limit (seconds per question)
$limit = 10;
$perQuestionSeconds = 20; // You can change this value later

$stmt = $pdo->prepare('SELECT * FROM questions ORDER BY RAND() LIMIT ?');
$stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
$stmt->execute();
$questions = $stmt->fetchAll();

if (!$questions) {
    include 'header.php';
    echo '<div class="card"><p class="incorrect-answer">No questions available. Please contact the administrator.</p></div>';
    include 'footer.php';
    exit;
}

// Create attempt record
$stmt = $pdo->prepare('INSERT INTO quiz_attempts (user_id, total_questions) VALUES (?, ?)');
$stmt->execute([$_SESSION['user']['id'], count($questions)]);
$attemptId = $pdo->lastInsertId();

// Save question IDs in session for later result checking
$_SESSION['current_attempt_id'] = $attemptId;
$_SESSION['current_attempt_question_ids'] = array_column($questions, 'id');

include 'header.php';
?>
<div class="card">
    <h1>Quiz</h1>
    <p id="questionIndicator"></p>

    <!-- data-per-question-seconds is read by JS to control timer per question -->
    <form id="quizForm"
          method="post"
          action="submit_quiz.php"
          data-per-question-seconds="<?php echo (int)$perQuestionSeconds; ?>">
        <input type="hidden" name="attempt_id" value="<?php echo htmlspecialchars($attemptId); ?>">

        <?php foreach ($questions as $index => $q): ?>
            <!-- Each question is a "slide"; JS will show/hide and manage timer -->
            <div class="question-slide<?php echo $index === 0 ? ' active' : ''; ?>">
                <div class="quiz-question">
                    <h3>
                        <?php echo 'Q' . ($index + 1) . '. ' . htmlspecialchars($q['question_text']); ?>
                    </h3>
                </div>
                <ul class="quiz-options">
                    <?php foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                        <?php $field = 'option_' . strtolower($opt); ?>
                        <li>
                            <label>
                                <input type="radio"
                                       name="answers[<?php echo $q['id']; ?>]"
                                       value="<?php echo $opt; ?>">
                                <span><?php echo htmlspecialchars($q[$field]); ?></span>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <div style="display:flex; justify-content:space-between; margin-top:1rem; gap:0.5rem; flex-wrap:wrap;">
            <button type="button" id="prevQuestion" class="btn btn-secondary">Previous</button>
            <button type="button" id="nextQuestion" class="btn btn-secondary">Next</button>
            <button type="submit" class="btn btn-primary">Submit Quiz</button>
        </div>
    </form>
</div>



<div class="quiz-page-container">
    <div class="quiz-card">
        <h1 class="quiz-title">Quiz</h1>
        <p class="quiz-subtitle">Answer each question before the timer runs out.</p>

        <p id="questionIndicator" class="quiz-progress"></p>

        <form id="quizForm" method="post" action="submit_quiz.php">
            <!-- question slide -->
            <div class="question-slide active">
                <p class="quiz-question-text">
                    What is the capital of France?
                </p>
                <ul class="quiz-options-list">
                    <li class="quiz-option-item">
                        <label class="quiz-option-label">
                            <input class="quiz-option-input" type="radio" name="answers[1]" value="A">
                            <span class="quiz-option-text">Paris</span>
                        </label>
                    </li>
                    <!-- other options -->
                </ul>
            </div>

            <div class="quiz-nav-row">
                <button type="button" id="prevQuestion" class="quiz-btn quiz-btn-secondary">
                    Previous
                </button>
                <button type="button" id="nextQuestion" class="quiz-btn quiz-btn-secondary">
                    Next
                </button>
                <button type="submit" class="quiz-btn quiz-btn-primary">
                    Submit Quiz
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
