<?php
include 'db.php';

// ... (Keep existing Helper Functions & Register/Login logic) ...

// Helper to generate Account Number
function generateAccountNumber($conn)
{
    do {
        $acc_num = rand(1000000000, 9999999999);
        $check = $conn->query("SELECT id FROM users WHERE account_number='$acc_num'");
    } while ($check->num_rows > 0);
    return $acc_num;
}

// 1. REGISTER
if (isset($_POST['register'])) {
    $role = $_POST['role'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $dob = $_POST['dob'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $pan = $_POST['pan_number'];
    $aadhaar = $_POST['aadhaar_number'];
    $qualification = $_POST['qualification'];
    $acc_num = ($role == 'consumer') ? generateAccountNumber($conn) : 'NULL';
    $sql = "INSERT INTO users (name, email, password, role, status, account_number, dob, gender, address, phone, pan_number, aadhaar_number, qualification) VALUES ('$name', '$email', '$pass', '$role', 'pending', $acc_num, '$dob', '$gender', '$address', '$phone', '$pan', '$aadhaar', '$qualification')";
    if ($conn->query($sql)) {
        $last_id = $conn->insert_id;
        $conn->query("INSERT INTO requests (user_id, request_type) VALUES ('$last_id', 'account_activation')");
        echo "<script>alert('Registration Successful! Waiting for Approval.'); window.location='index.php';</script>";
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
        if ($password == $row['password']) {
            if ($row['status'] == 'active') {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['account_number'] = $row['account_number'];
                if ($row['role'] == 'consumer')
                    header("Location: consumer_dashboard.php");
                else
                    header("Location: manager_dashboard.php");
            } elseif ($row['status'] == 'rejected') {
                echo "<script>alert('Account Rejected. Contact branch.'); window.location='index.php';</script>";
            } else {
                echo "<script>alert('Account pending approval.'); window.location='index.php';</script>";
            }
        } else {
            echo "<script>alert('Invalid Password'); window.location='index.php';</script>";
        }
    } else {
        echo "<script>alert('User not found'); window.location='index.php';</script>";
    }
}

// 3. MONEY TRANSFER
if (isset($_POST['transfer_money'])) {
    $sender_id = $_SESSION['user_id'];
    $receiver_acc = $_POST['receiver_acc'];
    $amount = $_POST['amount'];
    $sender = $conn->query("SELECT balance, email, name FROM users WHERE id='$sender_id'")->fetch_assoc();
    if ($sender['balance'] < $amount) {
        echo "<script>alert('Insufficient Balance'); window.location='consumer_dashboard.php';</script>";
        exit;
    }
    $res = $conn->query("SELECT id, email, name FROM users WHERE account_number='$receiver_acc'");
    if ($res->num_rows > 0) {
        $receiver = $res->fetch_assoc();
        $receiver_id = $receiver['id'];
        if ($receiver_id == $sender_id) {
            echo "<script>alert('Cannot transfer to self'); window.location='consumer_dashboard.php';</script>";
            exit;
        }
        $conn->query("UPDATE users SET balance = balance - $amount WHERE id='$sender_id'");
        $conn->query("UPDATE users SET balance = balance + $amount WHERE id='$receiver_id'");
        $conn->query("INSERT INTO transactions (sender_id, receiver_id, amount, type) VALUES ('$sender_id', '$receiver_id', '$amount', 'transfer')");
        $to = $receiver['email'];
        echo "<script>alert('Transfer Successful! Email sent to " . $to . "'); window.location='consumer_dashboard.php';</script>";
    } else {
        echo "<script>alert('Receiver Account Not Found'); window.location='consumer_dashboard.php';</script>";
    }
}

// 4. CASH DEPOSIT
if (isset($_POST['deposit_money'])) {
    if ($_SESSION['role'] == 'consumer')
        die("Access Denied");
    $acc_num = $_POST['account_number'];
    $amount = $_POST['amount'];
    $res = $conn->query("SELECT id FROM users WHERE account_number='$acc_num'");
    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        $uid = $user['id'];
        $conn->query("UPDATE users SET balance = balance + $amount WHERE id='$uid'");
        $conn->query("INSERT INTO transactions (sender_id, receiver_id, amount, type) VALUES (NULL, '$uid', '$amount', 'cash_deposit')");
        echo "<script>alert('Deposit Successful!'); window.location='manager_dashboard.php';</script>";
    } else {
        echo "<script>alert('Account Not Found'); window.location='manager_dashboard.php';</script>";
    }
}

// 4A. CASH DEPOSIT or Withdraw By MANAGER into Staff Account (Legacy/Alternative Method)
if (isset($_POST['manage_staff_funds'])) {
    // 1. Debugging: Check if we are receiving data
    if (!isset($_POST['staff_id']) || !isset($_POST['amount'])) {
        die("Error: Missing Data in Form Submission");
    }

    // 2. Security: Check role
    if (!isset($_SESSION['role']) || $_SESSION['role'] != 'manager') {
        die("Access Denied: You are not a manager.");
    }

    $manager_id = $_SESSION['user_id']; // Use Manager ID to fix NULL errors
    $staff_id = $_POST['staff_id'];
    $amount = floatval($_POST['amount']);
    $action = $_POST['action'];

    // 3. Database: Fetch user safely
    $query = $conn->query("SELECT * FROM users WHERE id='$staff_id' AND role='staff'");

    if (!$query) {
        die("Database Query Failed (Select Staff): " . $conn->error);
    }

    if ($query->num_rows > 0) {
        $staff = $query->fetch_assoc();
        $current_balance = floatval($staff['balance']);
        $staff_name = $staff['name'];

        if ($action == 'load') {
            $new_balance = $current_balance + $amount;

            // Execute Update
            if (!$conn->query("UPDATE users SET balance = '$new_balance' WHERE id='$staff_id'")) {
                die("Update Failed: " . $conn->error);
            }

            // FIX: Sender = Manager ($manager_id), Receiver = Staff ($staff_id)
            if (!$conn->query("INSERT INTO transactions (sender_id, receiver_id, amount, type) VALUES ('$manager_id', '$staff_id', '$amount', 'cash_deposit')")) {
                die("Transaction Log Failed (Check ENUM types): " . $conn->error);
            }

            echo "<script>alert('Successfully loaded ₹$amount to $staff_name'); window.location='manager_dashboard.php';</script>";
        } elseif ($action == 'collect') {
            if ($current_balance >= $amount) {
                $new_balance = $current_balance - $amount;

                if (!$conn->query("UPDATE users SET balance = '$new_balance' WHERE id='$staff_id'")) {
                    die("Update Failed: " . $conn->error);
                }

                // FIX: Sender = Staff ($staff_id), Receiver = Manager ($manager_id)
                // This prevents 'receiver_id cannot be null' error
                if (!$conn->query("INSERT INTO transactions (sender_id, receiver_id, amount, type) VALUES ('$staff_id', '$manager_id', '$amount', 'cash_withdrawal')")) {
                    die("Transaction Log Failed (Check ENUM types): " . $conn->error);
                }

                echo "<script>alert('Successfully collected ₹$amount from $staff_name'); window.location='manager_dashboard.php';</script>";
            } else {
                echo "<script>alert('Insufficient float balance with staff!'); window.location='manager_dashboard.php';</script>";
            }
        } else {
            echo "<script>alert('Invalid Action Selected'); window.location='manager_dashboard.php';</script>";
        }
    } else {
        echo "<script>alert('Staff member not found!'); window.location='manager_dashboard.php';</script>";
    }
}


// 5. CREATE FD (FIXED)
if (isset($_POST['create_fd'])) {
    $uid = $_SESSION['user_id'];
    $amount = $_POST['amount'];
    $user = $conn->query("SELECT balance FROM users WHERE id='$uid'")->fetch_assoc();
    if ($user['balance'] >= $amount) {
        $conn->query("UPDATE users SET balance = balance - $amount WHERE id='$uid'");
        $conn->query("INSERT INTO deposits (user_id, type, amount) VALUES ('$uid', 'FD', '$amount')");
        echo "<script>alert('Fixed Deposit Created!'); window.location='consumer_dashboard.php';</script>";
    } else {
        echo "<script>alert('Insufficient Balance for FD'); window.location='consumer_dashboard.php';</script>";
    }
}

// 6. UPDATE PROFILE
if (isset($_POST['update_profile'])) {
    $uid = $_SESSION['user_id'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $education = $_POST['qualification'];
    $photo_sql = "";
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['name'] != "") {
        $target_dir = "uploads/";
        if (!is_dir($target_dir))
            mkdir($target_dir);
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

// 7. LOANS
if (isset($_POST['apply_loan'])) {
    $uid = $_SESSION['user_id'];
    $type = $_POST['loan_type'];
    $amount = $_POST['amount'];
    $justification = $_POST['justification'];
    $sql = "INSERT INTO loans (user_id, loan_type, amount, justification, status) VALUES ('$uid', '$type', '$amount', '$justification', 'pending')";
    if ($conn->query($sql)) {
        echo "<script>alert('Loan Applied!'); window.location='consumer_dashboard.php';</script>";
    } else {
        die("Error Inserting Loan: " . $conn->error);
    }
}

// 8. APPROVALS
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
if (isset($_GET['reject_request'])) {
    if ($_SESSION['role'] == 'consumer')
        die("Access Denied");
    $req_id = $_GET['id'];
    $type = $_GET['type'];
    $uid = $_GET['uid'];
    $conn->query("UPDATE requests SET status='rejected' WHERE id='$req_id'");
    if ($type == 'account_activation')
        $conn->query("UPDATE users SET status='rejected' WHERE id='$uid'");
    header("Location: manager_dashboard.php?msg=Rejected");
}
if (isset($_POST['request_service'])) {
    $uid = $_SESSION['user_id'];
    $type = $_POST['service_type'];
    $conn->query("INSERT INTO requests (user_id, request_type) VALUES ('$uid', '$type')");
    header("Location: consumer_dashboard.php?msg=ServiceRequested");
}
if (isset($_GET['approve_loan'])) {
    if ($_SESSION['role'] != 'manager')
        die("Access Denied");
    $id = $_GET['id'];
    $loan = $conn->query("SELECT * FROM loans WHERE id='$id'")->fetch_assoc();
    $amount = $loan['amount'];
    $uid = $loan['user_id'];
    $conn->query("UPDATE loans SET status='approved' WHERE id='$id'");
    $conn->query("UPDATE users SET balance = balance + $amount WHERE id='$uid'");
    header("Location: manager_dashboard.php?msg=LoanApproved");
}
if (isset($_GET['reject_loan'])) {
    if ($_SESSION['role'] != 'manager')
        die("Access Denied");
    $id = $_GET['id'];
    $conn->query("UPDATE loans SET status='rejected' WHERE id='$id'");
    header("Location: manager_dashboard.php?msg=LoanRejected");
}
?>