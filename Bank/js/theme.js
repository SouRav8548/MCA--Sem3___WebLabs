// js/theme.js

// Single source for theme handling across the site
(function () {
  const html = document.documentElement;
  const toggle = document.getElementById("themeToggle"); // <button id="themeToggle">
  const THEME_KEY = "theme"; // localStorage key

  if (!toggle) return;

  // 1) Apply saved or system theme on load
  const prefersDark =
    window.matchMedia &&
    window.matchMedia("(prefers-color-scheme: dark)").matches; // optional system default[web:640]

  const saved = localStorage.getItem(THEME_KEY);
  const initial =
    saved === "light" || saved === "dark"
      ? saved
      : prefersDark
      ? "dark"
      : "light";

  setTheme(initial);

  // 2) Toggle on click
  toggle.addEventListener("click", () => {
    const current =
      html.getAttribute("data-theme") === "dark" ? "dark" : "light";
    const next = current === "dark" ? "light" : "dark";
    setTheme(next);
  });

  function setTheme(theme) {
    html.setAttribute("data-theme", theme);
    localStorage.setItem(THEME_KEY, theme); // remember user choice[web:629]
    // Optional: update button label/icon
    toggle.textContent = theme === "dark" ? "Light mode" : "Dark mode";
  }
})();
