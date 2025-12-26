const toggleButton = document.getElementById("theme-toggle");
const currentTheme = localStorage.getItem("theme");

// Apply saved theme on load
if (currentTheme) {
  document.documentElement.setAttribute("data-theme", currentTheme);
  if (currentTheme === "dark") {
    toggleButton.innerText = "☀️ Light Mode";
  }
}

toggleButton.addEventListener("click", () => {
  let theme = document.documentElement.getAttribute("data-theme");

  if (theme === "dark") {
    document.documentElement.setAttribute("data-theme", "light");
    localStorage.setItem("theme", "light");
    toggleButton.innerText = "🌙 Dark Mode";
  } else {
    document.documentElement.setAttribute("data-theme", "dark");
    localStorage.setItem("theme", "dark");
    toggleButton.innerText = "☀️ Light Mode";
  }
});
