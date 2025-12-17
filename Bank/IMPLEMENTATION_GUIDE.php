<?php
/**
 * BANKING SYSTEM - IMPLEMENTATION GUIDE
 * 
 * This file contains sample implementations for key features
 * and can be used as a reference for building other modules
 */

// ============================================
// 1. SAMPLE CUSTOMER DASHBOARD IMPLEMENTATION
// ============================================

class CustomerDashboard {
    private $conn;
    private $user_id;
    
    public function __construct($conn, $user_id) {
        $this->conn = $conn;
        $this->user_id = $user_id;
    }
    
    /**
     * Get dashboard overview data
     */
    public function getDashboardData() {
        $data = [];
        
        // Total balance across all accounts
        $stmt = $this->conn->prepare("
            SELECT 
                COUNT(*) as total_accounts,
                SUM(balance) as total_balance
            FROM bank_accounts 
            WHERE user_id = ? AND status = 'active'
        ");
        $stmt->bind_param("i", $this->user_id);
        $stmt->execute();
        $data['accounts'] = $stmt->get_result()->fetch_assoc();
        
        // Pending requests
        $stmt = $this->conn->prepare("
            SELECT 
                COUNT(*) as pending_loans,
                (SELECT COUNT(*) FROM cheque_book_requests 
                 WHERE user_id = ? AND status = 'pending') as pending_cheques,
                (SELECT COUNT(*) FROM atm_card_requests 
                 WHERE user_id = ? AND status = 'pending') as pending_cards
            FROM loans 
            WHERE user_id = ? AND status = 'pending'
        ");
        $stmt->bind_param("iii", $this->user_id, $this->user_id, $this->user_id);
        $stmt->execute();
        $data['pending'] = $stmt->get_result()->fetch_assoc();
        
        // Recent transactions
        $stmt = $this->conn->prepare("
            SELECT t.*, 
                   fa.account_number as from_account,
                   ta.account_number as to_account
            FROM transactions t
            LEFT JOIN bank_accounts fa ON t.from_account_id = fa.id
            LEFT JOIN bank_accounts ta ON t.to_account_id = ta.id
            WHERE t.from_account_id IN 
                (SELECT id FROM bank_accounts WHERE user_id = ?)
            OR t.to_account_id IN 
                (SELECT id FROM bank_accounts WHERE user_id = ?)
            ORDER BY t.created_at DESC
            LIMIT 5
        ");
        $stmt->bind_param("ii", $this->user_id, $this->user_id);
        $stmt->execute();
        $data['recent_transactions'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        return $data;
    }
}

// ============================================
// 2. LOAN MANAGEMENT IMPLEMENTATION
// ============================================

class LoanManager {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Create a new loan application
     */
    public function createLoanApplication(
        $user_id, 
        $account_id, 
        $loan_type, 
        $requested_amount, 
        $purpose, 
        $documents
    ) {
        // Validate loan type
        if (!isset(LOAN_TYPES[$loan_type])) {
            return ['success' => false, 'message' => 'Invalid loan type'];
        }
        
        // Check user has active account
        $stmt = $this->conn->prepare("
            SELECT id FROM bank_accounts 
            WHERE id = ? AND user_id = ? AND status = 'active'
        ");
        $stmt->bind_param("ii", $account_id, $user_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows === 0) {
            return ['success' => false, 'message' => 'Invalid account selected'];
        }
        
        // Create loan
        $status = 'pending';
        $stmt = $this->conn->prepare("
            INSERT INTO loans 
            (user_id, account_id, loan_type, requested_amount, purpose, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iisdss", $user_id, $account_id, $loan_type, $requested_amount, $purpose, $status);
        
        if ($stmt->execute()) {
            $loan_id = $this->conn->insert_id;
            
            // Upload documents
            foreach ($documents as $doc_name => $file) {
                $this->uploadLoanDocument($loan_id, $doc_name, $file);
            }
            
            return [
                'success' => true, 
                'message' => 'Loan application submitted',
                'loan_id' => $loan_id
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to create loan'];
    }
    
    /**
     * Upload loan document
     */
    private function uploadLoanDocument($loan_id, $doc_name, $file) {
        $upload_path = UPLOAD_PATH . 'loans/';
        
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        
        $filename = uniqid() . '_' . basename($file['name']);
        $target_file = $upload_path . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $doc_path = 'loans/' . $filename;
            
            $stmt = $this->conn->prepare("
                INSERT INTO loan_documents (loan_id, document_name, document_path, document_type)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("isss", $loan_id, $doc_name, $doc_path, $doc_name);
            $stmt->execute();
        }
    }
    
    /**
     * Get loan details
     */
    public function getLoanDetails($loan_id) {
        $stmt = $this->conn->prepare("
            SELECT l.*, u.full_name, ba.account_number
            FROM loans l
            JOIN users u ON l.user_id = u.id
            JOIN bank_accounts ba ON l.account_id = ba.id
            WHERE l.id = ?
        ");
        $stmt->bind_param("i", $loan_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * Approve loan (Manager)
     */
    public function approveLoan($loan_id, $approved_amount, $interest_rate, $tenure_months, $remarks, $manager_id) {
        $status = 'approved';
        
        $stmt = $this->conn->prepare("
            UPDATE loans 
            SET approved_amount = ?, 
                interest_rate = ?, 
                tenure_months = ?, 
                status = ?, 
                approval_remarks = ?, 
                approved_by = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->bind_param("ddissii", $approved_amount, $interest_rate, $tenure_months, $status, $remarks, $manager_id, $loan_id);
        
        return $stmt->execute();
    }
}

// ============================================
// 3. FIXED DEPOSIT IMPLEMENTATION
// ============================================

class FixedDepositManager {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Create fixed deposit
     */
    public function createFixedDeposit($user_id, $account_id, $principal, $tenure_months) {
        // Get interest rate
        $rate = $this->getInterestRate($tenure_months);
        
        if ($rate === null) {
            return ['success' => false, 'message' => 'Invalid tenure selected'];
        }
        
        // Calculate maturity date
        $maturity_date = date('Y-m-d', strtotime("+$tenure_months months"));
        
        // Create FD
        $status = 'active';
        $stmt = $this->conn->prepare("
            INSERT INTO fixed_deposits 
            (user_id, account_id, principal_amount, interest_rate, tenure_months, maturity_date, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iiddsss", $user_id, $account_id, $principal, $rate, $tenure_months, $maturity_date, $status);
        
        if ($stmt->execute()) {
            $maturity_amount = $principal + $this->calculateInterest($principal, $rate, $tenure_months);
            
            return [
                'success' => true,
                'message' => 'Fixed Deposit created successfully',
                'fd_id' => $this->conn->insert_id,
                'maturity_amount' => $maturity_amount,
                'maturity_date' => $maturity_date
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to create fixed deposit'];
    }
    
    /**
     * Get interest rate for tenure
     */
    private function getInterestRate($months) {
        if ($months == 12) return 6.5;
        if ($months == 24) return 7.0;
        if ($months == 36) return 7.25;
        return null;
    }
    
    /**
     * Calculate interest
     */
    private function calculateInterest($principal, $rate, $months) {
        return ($principal * $rate * ($months / 12)) / 100;
    }
}

// ============================================
// 4. REQUEST APPROVAL IMPLEMENTATION
// ============================================

class RequestManager {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Request ATM Card
     */
    public function requestATMCard($user_id, $account_id, $card_type) {
        $requested_date = date('Y-m-d');
        $status = 'pending';
        
        $stmt = $this->conn->prepare("
            INSERT INTO atm_card_requests 
            (user_id, account_id, card_type, requested_date, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iisss", $user_id, $account_id, $card_type, $requested_date, $status);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'ATM card request submitted'];
        }
        
        return ['success' => false, 'message' => 'Failed to submit request'];
    }
    
    /**
     * Request Cheque Book
     */
    public function requestChequeBook($user_id, $account_id, $number_of_leaves = 50) {
        $requested_date = date('Y-m-d');
        $status = 'pending';
        
        $stmt = $this->conn->prepare("
            INSERT INTO cheque_book_requests 
            (user_id, account_id, number_of_leaves, requested_date, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iisis", $user_id, $account_id, $number_of_leaves, $requested_date, $status);
        
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Cheque book request submitted'];
        }
        
        return ['success' => false, 'message' => 'Failed to submit request'];
    }
    
    /**
     * Approve request and generate PDF
     */
    public function approveRequest($request_id, $request_type, $remarks, $manager_id) {
        $approved_date = date('Y-m-d');
        $status = 'approved';
        
        if ($request_type === 'atm') {
            $table = 'atm_card_requests';
            $card_number = $this->generateCardNumber();
            
            // Generate PDF for ATM card
            $pdf_path = $this->generateATMCardPDF($request_id, $card_number);
            
            $stmt = $this->conn->prepare("
                UPDATE $table 
                SET status = ?, approved_date = ?, approval_remarks = ?, 
                    approved_by = ?, card_number = ?, card_pdf_path = ?
                WHERE id = ?
            ");
            $stmt->bind_param("sssssi", $status, $approved_date, $remarks, $manager_id, $card_number, $pdf_path, $request_id);
        } else {
            $table = 'cheque_book_requests';
            
            // Generate PDF for cheque book
            $pdf_path = $this->generateChequeBookPDF($request_id);
            
            $stmt = $this->conn->prepare("
                UPDATE $table 
                SET status = ?, approved_date = ?, approval_remarks = ?, 
                    approved_by = ?, cheque_pdf_path = ?
                WHERE id = ?
            ");
            $stmt->bind_param("sssssi", $status, $approved_date, $remarks, $manager_id, $pdf_path, $request_id);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Generate card number
     */
    private function generateCardNumber() {
        return substr(str_shuffle('0123456789'), 0, 4) . '-' .
               substr(str_shuffle('0123456789'), 0, 4) . '-' .
               substr(str_shuffle('0123456789'), 0, 4) . '-' .
               substr(str_shuffle('0123456789'), 0, 4);
    }
    
    /**
     * Generate ATM Card PDF
     */
    private function generateATMCardPDF($request_id, $card_number) {
        // Get request details
        $stmt = $this->conn->prepare("
            SELECT ar.*, u.full_name, u.email, ba.account_number
            FROM atm_card_requests ar
            JOIN users u ON ar.user_id = u.id
            JOIN bank_accounts ba ON ar.account_id = ba.id
            WHERE ar.id = ?
        ");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $request = $stmt->get_result()->fetch_assoc();
        
        // Create PDF content (simplified)
        $pdf_content = "
        ATM CARD APPROVAL
        
        Card Holder: {$request['full_name']}
        Email: {$request['email']}
        Card Number: $card_number
        Account: {$request['account_number']}
        Issued: " . date('Y-m-d') . "
        Valid Till: " . date('Y-m-d', strtotime('+5 years')) . "
        ";
        
        $pdf_path = 'cards/' . $request_id . '.pdf';
        
        // Create directory if not exists
        if (!is_dir(UPLOAD_PATH . 'cards')) {
            mkdir(UPLOAD_PATH . 'cards', 0755, true);
        }
        
        file_put_contents(UPLOAD_PATH . $pdf_path, $pdf_content);
        
        return $pdf_path;
    }
    
    /**
     * Generate Cheque Book PDF
     */
    private function generateChequeBookPDF($request_id) {
        // Get request details
        $stmt = $this->conn->prepare("
            SELECT cbr.*, u.full_name, u.email, ba.account_number
            FROM cheque_book_requests cbr
            JOIN users u ON cbr.user_id = u.id
            JOIN bank_accounts ba ON cbr.account_id = ba.id
            WHERE cbr.id = ?
        ");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $request = $stmt->get_result()->fetch_assoc();
        
        // Create PDF content (simplified)
        $pdf_content = "
        CHEQUE BOOK APPROVAL
        
        Cheque Book Holder: {$request['full_name']}
        Email: {$request['email']}
        Account: {$request['account_number']}
        Number of Leaves: {$request['number_of_leaves']}
        Issued: " . date('Y-m-d') . "
        
        Cheque Details:
        Serial Range: 000001 to " . str_pad($request['number_of_leaves'], 6, '0', STR_PAD_LEFT) . "
        ";
        
        $pdf_path = 'cheques/' . $request_id . '.pdf';
        
        // Create directory if not exists
        if (!is_dir(UPLOAD_PATH . 'cheques')) {
            mkdir(UPLOAD_PATH . 'cheques', 0755, true);
        }
        
        file_put_contents(UPLOAD_PATH . $pdf_path, $pdf_content);
        
        return $pdf_path;
    }
}

// ============================================
// 5. USAGE EXAMPLES IN PHP FILES
// ============================================

/**
 * In customer/dashboard.php:
 * 
 * require_once '../config.php';
 * checkLogin();
 * checkAnyRole(['customer']);
 * 
 * $dashboard = new CustomerDashboard($conn, $_SESSION['user_id']);
 * $data = $dashboard->getDashboardData();
 * 
 * // Display data in HTML
 */

/**
 * In customer/fixed-deposit.php:
 * 
 * if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 *     $fd_manager = new FixedDepositManager($conn);
 *     $result = $fd_manager->createFixedDeposit(
 *         $_SESSION['user_id'],
 *         $_POST['account_id'],
 *         $_POST['principal_amount'],
 *         $_POST['tenure_months']
 *     );
 *     
 *     if ($result['success']) {
 *         Message::addSuccess($result['message']);
 *     } else {
 *         Message::addError($result['message']);
 *     }
 * }
 */

/**
 * In customer/loan-request.php:
 * 
 * if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 *     $loan_manager = new LoanManager($conn);
 *     $result = $loan_manager->createLoanApplication(
 *         $_SESSION['user_id'],
 *         $_POST['account_id'],
 *         $_POST['loan_type'],
 *         $_POST['requested_amount'],
 *         $_POST['purpose'],
 *         $_FILES['documents'] ?? []
 *     );
 *     
 *     if ($result['success']) {
 *         header('Location: view-loans.php');
 *     }
 * }
 */

/**
 * In admin/approve-loans.php:
 * 
 * if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 *     $loan_manager = new LoanManager($conn);
 *     $success = $loan_manager->approveLoan(
 *         $_POST['loan_id'],
 *         $_POST['approved_amount'],
 *         $_POST['interest_rate'],
 *         $_POST['tenure_months'],
 *         $_POST['remarks'],
 *         $_SESSION['user_id']
 *     );
 *     
 *     if ($success) {
 *         Message::addSuccess('Loan approved successfully');
 *     }
 * }
 */

?>
