document.addEventListener("DOMContentLoaded", () => {
  // Theme Toggle Logic
  const themeBtn = document.getElementById("themeToggle");
  const html = document.documentElement;
  const icon = themeBtn ? themeBtn.querySelector("i") : null;

  // Load saved theme
  const savedTheme = localStorage.getItem("smart_theme") || "dark";
  html.setAttribute("data-theme", savedTheme);
  updateIcon(savedTheme);

  if (themeBtn) {
    themeBtn.addEventListener("click", () => {
      const current = html.getAttribute("data-theme");
      const newTheme = current === "dark" ? "light" : "dark";
      html.setAttribute("data-theme", newTheme);
      localStorage.setItem("smart_theme", newTheme);
      updateIcon(newTheme);
    });
  }

  function updateIcon(theme) {
    if (!icon) return;
    if (theme === "light") {
      icon.classList.remove("fa-sun");
      icon.classList.add("fa-moon");
    } else {
      icon.classList.remove("fa-moon");
      icon.classList.add("fa-sun");
    }
  }
});
