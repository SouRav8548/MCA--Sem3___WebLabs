<?php
/**
 * Global configuration for SecureBank
 * - Database connection
 * - Session setup
 * - Global constants (roles, statuses, paths)
 * - Auto-include of functions.php
 */

/* =========================
   ERROR REPORTING
   ========================= */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* =========================
   BASE PATHS
   ========================= */
define('BASE_PATH', __DIR__);
define('BASE_URL', '/banking_system'); // adjust if your folder name/virtual host differs

/* =========================
   DATABASE CONFIG
   ========================= */
// XAMPP defaults
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'banking_system');

// Create MySQLi connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Ensure UTF-8
$conn->set_charset('utf8mb4');

/* =========================
   SESSION CONFIG
   ========================= */
if (session_status() === PHP_SESSION_NONE) {
    // 30 minutes session lifetime
    ini_set('session.gc_maxlifetime', 1800);
    session_set_cookie_params([
        'lifetime' => 1800,
        'path' => '/',
        'secure' => false,   // set true on HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Simple timeout handling
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    // last request was more than 30 minutes ago
    session_unset();
    session_destroy();
}
$_SESSION['LAST_ACTIVITY'] = time();

/* =========================
   ROLE CONSTANTS
   ========================= */
define('ROLE_ADMIN', 'admin');
define('ROLE_MANAGER', 'manager');
define('ROLE_STAFF', 'staff');
define('ROLE_CUSTOMER', 'customer');

/* =========================
   UPLOAD PATHS
   ========================= */
define('UPLOAD_BASE', BASE_PATH . '/uploads');
define('UPLOAD_DOCUMENTS', UPLOAD_BASE . '/documents');
define('UPLOAD_KYC', UPLOAD_BASE . '/kyc');
define('UPLOAD_LOANS', UPLOAD_BASE . '/loans');
define('UPLOAD_CARDS', UPLOAD_BASE . '/cards');
define('UPLOAD_CHEQUES', UPLOAD_BASE . '/cheques');

// Ensure upload directories exist (optional but handy in dev)
$uploadDirs = [
    UPLOAD_BASE,
    UPLOAD_DOCUMENTS,
    UPLOAD_KYC,
    UPLOAD_LOANS,
    UPLOAD_CARDS,
    UPLOAD_CHEQUES,
];

foreach ($uploadDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}

/* =========================
   GENERAL CONSTANTS
   ========================= */
// Currency
define('CURRENCY_SYMBOL', '₹');
define('CURRENCY_CODE', 'INR');

// Account statuses
define('ACCOUNT_STATUS_ACTIVE', 'active');
define('ACCOUNT_STATUS_PENDING', 'pending');
define('ACCOUNT_STATUS_CLOSED', 'closed');

// Loan statuses
define('LOAN_STATUS_PENDING', 'pending');
define('LOAN_STATUS_APPROVED', 'approved');
define('LOAN_STATUS_REJECTED', 'rejected');
define('LOAN_STATUS_CLOSED', 'closed');

// Request types
define('REQUEST_TYPE_ATM', 'atm_card');
define('REQUEST_TYPE_CHEQUE', 'cheque_book');

/* =========================
   AUTO INCLUDE FUNCTIONS
   ========================= */
require_once BASE_PATH . '/functions.php';

/* =========================
   HELPER: BASE URL BUILDER
   ========================= */
function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return BASE_URL . '/' . $path;
}
