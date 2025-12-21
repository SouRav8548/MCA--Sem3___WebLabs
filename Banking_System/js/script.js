document.addEventListener("DOMContentLoaded", () => {
  const toggleBtn = document.getElementById("theme-toggle");
  const currentTheme = localStorage.getItem("theme") || "dark";

  document.documentElement.setAttribute("data-theme", currentTheme);
  toggleBtn.textContent =
    currentTheme === "dark" ? "☀️ Light Mode" : "🌙 Dark Mode";

  toggleBtn.addEventListener("click", () => {
    let theme = document.documentElement.getAttribute("data-theme");
    let newTheme = theme === "dark" ? "light" : "dark";

    document.documentElement.setAttribute("data-theme", newTheme);
    localStorage.setItem("theme", newTheme);
    toggleBtn.textContent =
      newTheme === "dark" ? "☀️ Light Mode" : "🌙 Dark Mode";
  });
});

function printDiv(divId) {
  var printContents = document.getElementById(divId).innerHTML;
  var originalContents = document.body.innerHTML;
  document.body.innerHTML = printContents;
  window.print();
  document.body.innerHTML = originalContents;
  location.reload();
}
