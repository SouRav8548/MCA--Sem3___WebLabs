<?php
include '../config/db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$msg = "";

// Add or Update Marks Logic
if (isset($_POST['save_marks'])) {
    $roll_no = $_POST['roll_no'];
    $s1 = $_POST['s1'];
    $s2 = $_POST['s2'];
    $s3 = $_POST['s3'];
    $s4 = $_POST['s4'];
    $s5 = $_POST['s5'];

    // Calculate Total & Grade
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

    // Find Student ID
    $check_student = $conn->query("SELECT id FROM students WHERE roll_no = '$roll_no'");
    if ($check_student->num_rows > 0) {
        $student_data = $check_student->fetch_assoc();
        $student_id = $student_data['id'];

        // Check if marks exist
        $check_marks = $conn->query("SELECT id FROM marks WHERE student_id = '$student_id'");
        if ($check_marks->num_rows > 0) {
            // Update
            $sql = "UPDATE marks SET subject_1=$s1, subject_2=$s2, subject_3=$s3, subject_4=$s4, subject_5=$s5, total=$total, grade='$grade' WHERE student_id=$student_id";
            $msg = "Marks updated successfully!";
        } else {
            // Insert
            $sql = "INSERT INTO marks (student_id, subject_1, subject_2, subject_3, subject_4, subject_5, total, grade) VALUES ($student_id, $s1, $s2, $s3, $s4, $s5, $total, '$grade')";
            $msg = "Marks added successfully!";
        }
        $conn->query($sql);
    } else {
        $msg = "Error: Student with Roll No '$roll_no' not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <nav class="navbar">
        <div class="brand">Admin Dashboard</div>
        <div class="nav-links">
            <a href="logout.php">Logout</a> <!-- Create a simple logout.php that destroys session -->
        </div>
    </nav>

    <div class="glass-container">
        <h3>Manage Student Marks</h3>
        <?php if ($msg)
            echo "<p style='color: var(--accent-color);'>$msg</p>"; ?>

        <form method="POST">
            <div class="form-group">
                <label>Student Roll Number</label>
                <input type="text" name="roll_no" placeholder="Enter Roll No (e.g., 101)" required>
            </div>

            <div class="flex-grid">
                <div class="flex-col"><input type="number" name="s1" placeholder="Subject 1" max="100" required></div>
                <div class="flex-col"><input type="number" name="s2" placeholder="Subject 2" max="100" required></div>
                <div class="flex-col"><input type="number" name="s3" placeholder="Subject 3" max="100" required></div>
                <div class="flex-col"><input type="number" name="s4" placeholder="Subject 4" max="100" required></div>
                <div class="flex-col"><input type="number" name="s5" placeholder="Subject 5" max="100" required></div>
            </div>

            <button type="submit" name="save_marks" class="btn btn-primary mt-2">Save Marks</button>
        </form>
    </div>

    <div class="glass-container">
        <h3>All Student Records</h3>
        <div style="overflow-x:auto;">
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>Roll No</th>
                        <th>Name</th>
                        <th>Total (500)</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT s.roll_no, s.name, m.total, m.grade FROM students s LEFT JOIN marks m ON s.id = m.student_id";
                    $result = $conn->query($sql);
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['roll_no']}</td>
                            <td>{$row['name']}</td>
                            <td>" . ($row['total'] ?? 'Not Entered') . "</td>
                            <td>" . ($row['grade'] ?? '-') . "</td>
                        </tr>";

                        echo "<tr>
                            <td>{$row['roll_no']}</td>
                            <td>{$row['name']}</td>
                            <td>{$row['total']}</td>
                            <td><a href='manage_marks.php?roll={$row['roll_no']}' class='btn' style='padding:5px 10px; font-size:12px; background:var(--accent-color); color:black;'>Edit</a></td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>