// js/main.js

// ---------- THEME TOGGLE ----------
(function () {
  const root = document.documentElement;
  const toggleBtn =
    document.getElementById("custThemeToggle") ||
    document.getElementById("themeToggle");

  if (!toggleBtn) return;

  const applySavedTheme = () => {
    const saved = localStorage.getItem("theme");
    const theme = saved === "light" || saved === "dark" ? saved : "dark";
    root.setAttribute("data-theme", theme);
    toggleBtn.textContent = theme === "light" ? "☀️" : "🌙";
  };

  applySavedTheme();

  toggleBtn.addEventListener("click", () => {
    const current =
      root.getAttribute("data-theme") === "light" ? "light" : "dark";
    const next = current === "dark" ? "light" : "dark";
    root.setAttribute("data-theme", next);
    localStorage.setItem("theme", next);
    toggleBtn.textContent = next === "light" ? "☀️" : "🌙";
  });
})();

// ---------- GENERIC FORM HELPERS ----------
document.addEventListener("DOMContentLoaded", () => {
  // Add .has-value class for filled inputs (for nicer labels, etc.)
  document.querySelectorAll("input, textarea").forEach((el) => {
    const update = () => {
      if (el.value && el.value.trim() !== "") {
        el.classList.add("has-value");
      } else {
        el.classList.remove("has-value");
      }
    };
    el.addEventListener("input", update);
    update();
  });
});

// ---------- SIMPLE CLIENT-SIDE VALIDATION ----------
(function () {
  const forms = document.querySelectorAll('form[data-validate="basic"]');
  if (!forms.length) return;

  forms.forEach((form) => {
    form.addEventListener("submit", (e) => {
      const requiredFields = form.querySelectorAll('[data-required="true"]');
      let firstError = null;

      requiredFields.forEach((field) => {
        field.classList.remove("field-error");
        if (!field.value || field.value.trim() === "") {
          field.classList.add("field-error");
          if (!firstError) firstError = field;
        }
      });

      if (firstError) {
        e.preventDefault();
        firstError.focus();
      }
    });
  });
})();

// ---------- PASSWORD TOGGLE ----------
(function () {
  document.querySelectorAll('[data-toggle="password"]').forEach((btn) => {
    const targetId = btn.getAttribute("data-target");
    const input = document.getElementById(targetId);
    if (!input) return;

    btn.addEventListener("click", () => {
      if (input.type === "password") {
        input.type = "text";
        btn.textContent = "Hide";
      } else {
        input.type = "password";
        btn.textContent = "Show";
      }
    });
  });
})();
