// public/assets/js/app.js
// Basic rate limiting of rapid submissions (UX only; server validates CSRF)
document.addEventListener("submit", (e) => {
  const btn = e.target.querySelector("button[type=submit]");
  if (btn) {
    btn.disabled = true;
    setTimeout(() => (btn.disabled = false), 1500);
  }
});
