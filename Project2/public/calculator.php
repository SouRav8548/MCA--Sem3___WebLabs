<?php
require_once __DIR__ . '/../config/db.php';
include 'partials/header.php';
include 'partials/nav.php';

// 1. PHP: Basic Security Check
if (empty($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
?>

<!-- 2. CSS: iOS-Style / Modern Dark Design -->
<style>
    :root {
        --ios-bg: #000000;
        --ios-btn-num: #333333;
        --ios-btn-op: #ff9f0a;
        /* Classic Orange */
        --ios-btn-top: #a5a5a5;
        /* Light Grey for C, DEL */
        --ios-text-white: #ffffff;
        --ios-text-black: #000000;
        --gap-size: 12px;
    }

    /* --- Layout Wrapper --- */
    .calc-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 2rem;
        width: 100%;
        overflow: hidden;
        background-color: #1c1c1e;
        /* Dark outer background */
    }

    /* --- The Calculator Device Box --- */
    .calc-box {
        display: flex;
        flex-direction: column;
        width: 100%;
        max-width: 400px;
        /* Phone-like width for portrait, expands for sci */
        background-color: var(--ios-bg);
        border-radius: 40px;
        /* Heavy rounding like a phone screen */
        padding: 25px;
        box-shadow: 0 50px 100px -20px rgba(0, 0, 0, 0.5);
        transition: max-width 0.3s ease;
    }

    /* Expand box when scientific mode is active */
    .calc-box.scientific-mode {
        max-width: 700px;
    }

    /* --- Screen (Display) --- */
    #calcDisplay {
        text-align: right;
        font-size: 4rem;
        font-weight: 300;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        border: none;
        background: transparent;
        color: var(--ios-text-white);
        padding: 0 10px;
        margin-bottom: 20px;
        width: 100%;
        height: 100px;
    }

    /* --- Segmented Tab Control --- */
    .tabbar {
        display: flex;
        background-color: #2c2c2e;
        padding: 4px;
        border-radius: 20px;
        margin-bottom: 20px;
        align-self: center;
        width: fit-content;
    }

    .tabbar button {
        background: none;
        border: none;
        padding: 8px 20px;
        color: #8e8e93;
        cursor: pointer;
        font-weight: 600;
        border-radius: 16px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .tabbar button.active {
        background-color: #636366;
        color: white;
    }

    /* --- Buttons Grid --- */
    #calcRoot {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: var(--gap-size);
    }

    /* Scientific Grid overrides (more columns) */
    .scientific-grid {
        grid-template-columns: repeat(6, 1fr) !important;
    }

    /* --- Button Styling --- */
    #calcRoot button {
        aspect-ratio: 1;
        /* Perfect Circles */
        border: none;
        border-radius: 50%;
        font-size: 1.7rem;
        font-weight: 400;
        cursor: pointer;
        color: var(--ios-text-white);
        background-color: var(--ios-btn-num);
        display: flex;
        align-items: center;
        justify-content: center;
        transition: filter 0.2s;
        user-select: none;
        -webkit-tap-highlight-color: transparent;
    }

    #calcRoot button:active {
        filter: brightness(1.3);
    }

    /* --- Color Classes --- */
    /* Operators (Right column usually) */
    .op-btn {
        background-color: var(--ios-btn-op) !important;
        font-size: 2rem !important;
        padding-bottom: 5px;
        /* Visual centering adjustment */
    }

    .eval-btn {
        background-color: var(--ios-btn-op) !important;
    }

    /* Function Keys (Top Row) */
    .clear-btn {
        background-color: var(--ios-btn-top) !important;
        color: var(--ios-text-black) !important;
        /* Black text on light grey */
        font-weight: 500 !important;
    }

    /* Zero Button Span (Optional, simplified for grid here) */
    /* If we wanted the '0' to be wide, we'd need specific grid logic, 
       but keeping it simple circular is robust for dynamic switching */

    /* Mobile Responsive */
    @media (max-width: 600px) {
        .calc-wrapper {
            padding: 0;
            background: black;
        }

        .calc-box {
            height: 100vh;
            border-radius: 0;
            max-width: 100%;
            padding: 20px;
            justify-content: flex-end;
            /* Push buttons to bottom */
        }

        #calcDisplay {
            margin-top: auto;
            font-size: 3.5rem;
        }

        .tabbar {
            margin-top: 2rem;
            margin-bottom: auto;
        }
    }
</style>

<!-- 3. HTML Structure -->
<div class="calc-wrapper">
    <div id="mainCard" class="calc-box">

        <!-- Tab Switcher -->
        <div class="tabbar">
            <button id="basicTab" class="active">Basic</button>
            <button id="scientificTab">Scientific</button>
        </div>

        <!-- Display -->
        <input id="calcDisplay" readonly value="0">

        <!-- Keypad -->
        <div id="calcRoot"></div>

    </div>
</div>

<!-- 4. JAVASCRIPT LOGIC -->
<script>
    // --- MATH HELPERS (Degrees) ---
    window.calc_sin = function (deg) { return (deg % 180 === 0) ? 0 : Math.sin(deg * (Math.PI / 180)); };
    window.calc_cos = function (deg) { return ((deg - 90) % 180 === 0) ? 0 : Math.cos(deg * (Math.PI / 180)); };
    window.calc_tan = function (deg) { return ((deg - 90) % 180 === 0) ? Infinity : Math.tan(deg * (Math.PI / 180)); };
    window.calc_log = Math.log10;
    window.calc_ln = Math.log;
    window.calc_sqrt = Math.sqrt;
    window.calc_abs = Math.abs;

    document.addEventListener("DOMContentLoaded", () => {
        const display = document.getElementById("calcDisplay");
        const root = document.getElementById("calcRoot");
        const mainCard = document.getElementById("mainCard");
        const basicTab = document.getElementById("basicTab");
        const sciTab = document.getElementById("scientificTab");

        let isScientific = false;
        let currentExp = "";

        // Layout Definitions
        const basicKeys = [
            "C", "DEL", "%", "/",
            "7", "8", "9", "*",
            "4", "5", "6", "-",
            "1", "2", "3", "+",
            "0", ".", "(", ")", "="
        ];

        // Scientific Mode usually needs more columns or a different layout.
        // We will inject a combined list for simplicity in this grid.
        const sciKeys = [
            "sin", "cos", "tan", "deg", "C", "DEL",
            "log", "ln", "sqrt", "/", "7", "8",
            "pi", "e", "^", "*", "9", "4",
            "abs", "(", ")", "-", "5", "6",
            "1", "2", "3", "+", "0", ".",
            "=",   // We will handle the '=' separately to fill space if needed
        ];

        function renderKeys() {
            root.innerHTML = "";

            // 1. Configure Grid
            if (isScientific) {
                root.className = "scientific-grid";
                mainCard.classList.add("scientific-mode");
            } else {
                root.className = "";
                mainCard.classList.remove("scientific-mode");
            }

            // 2. Choose Keys
            // For basic, we use the standard 4-column layout manually to ensure order
            const keysToRender = isScientific ? sciKeys : basicKeys;

            keysToRender.forEach((key) => {
                if (key === "deg") return; // Skip placeholder if used

                const btn = document.createElement("button");

                // Text Replacements
                if (key === "sqrt") btn.innerText = "√";
                else if (key === "pi") btn.innerText = "π";
                else btn.innerText = key;

                // --- STYLING LOGIC ---
                // Top Row Grey (C, DEL, %)
                if (["C", "DEL", "%"].includes(key)) {
                    btn.className = "clear-btn";
                }
                // Right Column Orange (Operators)
                else if (["/", "*", "-", "+", "="].includes(key)) {
                    btn.className = "op-btn";
                }
                // Numbers & Scientific (Dark Grey - Default)
                else {
                    btn.className = ""; // Default style
                }

                // Special case: Make '=' look like an operator
                if (key === "=") btn.classList.add("eval-btn");

                // Event Listener
                btn.onclick = () => handleInput(key);
                root.appendChild(btn);
            });
        }

        function handleInput(key) {
            if (key === "C") {
                currentExp = "";
                display.value = "0";
                return;
            }
            if (key === "DEL") {
                currentExp = currentExp.toString().slice(0, -1);
                display.value = currentExp || "0";
                return;
            }
            if (key === "=") {
                try {
                    if (!currentExp) return;
                    // Auto-close parens
                    const open = (currentExp.match(/\(/g) || []).length;
                    const close = (currentExp.match(/\)/g) || []).length;
                    if (open > close) currentExp += ")".repeat(open - close);

                    let evalStr = currentExp
                        .replace(/\bsin\b/g, "window.calc_sin")
                        .replace(/\bcos\b/g, "window.calc_cos")
                        .replace(/\btan\b/g, "window.calc_tan")
                        .replace(/\blog\b/g, "window.calc_log")
                        .replace(/\bln\b/g, "window.calc_ln")
                        .replace(/\bsqrt\b/g, "window.calc_sqrt")
                        .replace(/\babs\b/g, "window.calc_abs")
                        .replace(/\bpi\b/g, "Math.PI")
                        .replace(/\be\b/g, "Math.E")
                        .replace(/\^/g, "**");

                    const res = eval(evalStr);

                    if (!isFinite(res) || isNaN(res)) {
                        display.value = "Error";
                    } else {
                        const final = parseFloat(res.toFixed(10));
                        display.value = final;
                        currentExp = String(final);
                    }
                } catch (e) {
                    display.value = "Error";
                }
                return;
            }

            // Reset display on new input if currently 0 or Error
            if (display.value === "Error" || (display.value === "0" && currentExp === "")) {
                if (!["+", "-", "*", "/", "%", "^", ")", "."].includes(key)) {
                    currentExp = "";
                }
            }

            // Append Logic
            if (["sin", "cos", "tan", "log", "ln", "sqrt", "abs"].includes(key)) {
                currentExp += key + "(";
            } else if (key === "pi") {
                currentExp += "pi";
            } else {
                currentExp += key;
            }
            display.value = currentExp;
        }

        // --- KEYBOARD SUPPORT ---
        document.addEventListener("keydown", (e) => {
            const key = e.key;

            // Numbers 0-9
            if (/[0-9]/.test(key)) handleInput(key);

            // Operators
            if (["+", "-", "*", "/", "%", "^", "(", ")", "."].includes(key)) {
                handleInput(key);
            }

            // Enter or =
            if (key === "Enter" || key === "=") {
                e.preventDefault();
                handleInput("=");
            }

            // Backspace (Delete)
            if (key === "Backspace") handleInput("DEL");

            // Escape (Clear)
            if (key === "Escape") handleInput("C");
        });

        // Tabs
        basicTab.onclick = () => { isScientific = false; updateTabs(); };
        sciTab.onclick = () => { isScientific = true; updateTabs(); };
        function updateTabs() {
            basicTab.className = !isScientific ? "active" : "";
            sciTab.className = isScientific ? "active" : "";
            renderKeys();
        }

        // Init
        renderKeys();
    });
</script>

<?php include 'partials/footer.php'; ?>