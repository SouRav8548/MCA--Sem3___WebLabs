<!DOCTYPE html>
<html lang="en">

<head>
    <title>Registration</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
    <style>
        .form-section {
            border: 1px solid var(--accent);
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .form-section legend {
            color: var(--text-primary);
            font-weight: bold;
            padding: 0 10px;
        }
    </style>
</head>

<body>
    <div class="container" style="margin-top: 20px;">
        <div class="card">
            <h2>New Account Application</h2>
            <form action="actions.php" method="POST">
                <input type="hidden" name="role" value="<?php echo $_GET['role'] ?? 'consumer'; ?>">

                <fieldset class="form-section">
                    <legend>Personal Information</legend>
                    <input type="text" name="name" placeholder="Full Name" required>
                    <input type="date" name="dob" placeholder="Date of Birth" required title="Date of Birth">
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </fieldset>

                <fieldset class="form-section">
                    <legend>Communication Details</legend>
                    <input type="email" name="email" placeholder="Email Address" required>
                    <input type="text" name="phone" placeholder="Mobile Number" required>
                    <textarea name="address" placeholder="Residential Address" rows="2" required></textarea>
                </fieldset>

                <fieldset class="form-section">
                    <legend>Identity Proof</legend>
                    <input type="text" name="pan_number" placeholder="PAN Card Number" required>
                    <input type="text" name="aadhaar_number" placeholder="Aadhaar Number" required>
                </fieldset>

                <fieldset class="form-section">
                    <legend>Education & Security</legend>
                    <input type="text" name="qualification" placeholder="Highest Qualification" required>
                    <input type="password" name="password" placeholder="Create Password" required>
                </fieldset>

                <button type="submit" name="register" class="btn" style="width:100%; font-size: 1.2rem;">Submit
                    Application</button>
            </form>
            <br>
            <a href="index.php" style="color:var(--text-secondary)">Back to Login</a>
        </div>
    </div>
</body>

</html>