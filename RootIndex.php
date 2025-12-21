<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Project Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
            --accent: #38bdf8;
            --accent-hover: #0ea5e9;
            --card-hover: #334155;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            padding: 40px 20px;
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 50px;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(to right, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
        }

        p.subtitle {
            color: var(--text-sub);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .card {
            background-color: var(--card-bg);
            border-radius: 16px;
            padding: 25px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            background-color: var(--card-hover);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border-color: rgba(56, 189, 248, 0.3);
        }

        .card-icon {
            font-size: 2rem;
            color: var(--accent);
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 8px;
            word-wrap: break-word;
        }

        .card-meta {
            font-size: 0.85rem;
            color: var(--text-sub);
            margin-top: auto;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-dot {
            height: 8px;
            width: 8px;
            background-color: #22c55e;
            border-radius: 50%;
            display: inline-block;
        }

        /* Empty state styling */
        .empty-state {
            text-align: center;
            color: var(--text-sub);
            grid-column: 1 / -1;
            padding: 50px;
        }
    </style>
</head>

<body>

    <div class="container">
        <header>
            <h1>My Projects Workspace</h1>
            <p class="subtitle">Select a project to launch</p>
        </header>

        <div class="grid">
            <?php
            // 1. Get current directory
            $dir = __DIR__;

            // 2. Scan directory
            $files = scandir($dir);

            // 3. Files to ignore (system files + this script itself)
            $ignore = ['.', '..', 'index.php', '.git', '.vscode', 'images'];

            $count = 0;

            foreach ($files as $file) {
                // Check if it's a directory and not in the ignore list
                if (is_dir($file) && !in_array($file, $ignore)) {
                    $count++;

                    // Get last modified date of the folder
                    $last_modified = date("M d, Y", filemtime($file));

                    // Determine icon based on name (just for fun)
                    $icon = 'fa-folder';
                    if (stripos($file, 'bank') !== false)
                        $icon = 'fa-building-columns';
                    elseif (stripos($file, 'shop') !== false)
                        $icon = 'fa-cart-shopping';

                    // Clean up the name (replace underscores with spaces)
                    $display_name = str_replace(['_', '-'], ' ', $file);

                    echo "
                <a href=\"$file\" class=\"card\">
                    <div class=\"card-icon\">
                        <i class=\"fa-solid $icon\"></i>
                    </div>
                    <div>
                        <div class=\"card-title\">$display_name</div>
                        <div class=\"card-meta\">
                            <span class=\"status-dot\"></span>
                            Last update: $last_modified
                        </div>
                    </div>
                </a>
                ";
                }
            }

            if ($count == 0) {
                echo "<div class='empty-state'>No project folders found in this directory.</div>";
            }
            ?>
        </div>
    </div>

</body>

</html>