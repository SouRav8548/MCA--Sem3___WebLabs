document.addEventListener("DOMContentLoaded", () => {
  // Theme Toggle Logic
  const themeBtn = document.getElementById("theme-toggle");
  const body = document.body;

  // Check local storage
  if (localStorage.getItem("theme") === "light") {
    body.setAttribute("data-theme", "light");
  }

  if (themeBtn) {
    themeBtn.addEventListener("click", () => {
      if (body.getAttribute("data-theme") === "light") {
        body.removeAttribute("data-theme");
        localStorage.setItem("theme", "dark");
      } else {
        body.setAttribute("data-theme", "light");
        localStorage.setItem("theme", "light");
      }
    });
  }

  // Auto-hide alerts after 3 seconds
  const alertBox = document.querySelector(".alert");
  if (alertBox) {
    setTimeout(() => {
      alertBox.style.display = "none";
    }, 3000);
  }
});
