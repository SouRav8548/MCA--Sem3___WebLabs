<?php
include '../config/db_connect.php';

// Security: Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$msg = "";
$student_data = null;
$marks_data = null;

// 1. Handle "Edit" Mode: If a Roll No is passed via URL (e.g., manage_marks.php?roll=101)
if (isset($_GET['roll'])) {
    $roll_no = $_GET['roll'];

    // Fetch Student ID
    $stmt = $conn->prepare("SELECT id, name, roll_no FROM students WHERE roll_no = ?");
    $stmt->bind_param("s", $roll_no);
    $stmt->execute();
    $student_res = $stmt->get_result();

    if ($student_res->num_rows > 0) {
        $student_data = $student_res->fetch_assoc();
        $s_id = $student_data['id'];

        // Fetch existing marks if they exist
        $m_stmt = $conn->prepare("SELECT * FROM marks WHERE student_id = ?");
        $m_stmt->bind_param("i", $s_id);
        $m_stmt->execute();
        $marks_data = $m_stmt->get_result()->fetch_assoc();
    }
}

// 2. Handle Form Submission (Save Marks)
if (isset($_POST['save_marks'])) {
    $roll_no = $_POST['roll_no'];
    $s1 = $_POST['s1'];
    $s2 = $_POST['s2'];
    $s3 = $_POST['s3'];
    $s4 = $_POST['s4'];
    $s5 = $_POST['s5'];

    // Validation: Ensure marks are 0-100
    if (max($s1, $s2, $s3, $s4, $s5) > 100 || min($s1, $s2, $s3, $s4, $s5) < 0) {
        $msg = "Error: Marks must be between 0 and 100.";
    } else {
        // Calculation Logic
        $total = $s1 + $s2 + $s3 + $s4 + $s5;
        $percentage = ($total / 500) * 100;

        if ($percentage >= 90)
            $grade = 'A+';
        elseif ($percentage >= 80)
            $grade = 'A';
        elseif ($percentage >= 70)
            $grade = 'B';
        elseif ($percentage >= 60)
            $grade = 'C';
        elseif ($percentage >= 50)
            $grade = 'D';
        else
            $grade = 'F';

        // Check if student exists
        $check_student = $conn->prepare("SELECT id FROM students WHERE roll_no = ?");
        $check_student->bind_param("s", $roll_no);
        $check_student->execute();
        $res = $check_student->get_result();

        if ($res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $student_id = $row['id'];

            // Check if marks entry already exists -> Update or Insert
            $check_marks = $conn->query("SELECT id FROM marks WHERE student_id = $student_id");

            if ($check_marks->num_rows > 0) {
                // Update
                $update = $conn->prepare("UPDATE marks SET subject_1=?, subject_2=?, subject_3=?, subject_4=?, subject_5=?, total=?, grade=? WHERE student_id=?");
                $update->bind_param("iiiiissi", $s1, $s2, $s3, $s4, $s5, $total, $grade, $student_id);
                $update->execute();
                $msg = "Marks updated successfully for Roll: $roll_no";
            } else {
                // Insert
                $insert = $conn->prepare("INSERT INTO marks (student_id, subject_1, subject_2, subject_3, subject_4, subject_5, total, grade) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $insert->bind_param("iiiiiiis", $student_id, $s1, $s2, $s3, $s4, $s5, $total, $grade);
                $insert->execute();
                $msg = "Marks added successfully for Roll: $roll_no";
            }
        } else {
            $msg = "Error: Student with Roll Number '$roll_no' does not exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Marks</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <nav class="navbar">
        <div class="brand">Admin Portal</div>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="glass-container">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3>Manage Student Marks</h3>
            <a href="dashboard.php" class="btn"
                style="padding: 5px 15px; font-size: 0.8rem; border: 1px solid white;">&larr; Back</a>
        </div>

        <?php if ($msg): ?>
            <div
                style="padding: 10px; background: rgba(0, 255, 0, 0.1); border: 1px solid lime; border-radius: 5px; margin-bottom: 15px;">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <!-- Student Selection -->
            <div class="form-group">
                <label>Student Roll Number</label>
                <input type="text" name="roll_no"
                    value="<?php echo isset($student_data) ? $student_data['roll_no'] : ''; ?>"
                    placeholder="Enter Roll No (e.g., 101)" required <?php echo isset($student_data) ? 'readonly' : ''; ?>> <!-- Make readonly if editing specific student -->
                <?php if (isset($student_data)): ?>
                    <small style="color: var(--accent-color);">Editing Marks for:
                        <?php echo $student_data['name']; ?></small>
                <?php endif; ?>
            </div>

            <hr style="border-color: var(--glass-border); margin: 20px 0;">

            <!-- Subject Marks Inputs -->
            <div class="flex-grid">
                <div class="flex-col">
                    <label>Mathematics</label>
                    <input type="number" name="s1" min="0" max="100" required
                        value="<?php echo isset($marks_data) ? $marks_data['subject_1'] : ''; ?>">
                </div>
                <div class="flex-col">
                    <label>Physics</label>
                    <input type="number" name="s2" min="0" max="100" required
                        value="<?php echo isset($marks_data) ? $marks_data['subject_2'] : ''; ?>">
                </div>
                <div class="flex-col">
                    <label>Chemistry</label>
                    <input type="number" name="s3" min="0" max="100" required
                        value="<?php echo isset($marks_data) ? $marks_data['subject_3'] : ''; ?>">
                </div>
            </div>

            <div class="flex-grid mt-2">
                <div class="flex-col">
                    <label>English</label>
                    <input type="number" name="s4" min="0" max="100" required
                        value="<?php echo isset($marks_data) ? $marks_data['subject_4'] : ''; ?>">
                </div>
                <div class="flex-col">
                    <label>Computer Science</label>
                    <input type="number" name="s5" min="0" max="100" required
                        value="<?php echo isset($marks_data) ? $marks_data['subject_5'] : ''; ?>">
                </div>
            </div>

            <button type="submit" name="save_marks" class="btn btn-primary" style="width: 100%; margin-top: 2rem;">
                <?php echo isset($marks_data) ? 'Update Marks' : 'Save Marks'; ?>
            </button>
        </form>
    </div>

    <script src="../assets/js/script.js"></script>
</body>

</html>