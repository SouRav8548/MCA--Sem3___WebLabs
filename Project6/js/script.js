document.addEventListener("DOMContentLoaded", () => {
  const toggleBtn = document.getElementById("theme-toggle");
  const currentTheme = localStorage.getItem("theme") || "dark";

  document.documentElement.setAttribute("data-theme", currentTheme);
  if (toggleBtn)
    toggleBtn.textContent =
      currentTheme === "dark" ? "☀️ Light Mode" : "🌙 Dark Mode";

  if (toggleBtn) {
    toggleBtn.addEventListener("click", () => {
      let theme = document.documentElement.getAttribute("data-theme");
      let newTheme = theme === "dark" ? "light" : "dark";

      document.documentElement.setAttribute("data-theme", newTheme);
      localStorage.setItem("theme", newTheme);
      toggleBtn.textContent =
        newTheme === "dark" ? "☀️ Light Mode" : "🌙 Dark Mode";
    });
  }
});
