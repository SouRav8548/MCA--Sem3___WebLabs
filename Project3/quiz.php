<?php
// quiz.php
require 'config.php';
require_login();

// how many questions per attempt and time limit (seconds per question)
$limit = 10;
$perQuestionSeconds = 20;

$stmt = $pdo->prepare('SELECT * FROM questions ORDER BY RAND() LIMIT ?');
$stmt->bindValue(1, (int) $limit, PDO::PARAM_INT);
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

// Save question IDs in session
$_SESSION['current_attempt_id'] = $attemptId;
$_SESSION['current_attempt_question_ids'] = array_column($questions, 'id');

include 'header.php';
?>

<style>
    .question-slide {
        display: none;
    }

    .question-slide.active {
        display: block;
    }
</style>

<div class="card">
    <h1>Quiz</h1>
    <p id="questionIndicator">Question 1 of <?php echo count($questions); ?></p>

    <form id="quizForm" method="post" action="submit_quiz.php"
        data-per-question-seconds="<?php echo (int) $perQuestionSeconds; ?>">
        <input type="hidden" name="attempt_id" value="<?php echo htmlspecialchars($attemptId); ?>">

        <?php foreach ($questions as $index => $q): ?>
            <div class="question-slide<?php echo $index === 0 ? ' active' : ''; ?>">
                <div class="quiz-question">
                    <h3 style="white-space: pre-wrap;">
                        <?php echo 'Q' . ($index + 1) . '. ' . htmlspecialchars($q['question_text']); ?>
                    </h3>
                </div>
                <ul class="quiz-options">
                    <?php foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                        <?php $field = 'option_' . strtolower($opt); ?>
                        <li>
                            <label>
                                <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="<?php echo $opt; ?>">
                                <span><?php echo htmlspecialchars($q[$field]); ?></span>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>

        <div style="display:flex; justify-content:space-between; margin-top:1rem; gap:0.5rem; flex-wrap:wrap;">
            <button type="button" id="prevQuestion" class="btn btn-secondary" style="display:none;">Previous</button>
            <button type="button" id="nextQuestion" class="btn btn-secondary">Next</button>
            <button type="submit" id="submitBtn" class="btn btn-primary" style="display:none;">Submit Quiz</button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const slides = document.querySelectorAll('.question-slide');
        const prevBtn = document.getElementById('prevQuestion');
        const nextBtn = document.getElementById('nextQuestion');
        const submitBtn = document.getElementById('submitBtn');
        const indicator = document.getElementById('questionIndicator');
        let currentStep = 0;

        function updateView() {
            // Hide all slides
            slides.forEach((slide, index) => {
                slide.classList.remove('active');
                if (index === currentStep) slide.classList.add('active');
            });

            // Update Question Indicator
            indicator.textContent = `Question ${currentStep + 1} of ${slides.length}`;

            // Button Visibility
            if (currentStep === 0) {
                prevBtn.style.display = 'none';
            } else {
                prevBtn.style.display = 'inline-block';
            }

            if (currentStep === slides.length - 1) {
                nextBtn.style.display = 'none';
                submitBtn.style.display = 'inline-block';
            } else {
                nextBtn.style.display = 'inline-block';
                submitBtn.style.display = 'none';
            }
        }

        nextBtn.addEventListener('click', function () {
            if (currentStep < slides.length - 1) {
                currentStep++;
                updateView();
            }
        });

        prevBtn.addEventListener('click', function () {
            if (currentStep > 0) {
                currentStep--;
                updateView();
            }
        });

        // Initialize view
        updateView();
    });
</script>

<?php include 'footer.php'; ?>