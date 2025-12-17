/**
 * theme-toggle.js
 * Handles Light/Dark mode switching with localStorage persistence
 * and system preference detection.
 */

document.addEventListener("DOMContentLoaded", () => {
  const toggleButton = document.getElementById("theme-toggle");
  const body = document.body;

  // 1. Check for saved user preference
  const savedTheme = localStorage.getItem("theme");

  // 2. Helper to apply Dark Mode
  const enableDarkMode = () => {
    body.classList.add("dark-mode");
    body.classList.remove("light-mode");
    localStorage.setItem("theme", "dark");
  };

  // 3. Helper to apply Light Mode
  const enableLightMode = () => {
    body.classList.add("light-mode"); // Explicitly add light-mode class
    body.classList.remove("dark-mode");
    localStorage.setItem("theme", "light");
  };

  // 4. Initialize Theme based on storage or system default
  if (savedTheme === "dark") {
    enableDarkMode();
  } else if (savedTheme === "light") {
    enableLightMode();
  }
  // If no savedTheme, we leave the body classes empty.
  // The CSS @media (prefers-color-scheme) will handle the default.

  // 5. Event Listener for the Toggle Button
  if (toggleButton) {
    toggleButton.addEventListener("click", () => {
      // Determine current effective state
      // If body has 'dark-mode' OR (no explicit 'light-mode' AND system is dark)
      const systemPrefersDark = window.matchMedia(
        "(prefers-color-scheme: dark)"
      ).matches;
      const isDark =
        body.classList.contains("dark-mode") ||
        (!body.classList.contains("light-mode") && systemPrefersDark);

      if (isDark) {
        enableLightMode();
      } else {
        enableDarkMode();
      }
    });
  }

  // 6. Listen for System Changes (Only if user hasn't manually overridden)
  window
    .matchMedia("(prefers-color-scheme: dark)")
    .addEventListener("change", (e) => {
      if (!localStorage.getItem("theme")) {
        // No user override exists, so we let CSS handle it.
        // We just ensure no conflicting classes are present.
        body.classList.remove("dark-mode", "light-mode");
      }
    });
});
