<?php
require_once '../config.php';
checkRole(ROLE_CUSTOMER);

$user = $_SESSION['user'] ?? null;
$userId = (int) $user['id'];

$message = '';
$error = '';

// Simple loan types and tenure options (you can move these to DB)
$loanTypes = ['personal', 'home', 'auto', 'education', 'business'];
$tenureOptions = [12, 24, 36, 60, 120]; // in months

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loan_type = sanitizeInput(post('loan_type'));
    $amount = (float) post('amount');
    $tenure = (int) post('tenure');
    $purpose = sanitizeInput(post('purpose'));
    $income = (float) post('income');
    $employer = sanitizeInput(post('employer'));
    $notes = sanitizeInput(post('notes'));

    $errors = [];

    if (!in_array($loan_type, $loanTypes, true)) {
        $errors[] = 'Please select a valid loan type.';
    }
    if ($amount <= 0) {
        $errors[] = 'Please enter a valid loan amount.';
    }
    if (!in_array($tenure, $tenureOptions, true)) {
        $errors[] = 'Please select a valid tenure.';
    }
    if ($purpose === '') {
        $errors[] = 'Please describe the purpose of the loan.';
    }
    if ($income <= 0) {
        $errors[] = 'Please enter your approximate monthly income.';
    }

    if (!$errors) {
        $stmt = $conn->prepare("
            INSERT INTO loans
                (user_id, loan_type, amount, tenure_months, purpose, monthly_income, employer, status, notes, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())
        ");
        $stmt->bind_param(
            'isdissss',
            $userId,
            $loan_type,
            $amount,
            $tenure,
            $purpose,
            $income,
            $employer,
            $notes
        );

        if ($stmt->execute()) {
            $message = 'Your loan request has been submitted and is pending review.';
            logActivity(
                $conn,
                $userId,
                'customer_loan_request',
                "Customer applied for {$loan_type} loan amount {$amount}"
            );
        } else {
            $error = 'Failed to submit loan request. Please try again later.';
        }
        $stmt->close();
    } else {
        $error = implode(' ', $errors);
    }
}

// Fetch recent loan requests by this customer
$recentLoans = fetchAll(
    $conn,
    "SELECT * FROM loans
     WHERE user_id = ?
     ORDER BY created_at DESC
     LIMIT 5",
    'i',
    [$userId]
);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <title>Loan Request - SecureBank</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg-primary, #131a1a);
            color: var(--text-primary, #e0e0e0);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .page {
            min-height: 100vh;
        }

        .content {
            max-width: 900px;
            margin: 20px auto 30px;
            padding: 0 18px 22px;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 4px;
        }

        .subtitle {
            font-size: 12px;
            color: var(--text-secondary, #c0c0c0);
            margin-bottom: 14px;
        }

        .alert {
            border-radius: 8px;
            padding: 8px 10px;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .alert-success {
            background: rgba(46, 184, 111, 0.12);
            color: var(--success, #2eb86f);
            border: 1px solid rgba(46, 184, 111, 0.3);
        }

        .alert-danger {
            background: rgba(201, 31, 47, 0.12);
            color: var(--danger, #c91f2f);
            border: 1px solid rgba(201, 31, 47, 0.3);
        }

        .card {
            background: var(--bg-secondary, #181f20);
            border-radius: 12px;
            padding: 16px 14px 14px;
            border: 1px solid var(--border-color, #2a3334);
            margin-bottom: 14px;
        }

        .card-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .card-subtitle {
            font-size: 12px;
            color: var(--text-secondary, #c0c0c0);
            margin-bottom: 10px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px 14px;
        }

        .form-group {
            font-size: 13px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        label {
            font-size: 13px;
        }

        select,
        input[type="number"],
        textarea {
            width: 100%;
            padding: 8px 9px;
            border-radius: 8px;
            border: 1px solid var(--border-color, #2a3334);
            background: var(--bg-primary, #131a1a);
            color: var(--text-primary, #e0e0e0);
            font-size: 13px;
        }

        textarea {
            min-height: 70px;
            resize: vertical;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary, #2d5f82);
            box-shadow: 0 0 0 1px rgba(45, 95, 130, 0.3);
        }

        .hint {
            font-size: 11px;
            color: var(--text-secondary, #c0c0c0);
        }

        .btn-primary {
            border-radius: 999px;
            padding: 9px 18px;
            border: none;
            background: var(--primary, #2d5f82);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-primary:hover {
            background: var(--secondary, #8b6f47);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        th,
        td {
            padding: 6px 4px;
            border-bottom: 1px solid var(--border-color, #2a3334);
            text-align: left;
        }

        th {
            font-weight: 600;
            color: var(--text-secondary, #c0c0c0);
        }

        tr:last-child td {
            border-bottom: none;
        }

        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: 11px;
        }

        .badge-pending {
            background: rgba(244, 165, 66, 0.18);
            color: var(--warning, #f4a542);
        }

        .badge-approved {
            background: rgba(46, 184, 111, 0.18);
            color: var(--success, #2eb86f);
        }

        .badge-rejected {
            background: rgba(201, 31, 47, 0.18);
            color: var(--danger, #c91f2f);
        }

        .info-small {
            font-size: 11px;
            color: var(--text-secondary, #c0c0c0);
        }
    </style>
</head>

<body>
    <div class="page">
        <?php include 'header.php'; ?>

        <div class="content">
            <h1>Loan Request</h1>
            <p class="subtitle">
                Apply for a new loan by specifying amount, tenure and purpose. The bank will review your request and
                contact you with the decision.[web:512][web:272]
            </p>

            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form method="post" novalidate>
                <div class="card">
                    <div class="card-title">Loan Details</div>
                    <div class="card-subtitle">
                        Select the type of loan you need and key financial details.[web:513]
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="loan_type">Loan Type *</label>
                            <select id="loan_type" name="loan_type" required>
                                <option value="">Select type</option>
                                <option value="personal">Personal Loan</option>
                                <option value="home">Home Loan</option>
                                <option value="auto">Auto/Vehicle Loan</option>
                                <option value="education">Education Loan</option>
                                <option value="business">Business Loan</option>
                            </select>
                            <div class="hint">Different loan schemes may have different eligibility and rates.[web:512]
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="amount">Loan Amount *</label>
                            <input type="number" id="amount" name="amount" min="1" step="0.01" required>
                            <div class="hint">Total amount you wish to borrow (e.g., 300000).[web:516]</div>
                        </div>
                        <div class="form-group">
                            <label for="tenure">Tenure (months) *</label>
                            <select id="tenure" name="tenure" required>
                                <option value="">Select tenure</option>
                                <?php foreach ($tenureOptions as $t): ?>
                                    <option value="<?php echo (int) $t; ?>"><?php echo (int) $t; ?> months</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="hint">Choose repayment period; EMI depends on tenure and interest rate.[web:273]
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="income">Monthly Income *</label>
                            <input type="number" id="income" name="income" min="1" step="0.01" required>
                            <div class="hint">Approximate net monthly income for eligibility assessment.[web:513]</div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-title">Purpose & Employment</div>
                    <div class="card-subtitle">
                        Provide details on how the loan will be used and your employment.[web:513]
                    </div>
                    <div class="form-grid">
                        <div class="form-group" style="grid-column:1/-1;">
                            <label for="purpose">Purpose of Loan *</label>
                            <textarea id="purpose" name="purpose" required
                                placeholder="Example: Purchase of house, medical expenses, education fee, business expansion, etc."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="employer">Employer / Business Name</label>
                            <input type="text" id="employer" name="employer" placeholder="Company or business name">
                            <div class="hint">Optional, but helps lender evaluate your profile.[web:513]</div>
                        </div>
                        <div class="form-group">
                            <label for="notes">Additional Notes (optional)</label>
                            <textarea id="notes" name="notes"
                                placeholder="Any extra information or existing relationship with the bank."></textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary">Submit Loan Request</button>
                <div class="info-small" style="margin-top:8px;">
                    You may be asked to upload supporting documents separately; track status from your loan requests
                    section.[web:272][web:518]
                </div>
            </form>

            <div class="card" style="margin-top:18px;">
                <div class="card-title">Recent Loan Requests</div>
                <div class="card-subtitle">
                    Latest loan applications you submitted through internet banking.[web:273]
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Tenure</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentLoans)): ?>
                            <tr>
                                <td colspan="5">No loan requests found.[web:380]</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentLoans as $r): ?>
                                <tr>
                                    <td><?php echo formatDate($r['created_at']); ?></td>
                                    <td class="info-small">
                                        <?php echo htmlspecialchars(ucfirst($r['loan_type']), ENT_QUOTES, 'UTF-8'); ?>
                                    </td>
                                    <td><?php echo formatCurrency((float) $r['amount']); ?></td>
                                    <td class="info-small"><?php echo (int) $r['tenure_months']; ?> months</td>
                                    <td>
                                        <?php
                                        $badgeClass = 'badge-pending';
                                        if ($r['status'] === 'approved')
                                            $badgeClass = 'badge-approved';
                                        if ($r['status'] === 'rejected')
                                            $badgeClass = 'badge-rejected';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>">
                                            <?php echo htmlspecialchars($r['status'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>