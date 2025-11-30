// assets/app.js

/* ==========================
   THEME TOGGLE
   ========================== */

function initThemeToggle() {
    const root = document.documentElement;
    const btn = document.getElementById('themeToggle');
    if (!btn) return;

    const saved = localStorage.getItem('theme');
    if (saved === 'dark' || saved === 'light') {
        root.setAttribute('data-theme', saved);
    }

    function updateIcon() {
        const current = root.getAttribute('data-theme') || 'dark';
        btn.textContent = current === 'light' ? '🌙' : '☀️';
    }

    updateIcon();

    btn.addEventListener('click', () => {
        const current = root.getAttribute('data-theme') || 'dark';
        const next = current === 'light' ? 'dark' : 'light';
        root.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        updateIcon();
    });
}

/* ==========================
   NAVBAR TOGGLE (mobile)
   ========================== */

function initNavbarToggle() {
    const toggle = document.getElementById('navToggle');
    const links = document.getElementById('navLinks');
    if (!toggle || !links) return;

    toggle.addEventListener('click', () => {
        links.classList.toggle('open');
    });
}

/* ==========================
   PER-QUESTION TIMER + QUIZ NAVIGATION
   - Auto-starts when a question appears
   - Resets for each new question
   - Advances or submits when time is up
   ========================== */

let perQuestionSeconds = 20;      // default; overridden by data attribute on form
let questionTimeLeft = 0;         // remaining seconds for the current question
let questionTimerInterval = null; // setInterval handle
let currentQuestionIndex = 0;     // index of active .question-slide

function initPerQuestionTimerQuiz() {
    const quizForm = document.getElementById('quizForm');
    const timerWrapper = document.getElementById('timerWrapper');
    const display = document.getElementById('quizTimerDisplay');

    // If we are not on a quiz page, do nothing
    if (!quizForm || !timerWrapper || !display) return;

    // Show timer only on quiz pages
    timerWrapper.classList.add('visible');

    // Read per-question limit from form attribute (e.g., 20 seconds)
    const attr = quizForm.getAttribute('data-per-question-seconds');
    if (attr) {
        const parsed = parseInt(attr, 10);
        if (!isNaN(parsed) && parsed > 0) {
            perQuestionSeconds = parsed;
        }
    }

    const slides = Array.from(document.querySelectorAll('.question-slide'));
    if (!slides.length) return;

    const prevBtn = document.getElementById('prevQuestion');
    const nextBtn = document.getElementById('nextQuestion');
    const indicator = document.getElementById('questionIndicator');

    // --- Helper: render the remaining seconds into the navbar ---
    function updateQuestionTimerDisplay() {
        const safeSeconds = Math.max(questionTimeLeft, 0);
        display.textContent = String(safeSeconds).padStart(2, '0');
    }

    // --- Stop the current timer (used before changing questions or on submit) ---
    function resetQuestionTimer() {
        if (questionTimerInterval) {
            clearInterval(questionTimerInterval);
            questionTimerInterval = null;
        }
    }

    // --- Start/restart timer for the currently active question ---
    function startQuestionTimer() {
        // Set full time for this question
        questionTimeLeft = perQuestionSeconds;
        updateQuestionTimerDisplay();

        // Clear any previous interval for safety
        if (questionTimerInterval) {
            clearInterval(questionTimerInterval);
        }

        // Start countdown
        questionTimerInterval = setInterval(() => {
            questionTimeLeft--;
            updateQuestionTimerDisplay();

            // When time reaches zero, move to next question or submit quiz
            if (questionTimeLeft <= 0) {
                clearInterval(questionTimerInterval);
                questionTimerInterval = null;
                handleQuestionTimeout();
            }
        }, 1000);
    }

    // --- When time runs out for a question ---
    function handleQuestionTimeout() {
        const lastIndex = slides.length - 1;
        if (currentQuestionIndex < lastIndex) {
            alert('Time is up for this question. Moving to the next question.');
            showSlide(currentQuestionIndex + 1);
        } else {
            alert('Time is up for the last question. Submitting your quiz.');
            quizForm.submit();
        }
    }

    // --- Show a specific slide and (re)start its timer ---
    function showSlide(index) {
        if (index < 0 || index >= slides.length) return;

        // Stop timer for previous question, if any
        resetQuestionTimer();

        currentQuestionIndex = index;

        // Toggle active slide
        slides.forEach((slide, i) => {
            slide.classList.toggle('active', i === index);
        });

        // Update "Question X of N" text
        if (indicator) {
            indicator.textContent = `Question ${index + 1} of ${slides.length}`;
        }

        // Enable/disable nav buttons appropriately
        if (prevBtn) prevBtn.disabled = index === 0;
        if (nextBtn) nextBtn.disabled = index === slides.length - 1;

        // Start timer automatically for the question that just appeared
        startQuestionTimer();
    }

    // --- Hook up navigation buttons ---
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            if (currentQuestionIndex > 0) {
                showSlide(currentQuestionIndex - 1);
            }
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            if (currentQuestionIndex < slides.length - 1) {
                showSlide(currentQuestionIndex + 1);
            }
        });
    }

    // Stop timer when the quiz is submitted manually
    quizForm.addEventListener('submit', () => {
        resetQuestionTimer();
    });

    // INITIALIZE: show first question and start its timer immediately
    showSlide(0);
}

/* ==========================
   BOOTSTRAP ALL FEATURES
   ========================== */

document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initNavbarToggle();
    initPerQuestionTimerQuiz(); // timer + quiz navigation
});
