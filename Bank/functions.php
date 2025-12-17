<?php
/**
 * Common functions for SecureBank
 * - Flash message class
 * - Authentication helpers
 * - Validation & sanitization
 * - Password hashing
 * - Database helpers
 * - Utility functions
 */

/* =========================
   FLASH MESSAGE CLASS
   ========================= */
class Message
{
    const KEY_SUCCESS = 'flash_success';
    const KEY_ERROR = 'flash_error';
    const KEY_INFO = 'flash_info';

    public static function addSuccess(string $msg): void
    {
        $_SESSION[self::KEY_SUCCESS][] = $msg;
    }

    public static function addError(string $msg): void
    {
        $_SESSION[self::KEY_ERROR][] = $msg;
    }

    public static function addInfo(string $msg): void
    {
        $_SESSION[self::KEY_INFO][] = $msg;
    }

    public static function displaySuccess(): void
    {
        if (!empty($_SESSION[self::KEY_SUCCESS])) {
            echo '<div class="alert alert-success">';
            foreach ($_SESSION[self::KEY_SUCCESS] as $msg) {
                echo '<p>' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            echo '</div>';
            unset($_SESSION[self::KEY_SUCCESS]);
        }
    }

    public static function displayErrors(): void
    {
        if (!empty($_SESSION[self::KEY_ERROR])) {
            echo '<div class="alert alert-danger">';
            foreach ($_SESSION[self::KEY_ERROR] as $msg) {
                echo '<p>' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            echo '</div>';
            unset($_SESSION[self::KEY_ERROR]);
        }
    }

    public static function displayInfo(): void
    {
        if (!empty($_SESSION[self::KEY_INFO])) {
            echo '<div class="alert alert-info">';
            foreach ($_SESSION[self::KEY_INFO] as $msg) {
                echo '<p>' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            echo '</div>';
            unset($_SESSION[self::KEY_INFO]);
        }
    }
}

/* =========================
   AUTHENTICATION HELPERS
   ========================= */

function isLoggedIn(): bool
{
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

function checkLogin(): void
{
    if (!isLoggedIn()) {
        Message::addError('Please login to continue.');
        header('Location: ' . url('login.php'));
        exit;
    }
}

/**
 * Require a specific role
 */
function checkRole(string $role): void
{
    checkLogin();
    if (empty($_SESSION['user']['role']) || $_SESSION['user']['role'] !== $role) {
        http_response_code(403);
        Message::addError('You are not authorized to access this page.');
        header('Location: ' . url('login.php'));
        exit;
    }
}

/**
 * Require any of multiple roles
 */
function checkAnyRole(array $roles): void
{
    checkLogin();
    $userRole = $_SESSION['user']['role'] ?? '';
    if (!in_array($userRole, $roles, true)) {
        http_response_code(403);
        Message::addError('You are not authorized to access this page.');
        header('Location: ' . url('login.php'));
        exit;
    }
}

/* =========================
   INPUT SANITIZATION
   ========================= */

function sanitizeInput(?string $value): string
{
    $value = $value ?? '';
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function validateEmail(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone(string $phone): bool
{
    // 10-digit numeric Indian phone format
    return (bool) preg_match('/^[0-9]{10}$/', $phone);
}

function validateAadhar(string $aadhar): bool
{
    return (bool) preg_match('/^[0-9]{12}$/', $aadhar);
}

function validatePAN(string $pan): bool
{
    // Basic PAN format: 5 letters, 4 digits, 1 letter
    return (bool) preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', strtoupper($pan));
}

/* =========================
   PASSWORD UTILITIES
   ========================= */

function hashPassword(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT);
}

function verifyPassword(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}

/* =========================
   DATABASE HELPERS
   ========================= */

/**
 * Execute prepared SELECT with params and return mysqli_result
 */
function executeQuery(mysqli $conn, string $sql, string $types = '', array $params = []): mysqli_result
{
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Prepare failed: ' . $conn->error);
    }

    if ($types !== '' && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    if (!$stmt->execute()) {
        throw new RuntimeException('Execute failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

/**
 * Return all rows as array
 */
function getResultAsArray(mysqli_result $result): array
{
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Shortcut: run query and get all rows
 */
function fetchAll(mysqli $conn, string $sql, string $types = '', array $params = []): array
{
    $res = executeQuery($conn, $sql, $types, $params);
    return getResultAsArray($res);
}

/**
 * Shortcut: run query and get single row
 */
function fetchOne(mysqli $conn, string $sql, string $types = '', array $params = []): ?array
{
    $res = executeQuery($conn, $sql, $types, $params);
    $row = $res->fetch_assoc();
    return $row ?: null;
}

/* =========================
   ACTIVITY LOGGING
   ========================= */

function logActivity(mysqli $conn, int $userId, string $action, string $details = ''): void
{
    $sql = "INSERT INTO activity_log (user_id, action, details, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return;
    }
    $stmt->bind_param('iss', $userId, $action, $details);
    $stmt->execute();
    $stmt->close();
}

/* =========================
   UTILITY FUNCTIONS
   ========================= */

function formatCurrency(float $amount): string
{
    return CURRENCY_SYMBOL . number_format($amount, 2);
}

function formatDate(?string $datetime): string
{
    if (!$datetime) {
        return '';
    }
    $ts = strtotime($datetime);
    return $ts ? date('d M Y, h:i A', $ts) : $datetime;
}

/**
 * Helper to safely get POST value
 */
function post(string $key, $default = '')
{
    return $_POST[$key] ?? $default;
}

/**
 * Helper to safely get GET value
 */
function get(string $key, $default = '')
{
    return $_GET[$key] ?? $default;
}
