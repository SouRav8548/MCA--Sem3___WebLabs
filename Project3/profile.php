<?php
// profile.php
require 'config.php';
require_login();

$userId = $_SESSION['user']['id'];
$errors = [];
$success = '';

// Fetch current user data
$stmt = $pdo->prepare('SELECT id, name, email, phone, dob FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    die('User not found.');
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dob   = trim($_POST['dob'] ?? '');

    // 1. Basic validation
    if ($name === '') {
        $errors[] = 'Full name is required.';
    }
    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email format is invalid.';
    }

    // Optional: basic phone and DOB validation
    if ($phone !== '' && !preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) {
        $errors[] = 'Phone number format is invalid.';
    }
    if ($dob !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
        $errors[] = 'Date of birth must be in YYYY-MM-DD format.';
    }

    // 2. Check for unique email (ignore current user)
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            $errors[] = 'This email address is already in use.';
        }
    }

    // 3. Update in database
    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, phone = ?, dob = ? WHERE id = ?');
        $stmt->execute([$name, $email, $phone ?: null, $dob ?: null, $userId]);

        // Refresh user data
        $user['name']  = $name;
        $user['email'] = $email;
        $user['phone'] = $phone;
        $user['dob']   = $dob;

        // Update session so navbar etc. reflect new name/email
        $_SESSION['user']['name']  = $name;
        $_SESSION['user']['email'] = $email;

        $success = 'Profile updated successfully.';
    }
}

// Fetch quiz attempts for this user ordered by date
$stmt = $pdo->prepare('
    SELECT id, started_at, score, total_questions
    FROM quiz_attempts
    WHERE user_id = ?
    ORDER BY started_at ASC
');
$stmt->execute([$userId]);
$attempts = $stmt->fetchAll();

// Prepare data for Chart.js
$labels        = [];
$dataScores    = [];
$latestScore   = null;
$latestPercent = null;
$latestDate    = null;

foreach ($attempts as $a) {
    // DATE-ONLY label instead of date + time
    $labels[] = date('Y-m-d', strtotime($a['started_at']));

    $percent  = $a['total_questions'] > 0
        ? round(($a['score'] / $a['total_questions']) * 100, 2)
        : 0;

    $dataScores[] = $percent;
    $latestScore   = $a['score'];
    $latestPercent = $percent;
    $latestDate    = $a['started_at'];
}


// Encode chart data for JS
$chartLabels = json_encode($labels);
$chartData   = json_encode($dataScores);

include 'header.php';


?>
<div class="card">
    <h1>Your Profile</h1>

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

    <!-- Highlight latest quiz result -->
    <div class="card" style="margin-top:1rem; background:rgba(78,159,255,0.08);">
        <h2>Latest Quiz Performance</h2>
        <?php if ($latestScore === null): ?>
            <p>You have not completed any quizzes yet.</p>
        <?php else: ?>
            <p>
                <strong>Score:</strong>
                <?php echo htmlspecialchars($latestScore); ?> /
                <?php echo htmlspecialchars(end($attempts)['total_questions']); ?>
                (<?php echo htmlspecialchars($latestPercent); ?>%)
            </p>
            <p><strong>Date:</strong> <?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($latestDate))); ?></p>
        <?php endif; ?>
    </div>

    
    

    <!-- Profile edit form -->
    <div class="card" style="margin-top:1rem;">
        <h2>Edit Personal Information</h2>
        <form method="post" novalidate>
            <div class="form-group">
                <label for="name">Full name</label>
                <input id="name" name="name" type="text" required
                       value="<?php echo htmlspecialchars($user['name']); ?>">
            </div>

            <div class="form-group">
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" required
                       value="<?php echo htmlspecialchars($user['email']); ?>">
            </div>

            <div class="form-group">
                <label for="phone">Phone number</label>
                <input id="phone" name="phone" type="text"
                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                       placeholder="+91 9876543210">
            </div>
            

            <div class="form-group dob-flip-group">
                <label for="dob">Date of birth 🎂</label>

                <div class="dob-flip-card" id="dobFlipCard">
                    <div class="dob-flip-inner">
                        <!-- FRONT: pretty display / placeholder -->
                        <div class="dob-face dob-front">
                            <span class="dob-front-icon">📅</span>
                            <span class="dob-front-text">
                                <?php echo $user['dob'] ? htmlspecialchars($user['dob']) : 'Select your birthday'; ?>
                            </span>
                        </div>

                        <!-- BACK: real date input with native calendar -->
                        <div class="dob-face dob-back">
                            <input
                                id="dob"
                                name="dob"
                                type="date"
                                class="dob-input-real"
                                value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add more custom fields here if needed -->

            <button type="submit" class="btn btn-primary">Save changes</button>
        </form>
    </div>

    <!-- Chart.js area for score history -->
    <div class="card" style="margin-top:1rem;">
        <h2>Score History</h2>
        <?php if (!$attempts): ?>
            <p>No quiz data available yet.</p>
        <?php else: ?>
            <!-- Fixed-height wrapper for the chart -->
            <div style="position:relative; width:100%; height:260px;">
                <canvas id="scoreChart"></canvas>
            </div>
        <?php endif; ?>
    </div>
</div>


<!-- <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script> -->

<?php if ($attempts): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Guard: only proceed if the canvas exists
    const canvasEl = document.getElementById('scoreChart');
    if (canvasEl) {
        const scoreLabels = <?php echo json_encode($labels); ?>;
        const scoreValues = <?php echo json_encode($dataScores); ?>;

        const ctx = canvasEl.getContext('2d');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: scoreLabels,
                datasets: [{
                    label: 'Quiz Score (%)',
                    data: scoreValues,
                    borderColor: '#4e9fff',
                    backgroundColor: 'rgba(78,159,255,0.25)',
                    tension: 0.25,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // use wrapper height
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Score (%)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Attempt time'
                        }
                    }
                }
            }
        });
    }
</script>
<?php endif; ?>

<?php include 'footer.php'; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const flipCard = document.getElementById('dobFlipCard');
    const dobInput = document.getElementById('dob');

    if (!flipCard || !dobInput) return;

    // Click on front flips to back and focuses calendar
    flipCard.addEventListener('click', function () {
        flipCard.classList.add('flipped');
        // slight delay so flip animation starts before focus
        setTimeout(() => dobInput.showPicker ? dobInput.showPicker() : dobInput.focus(), 180);
    });

    // When date changes, update front text and flip back
    dobInput.addEventListener('change', function () {
        const frontText = flipCard.querySelector('.dob-front-text');
        if (this.value) {
            frontText.textContent = this.value;
        }
        flipCard.classList.remove('flipped');
    });

    // Optional: on blur, flip back if no value selected
    dobInput.addEventListener('blur', function () {
        if (!this.value) {
            flipCard.classList.remove('flipped');
        }
    });
});
</script>
