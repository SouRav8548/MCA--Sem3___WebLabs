// assets/js/app.js
(function () {
  const themeToggle = document.getElementById("theme-toggle");
  const root = document.documentElement;
  const stored = localStorage.getItem("theme");
  if (stored) root.setAttribute("data-theme", stored);
  themeToggle.addEventListener("click", () => {
    const t = root.getAttribute("data-theme") === "light" ? "dark" : "light";
    root.setAttribute("data-theme", t);
    localStorage.setItem("theme", t);
  });

  // converter tab switch
  document.querySelectorAll(".converter-tabs button").forEach((btn) => {
    btn.addEventListener("click", () => {
      const tab = btn.dataset.tab;
      document
        .querySelectorAll(".converter-panel")
        .forEach((p) => (p.style.display = "none"));
      document.getElementById(tab + "-tab").style.display = "block";
    });
  });

  // calculator mode toggle
  document.querySelectorAll('input[name="calc-mode"]').forEach((r) => {
    r.addEventListener("change", () => {
      const v = document.querySelector('input[name="calc-mode"]:checked').value;
      document
        .querySelectorAll(".calc-panel")
        .forEach((p) => (p.style.display = "none"));
      document.getElementById(v + "-calc").style.display = "block";
    });
  });
})();
