// assets/app.js

/* ==========================
   THEME TOGGLE (Unified)
   - Supports body.dark-mode + body.dark-mode
   ========================== */

// function initThemeToggle() {
//   const root = document.documentElement;
//   const body = document.body;
//   const btn = document.getElementById("themeToggle");

//   if (!btn) return;

//   // Load or set default theme
//   let theme = localStorage.getItem("theme") || "light";
//   localStorage.setItem("theme", theme);
//   applyTheme(theme);

//   btn.addEventListener("click", () => {
//     theme = theme === "light" ? "dark" : "light";
//     localStorage.setItem("theme", theme);
//     applyTheme(theme);
//   });

//   function applyTheme(theme) {
//     root.setAttribute("data-theme", theme);
//     body.classList.toggle("dark-mode", theme === "dark");
//     btn.textContent = theme === "dark" ? "☀️ Light Mode" : "🌙 Dark Mode";
//   }
// }
/* ==========================
   NAVBAR TOGGLE (Mobile)
   ========================== */

function initNavbarToggle() {
  const toggle = document.getElementById("navToggle");
  const links = document.getElementById("navLinks");
  if (!toggle || !links) return;

  toggle.addEventListener("click", () => {
    links.classList.toggle("open");
  });
}

/* ==========================
   PER-QUESTION TIMER + QUIZ
   ========================== */

let perQuestionSeconds = 20;
let questionTimeLeft = 0;
let questionTimerInterval = null;
let currentQuestionIndex = 0;

function initPerQuestionTimerQuiz() {
  const quizForm = document.getElementById("quizForm");
  const timerWrapper = document.getElementById("timerWrapper");
  const display = document.getElementById("quizTimerDisplay");

  if (!quizForm || !timerWrapper || !display) return;

  timerWrapper.classList.add("visible");

  const attr = quizForm.getAttribute("data-per-question-seconds");
  if (attr) {
    const parsed = parseInt(attr, 10);
    if (!isNaN(parsed) && parsed > 0) {
      perQuestionSeconds = parsed;
    }
  }

  const slides = Array.from(document.querySelectorAll(".question-slide"));
  if (!slides.length) return;

  const prevBtn = document.getElementById("prevQuestion");
  const nextBtn = document.getElementById("nextQuestion");
  const indicator = document.getElementById("questionIndicator");

  function updateTimerDisplay() {
    display.textContent = String(Math.max(questionTimeLeft, 0)).padStart(
      2,
      "0"
    );
  }

  function resetTimer() {
    if (questionTimerInterval) {
      clearInterval(questionTimerInterval);
      questionTimerInterval = null;
    }
  }

  function startTimer() {
    questionTimeLeft = perQuestionSeconds;
    updateTimerDisplay();

    resetTimer();
    questionTimerInterval = setInterval(() => {
      questionTimeLeft--;
      updateTimerDisplay();

      if (questionTimeLeft <= 0) {
        resetTimer();
        handleTimeout();
      }
    }, 1000);
  }

  function handleTimeout() {
    if (currentQuestionIndex < slides.length - 1) {
      alert("Time is up. Moving to next question.");
      showSlide(currentQuestionIndex + 1);
    } else {
      alert("Time is up. Submitting quiz.");
      quizForm.submit();
    }
  }

  function showSlide(index) {
    if (index < 0 || index >= slides.length) return;

    resetTimer();
    currentQuestionIndex = index;

    slides.forEach((slide, i) => {
      slide.classList.toggle("active", i === index);
    });

    if (indicator) {
      indicator.textContent = `Question ${index + 1} of ${slides.length}`;
    }

    if (prevBtn) prevBtn.disabled = index === 0;
    if (nextBtn) nextBtn.disabled = index === slides.length - 1;

    startTimer();
  }

  if (prevBtn) {
    prevBtn.addEventListener("click", () => {
      if (currentQuestionIndex > 0) {
        showSlide(currentQuestionIndex - 1);
      }
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener("click", () => {
      if (currentQuestionIndex < slides.length - 1) {
        showSlide(currentQuestionIndex + 1);
      }
    });
  }

  quizForm.addEventListener("submit", resetTimer);

  showSlide(0);
}

/* ==========================
   BOOTSTRAP
   ========================== */

document.addEventListener("DOMContentLoaded", () => {
  initThemeToggle();
  initNavbarToggle();
  initPerQuestionTimerQuiz();
});
