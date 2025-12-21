<?php
include 'db.php';

// Function to generate Unique 10-digit Account Number
function generateAccountNumber($conn)
{
    do {
        $acc_num = rand(1000000000, 9999999999);
        $check = $conn->query("SELECT id FROM users WHERE account_number='$acc_num'");
    } while ($check->num_rows > 0);
    return $acc_num;
}

// 1. REGISTER (Extended)
if (isset($_POST['register'])) {
    $role = $_POST['role'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['password']; // Plain text as requested

    // Extra Fields
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $pan = $_POST['pan_number'];
    $aadhaar = $_POST['aadhaar_number'];
    $qualification = $_POST['qualification'];

    // Generate Account Number only for consumers
    $acc_num = ($role == 'consumer') ? generateAccountNumber($conn) : 'NULL';

    $sql = "INSERT INTO users (name, email, password, role, status, account_number, dob, gender, address, phone, pan_number, aadhaar_number, qualification) 
            VALUES ('$name', '$email', '$pass', '$role', 'pending', $acc_num, '$dob', '$gender', '$address', '$phone', '$pan', '$aadhaar', '$qualification')";

    if ($conn->query($sql)) {
        $last_id = $conn->insert_id;
        $conn->query("INSERT INTO requests (user_id, request_type) VALUES ('$last_id', 'account_activation')");
        echo "<script>alert('Registration Successful! Your Account Number is generated once approved.'); window.location='index.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}

// 2. LOGIN
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email='$email'");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($password == $row['password']) { // Plain text check
            if ($row['status'] == 'pending') {
                echo "<script>alert('Account pending approval.'); window.location='index.php';</script>";
            } else {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['name'] = $row['name'];

                if ($row['role'] == 'consumer')
                    header("Location: consumer_dashboard.php");
                else
                    header("Location: manager_dashboard.php");
            }
        } else {
            echo "<script>alert('Invalid Password'); window.location='index.php';</script>";
        }
    } else {
        echo "<script>alert('User not found'); window.location='index.php';</script>";
    }
}

// 3. UPDATE PROFILE (New Feature)
if (isset($_POST['update_profile'])) {
    $uid = $_SESSION['user_id'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $education = $_POST['qualification'];

    // Handle Photo Upload
    $photo_sql = "";
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['name'] != "") {
        $target_dir = "uploads/";
        $filename = time() . "_" . basename($_FILES["profile_pic"]["name"]);
        $target_file = $target_dir . $filename;
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file);
        $photo_sql = ", profile_pic='$filename'";
    }

    $sql = "UPDATE users SET phone='$phone', address='$address', qualification='$education' $photo_sql WHERE id='$uid'";

    if ($conn->query($sql)) {
        echo "<script>alert('Profile Updated!'); window.location='profile.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}

// ... (Keep Loan and Service Request logic from previous version here) ...
// 4. APPLY FOR LOAN
if (isset($_POST['apply_loan'])) {
    $uid = $_SESSION['user_id'];
    $type = $_POST['loan_type'];
    $amount = $_POST['amount'];
    $justification = $_POST['justification'];

    $conn->query("INSERT INTO loans (user_id, loan_type, amount, justification) VALUES ('$uid', '$type', '$amount', '$justification')");
    header("Location: consumer_dashboard.php?msg=LoanApplied");
}

// 5. SERVICE REQUESTS (Cheque/ATM)
if (isset($_POST['request_service'])) {
    $uid = $_SESSION['user_id'];
    $type = $_POST['service_type'];
    $conn->query("INSERT INTO requests (user_id, request_type) VALUES ('$uid', '$type')");
    header("Location: consumer_dashboard.php?msg=ServiceRequested");
}

// 6. MANAGER APPROVAL ACTIONS
if (isset($_GET['approve_request'])) {
    if ($_SESSION['role'] == 'consumer')
        die("Access Denied");
    $req_id = $_GET['id'];
    $type = $_GET['type'];
    $uid = $_GET['uid'];
    $conn->query("UPDATE requests SET status='approved' WHERE id='$req_id'");
    if ($type == 'account_activation')
        $conn->query("UPDATE users SET status='active' WHERE id='$uid'");
    header("Location: manager_dashboard.php");
}

if (isset($_GET['approve_loan'])) {
    if ($_SESSION['role'] == 'consumer')
        die("Access Denied");
    $id = $_GET['id'];
    $conn->query("UPDATE loans SET status='approved' WHERE id='$id'");
    header("Location: manager_dashboard.php");
}
?>