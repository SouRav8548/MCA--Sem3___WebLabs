// js/validation.js

// -------- BASIC HELPERS --------
function showError(field, message) {
  const group = field.closest(".form-group") || field.parentElement;
  const errorEl = group.querySelector(".error-msg");

  group.classList.add("has-error");
  if (errorEl) errorEl.textContent = message;
}

function clearError(field) {
  const group = field.closest(".form-group") || field.parentElement;
  const errorEl = group.querySelector(".error-msg");

  group.classList.remove("has-error");
  if (errorEl) errorEl.textContent = "";
}

function isEmail(value) {
  const pattern = /^[^ ]+@[^ ]+\.[a-z]{2,}$/i; // simple but effective pattern[web:680]
  return pattern.test(value.trim());
}

// -------- VALIDATORS --------
function validateRequired(field) {
  clearError(field);
  if (!field.value.trim()) {
    showError(field, "This field is required");
    return false;
  }
  return true;
}

function validateEmail(field) {
  if (!validateRequired(field)) return false;
  if (!isEmail(field.value)) {
    showError(field, "Enter a valid email address");
    return false;
  }
  return true;
}

function validatePassword(field, minLength = 6) {
  if (!validateRequired(field)) return false;
  if (field.value.length < minLength) {
    showError(field, `Password must be at least ${minLength} characters`);
    return false;
  }
  return true;
}

function validateConfirmPassword(passField, confirmField) {
  clearError(confirmField);
  if (!validateRequired(confirmField)) return false;
  if (passField.value !== confirmField.value) {
    showError(confirmField, "Passwords do not match");
    return false;
  }
  return true;
}

// -------- FORM WIRING --------
document.addEventListener("DOMContentLoaded", () => {
  // LOGIN FORM
  const loginForm = document.getElementById("loginForm");
  if (loginForm) {
    const email = loginForm.querySelector('input[name="email"]');
    const password = loginForm.querySelector('input[name="password"]');

    loginForm.addEventListener("submit", (e) => {
      let ok = true;
      ok = validateEmail(email) && ok;
      ok = validatePassword(password, 6) && ok;
      if (!ok) e.preventDefault();
    });
  }

  // REGISTER FORM
  const registerForm = document.getElementById("registerForm");
  if (registerForm) {
    const name = registerForm.querySelector('input[name="name"]');
    const email = registerForm.querySelector('input[name="email"]');
    const password = registerForm.querySelector('input[name="password"]');
    const confirm = registerForm.querySelector(
      'input[name="confirm_password"]'
    );

    registerForm.addEventListener("submit", (e) => {
      let ok = true;
      ok = validateRequired(name) && ok;
      ok = validateEmail(email) && ok;
      ok = validatePassword(password, 8) && ok; // stronger on register[web:677]
      ok = validateConfirmPassword(password, confirm) && ok;
      if (!ok) e.preventDefault();
    });
  }
});
