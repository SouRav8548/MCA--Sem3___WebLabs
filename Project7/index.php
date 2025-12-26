<?php
// Configuration
$filename = "data.txt";
// $filename = "good.php";
$message = "";
$messageType = "";

// ---------------------------------------------------------
// 1. HANDLE FORM SUBMISSIONS
// ---------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ACTION A: Append New Line
    if (isset($_POST['action_append'])) {
        $input = strip_tags(trim($_POST['user_input']));

        // Add new line if file has content
        if (file_exists($filename) && filesize($filename) > 0) {
            $input = PHP_EOL . $input;
        }

        if (file_put_contents($filename, $input, FILE_APPEND | LOCK_EX) !== false) {
            $message = "Line appended successfully!";
            $messageType = "success";
        } else {
            $message = "Error appending to file.";
            $messageType = "error";
        }
    }

    // ACTION B: Save Full Edit (Overwrite)
    if (isset($_POST['action_save_edit'])) {
        // Get raw content to preserve formatting
        $fullContent = $_POST['full_content'];

        if (file_put_contents($filename, $fullContent, LOCK_EX) !== false) {
            $message = "File updated successfully!";
            $messageType = "success";
        } else {
            $message = "Error saving file.";
            $messageType = "error";
        }
    }
}

// ---------------------------------------------------------
// 2. READ FILE CONTENT
// ---------------------------------------------------------
$displayContent = ""; // For View Mode
$rawContent = "";     // For Edit Mode

if (file_exists($filename)) {
    $rawContent = file_get_contents($filename);
    $displayContent = !empty($rawContent) ? htmlspecialchars($rawContent) : "File is empty.";
} else {
    $displayContent = "File does not exist yet.";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Editor</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">

        <!-- HEADER -->
        <header>
            <h1>📝 Ultimate File Editor by You</h1>
            <button id="theme-toggle" class="btn theme-toggle">🌙 Dark Mode</button>
        </header>

        <!-- FEEDBACK MESSAGE -->
        <?php if (!empty($message)): ?>

            <div class="message <?php echo $messageType; ?>" id="msg-box">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- =========================
         VIEW MODE (Default)
    ========================== -->
        <div id="view-mode">
            <div class="toolbar">
                <button onclick="toggleEditMode()" class="btn btn-edit">✏️ Edit Full File</button>
                <a href="index.php" class="btn btn-refresh">🔄 Refresh</a>
            </div>

            <button class="button" data-text="Awesome">
                <span class="actual-text">&nbsp;File Content&nbsp;</span>
            </button>
            <div class="content-display">
                <div class="file-content"><?php echo $displayContent; ?></div>
            </div>

            <form action="index.php" method="POST">
                <label for="user_input">Append a new line:</label>
                <textarea name="user_input" class="standard-input" rows="3" placeholder="Type here..."
                    required></textarea>
                <button type="submit" name="action_append" class="btn btn-save">➕ Append Text</button>
            </form>
        </div>

        <!-- =========================
         EDIT MODE (Hidden)
    ========================== -->
        <div id="edit-mode" class="hidden">
            <div class="toolbar">
                <span style="margin-right: auto; font-weight:bold; font-size:1.2rem;">Editing Mode</span>
                <button onclick="toggleEditMode()" class="btn btn-cancel">❌ Cancel</button>
            </div>

            <form action="index.php" method="POST">
                <div class="editor-container">
                    <!-- Sidebar for Numbers -->
                    <div id="line-numbers" class="line-numbers"></div>

                    <!-- Hidden Ghost Div for Measuring Heights -->
                    <div id="text-measurer" class="code-typography"></div>

                    <!-- Actual Text Area -->
                    <textarea name="full_content" id="editor-area" class="editor-textarea code-typography"
                        placeholder="File content..." spellcheck="false" oninput="updateLineNumbers()"
                        onscroll="syncScroll()"><?php echo htmlspecialchars($rawContent); ?></textarea>
                </div>

                <button type="submit" name="action_save_edit" class="btn btn-save">💾 Save Changes (Ctrl+S)</button>
            </form>
        </div>

    </div>

    <!-- JAVASCRIPT -->
    <script>
        // 1. INITIALIZE
        window.onload = function () {
            if (!document.getElementById('edit-mode').classList.contains('hidden')) {
                updateLineNumbers();
            }

            // Restore Theme
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark') {
                document.body.classList.add('dark-mode');
                document.getElementById('theme-toggle').textContent = '☀️ Light Mode';
            }
        };

        // 2. LINE NUMBER LOGIC
        function updateLineNumbers() {
            const textarea = document.getElementById('editor-area');
            const lineNumbersEle = document.getElementById('line-numbers');
            const measurer = document.getElementById('text-measurer');

            if (!textarea || !lineNumbersEle || !measurer) return;

            // A. Match Ghost Width to Textarea Client Width (handles scrollbar calc)
            measurer.style.width = textarea.clientWidth + 'px';

            // B. Get lines
            const rawText = textarea.value;
            const lines = rawText.split(/\n/);

            let numbersHtml = "";

            // C. Loop through lines to measure height
            lines.forEach((line, index) => {
                // Fill text. Use zero-width space for empty lines so they have height.
                measurer.textContent = line ? line : '\u200b';

                // Get computed height (which now ONLY includes text, no vertical padding)
                const height = measurer.offsetHeight;

                // Add number div with that exact height
                numbersHtml += `<div style="height: ${height}px;">${index + 1}</div>`;
            });

            lineNumbersEle.innerHTML = numbersHtml;
        }

        // 3. SYNC SCROLL
        function syncScroll() {
            const textarea = document.getElementById('editor-area');
            const lineNumbersEle = document.getElementById('line-numbers');
            if (textarea && lineNumbersEle) {
                lineNumbersEle.scrollTop = textarea.scrollTop;
            }
        }

        // 4. RESIZE HANDLER
        window.addEventListener('resize', () => {
            const editMode = document.getElementById('edit-mode');
            if (!editMode.classList.contains('hidden')) {
                updateLineNumbers();
            }
        });

        // 5. TOGGLE MODES
        function toggleEditMode() {
            const viewMode = document.getElementById('view-mode');
            const editMode = document.getElementById('edit-mode');

            if (editMode.classList.contains('hidden')) {
                viewMode.classList.add('hidden');
                editMode.classList.remove('hidden');
                // Wait for DOM to paint before measuring
                setTimeout(updateLineNumbers, 0);
            } else {
                editMode.classList.add('hidden');
                viewMode.classList.remove('hidden');
            }
        }

        // 6. THEME TOGGLE
        const toggleBtn = document.getElementById('theme-toggle');
        toggleBtn.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            const isDark = document.body.classList.contains('dark-mode');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            toggleBtn.textContent = isDark ? '☀️ Light Mode' : '🌙 Dark Mode';
        });

        // 7. CTRL+S TO SAVE
        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                const editMode = document.getElementById('edit-mode');
                if (!editMode.classList.contains('hidden')) {
                    e.preventDefault();
                    const saveBtn = document.querySelector('#edit-mode .btn-save');
                    if (saveBtn) saveBtn.click();
                }
            }
        });

        // 8. HIDE ALERTS
        const msgBox = document.getElementById('msg-box');
        if (msgBox) {
            setTimeout(() => {
                msgBox.style.opacity = '0';
                setTimeout(() => msgBox.remove(), 500);
            }, 3000);
        }
    </script>

</body>

</html>