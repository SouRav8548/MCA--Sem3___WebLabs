// --- Keyboard Support Note ---
// This calculator supports keyboard input for convenience.
// Numbers can be entered via the main keyboard or numpad.
// Operators: +, -, *, /, %, ., (, ), ^
// Special Keys: Enter (=), Backspace (DEL), Escape (C)

document.addEventListener("DOMContentLoaded", () => {
  const display = document.getElementById("calcDisplay");
  const root = document.getElementById("calcRoot");
  const basicTab = document.getElementById("basicTab");
  const sciTab = document.getElementById("scientificTab");

  let isScientific = false;
  let currentExp = "";

  // --- DEGREE CONVERSION HELPERS ---
  // These take Degrees, convert to Radians, then run the Math function.
  // Example: sin(90) -> Math.sin(90 * PI/180) -> 1
  const sinDeg = (deg) => Math.sin(deg * (Math.PI / 180));
  const cosDeg = (deg) => Math.cos(deg * (Math.PI / 180));
  const tanDeg = (deg) => Math.tan(deg * (Math.PI / 180));

  const basicKeys = [
    "C",
    "DEL",
    "%",
    "/",
    "7",
    "8",
    "9",
    "*",
    "4",
    "5",
    "6",
    "-",
    "1",
    "2",
    "3",
    "+",
    "0",
    ".",
    "(",
    ")",
  ];

  const sciKeys = [
    "sin",
    "cos",
    "tan",
    "log",
    "ln",
    "sqrt",
    "^",
    "pi",
    "e",
    "abs",
    "(",
    ")",
  ];

  function renderKeys() {
    root.innerHTML = "";
    // If scientific, prepend keys
    let keys = isScientific ? [...sciKeys, ...basicKeys] : basicKeys;

    // Add '=' at the end
    const finalKeys = [...keys, "="];

    finalKeys.forEach((key) => {
      const btn = document.createElement("button");
      btn.innerText = key === "sqrt" ? "√" : key;

      // Apply Classes for styling
      if (key === "=") btn.className = "eval-btn";
      else if (key === "C" || key === "DEL") btn.className = "clear-btn";
      else if (["+", "-", "*", "/", "%"].includes(key))
        btn.className = "op-btn";

      // Add click listener
      btn.onclick = () => handleInput(key);
      root.appendChild(btn);
    });
  }

  function handleInput(key) {
    // Clear All
    if (key === "C") {
      currentExp = "";
      display.value = "0";
      return;
    }

    // Delete Last Character
    if (key === "DEL" || key === "Backspace") {
      currentExp = currentExp.toString().slice(0, -1);
      display.value = currentExp || "0";
      return;
    }

    // Calculate
    if (key === "=" || key === "Enter") {
      try {
        // Safe evaluation replacement
        let evalString = currentExp
          // UPDATED: Use custom degree functions instead of Math.sin/cos/tan
          .replace(/sin/g, "sinDeg")
          .replace(/cos/g, "cosDeg")
          .replace(/tan/g, "tanDeg")
          // Standard Math replacements
          .replace(/log/g, "Math.log10")
          .replace(/ln/g, "Math.log")
          .replace(/sqrt/g, "Math.sqrt")
          .replace(/pi/g, "Math.PI")
          .replace(/e/g, "Math.E")
          .replace(/\^/g, "**");

        if (!evalString) return;

        // eval() will now look for sinDeg() which handles the conversion
        const result = eval(evalString);

        // Handle infinity or NaN
        if (!isFinite(result) || isNaN(result)) {
          display.value = "Error";
          currentExp = "";
        } else {
          // Optional: Fix floating point precision for common angles
          // e.g. sin(180) might return 1.22e-16 instead of 0 without rounding
          // We keep it raw here for accuracy, or you can add .toFixed(10) logic if preferred.
          display.value = result;
          currentExp = String(result);
        }
      } catch (e) {
        display.value = "Error";
        currentExp = "";
      }
      return;
    }

    // Standard Input
    if (display.value === "0" || display.value === "Error") {
      if (!["+", "-", "*", "/", "%", "."].includes(key)) currentExp = "";
    }

    // Wrap scientific functions
    if (["sin", "cos", "tan", "log", "ln", "sqrt", "abs"].includes(key)) {
      currentExp += key + "(";
    } else if (key === "pi") {
      currentExp += "pi";
    } else {
      currentExp += key;
    }

    display.value = currentExp;
  }

  // --- KEYBOARD LISTENER ---
  document.addEventListener("keydown", (e) => {
    const key = e.key;

    // Numbers & Operators
    if (/[0-9]/.test(key)) handleInput(key);
    if (["+", "-", "*", "/", "%", ".", "(", ")", "^"].includes(key))
      handleInput(key);

    // Special Keys
    if (key === "Enter" || key === "=") {
      e.preventDefault();
      handleInput("=");
    }
    if (key === "Backspace") handleInput("DEL");
    if (key === "Escape") handleInput("C");
  });

  // --- TAB SWITCHING ---
  basicTab.onclick = () => {
    isScientific = false;
    updateTabs();
    renderKeys();
  };
  sciTab.onclick = () => {
    isScientific = true;
    updateTabs();
    renderKeys();
  };

  function updateTabs() {
    basicTab.className = !isScientific ? "active" : "";
    sciTab.className = isScientific ? "active" : "";
  }

  renderKeys();
});
