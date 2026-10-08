<?php
/**
 * Database Configuration & Global Utility Functions
 * Student Management System
 */

// Database credentials - Update if using different XAMPP/WAMP settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'student_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Upload directory configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/aadhaar/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB

// Google OAuth Client ID Configuration
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com');

// Real SMS Gateway API Configuration (Fast2SMS / Twilio)
define('FAST2SMS_API_KEY', ''); // Enter your Fast2SMS API Key for real SMS delivery in India
define('TWILIO_ACCOUNT_SID', ''); // Or enter Twilio Account SID
define('TWILIO_AUTH_TOKEN', '');  // Twilio Auth Token
define('TWILIO_PHONE_NUMBER', ''); // Twilio Sender Phone Number

/**
 * Get PDO Database Connection
 * @return PDO
 */
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            autoSeedAdmin($pdo);
        } catch (\PDOException $e) {
            try {
                $dsn_no_db = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
                $pdo_no_db = new PDO($dsn_no_db, DB_USER, DB_PASS, $options);
                $pdo_no_db->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                autoSeedTablesAndAdmin($pdo);
            } catch (\PDOException $ex) {
                die("Database Connection Error: " . $ex->getMessage());
            }
        }
    }
    return $pdo;
}

/**
 * Automatically creates tables and seeds Admin if not present
 */
function autoSeedTablesAndAdmin($pdo) {
    $tableSql = "
    CREATE TABLE IF NOT EXISTS `students` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `phone_number` VARCHAR(20) NULL,
        `password` VARCHAR(255) NOT NULL,
        `role` ENUM('admin', 'student') NOT NULL DEFAULT 'student',
        `date_of_birth` DATE NULL,
        `gender` ENUM('Male', 'Female', 'Other') NULL,
        `qualification` VARCHAR(50) NULL,
        `interests` TEXT NULL,
        `class` VARCHAR(50) NULL,
        `subject` VARCHAR(100) NULL,
        `marks` DECIMAL(5,2) NULL,
        `aadhaar_file` VARCHAR(255) NULL,
        `reset_token` VARCHAR(64) NULL,
        `reset_token_expires` DATETIME NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (`email`),
        INDEX (`role`),
        INDEX (`class`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $pdo->exec($tableSql);
    autoSeedAdmin($pdo);
}

/**
 * Ensures Admin Account exists in DB
 */
function autoSeedAdmin($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT id FROM students WHERE email = ?");
        $stmt->execute(['admin@example.com']);
        if (!$stmt->fetch()) {
            $hashedPassword = password_hash('Admin@123', PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO students (name, email, password, role, created_at) VALUES (?, ?, ?, 'admin', NOW())");
            $insert->execute(['Administrator', 'admin@example.com', $hashedPassword]);
        }
    } catch (\PDOException $e) {
        // Table handled by caller
    }
}

/**
 * Real SMS Dispatcher Function
 * Sends SMS via Fast2SMS or Twilio if API key is provided
 */
function sendRealSMS($mobileNumber, $otpCode) {
    $message = "Your EduPortal verification OTP code is: " . $otpCode . ". Valid for 10 minutes.";
    
    // 1. Fast2SMS API (Popular in India for direct mobile SMS)
    if (defined('FAST2SMS_API_KEY') && !empty(FAST2SMS_API_KEY)) {
        $url = "https://www.fast2sms.com/dev/bulkV2";
        $fields = [
            "variables_values" => $otpCode,
            "route" => "otp",
            "numbers" => $mobileNumber
        ];
        $headers = [
            "authorization: " . FAST2SMS_API_KEY,
            "Content-Type: application/json"
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return true;
    }

    // 2. Twilio API
    if (defined('TWILIO_ACCOUNT_SID') && !empty(TWILIO_ACCOUNT_SID) && !empty(TWILIO_AUTH_TOKEN)) {
        $url = "https://api.twilio.com/2010-04-01/Accounts/" . TWILIO_ACCOUNT_SID . "/Messages.json";
        $data = [
            'From' => TWILIO_PHONE_NUMBER,
            'To' => '+91' . $mobileNumber,
            'Body' => $message
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_USERPWD, TWILIO_ACCOUNT_SID . ":" . TWILIO_AUTH_TOKEN);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        return true;
    }

    return true;
}

/**
 * Output Escaping for XSS Protection
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Input Data Sanitization
 */
function sanitizeInput($data) {
    return trim(stripslashes($data ?? ''));
}

/**
 * Calculate Age from Date of Birth
 */
function calculateAge($dob) {
    if (empty($dob)) return 0;
    try {
        $birthDate = new DateTime($dob);
        $today = new DateTime('today');
        return $birthDate->diff($today)->y;
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * CSRF Token Generator & Validator
 */
function generateCSRFToken() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

/**
 * Flash Notification Messages
 */
function set_flash($type, $message) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

function get_flash() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Aadhaar PDF Upload Handler
 */
function validateAndProcessAadhaarUpload($file, $oldFilename = null) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['valid' => false, 'error' => 'Please upload a valid PDF file.'];
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['valid' => false, 'error' => 'File size exceeds 5 MB limit.'];
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        return ['valid' => false, 'error' => 'Please upload a valid PDF file.'];
    }
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    $newFilename = 'student_' . time() . '_' . bin2hex(random_bytes(6)) . '.pdf';
    $targetPath = UPLOAD_DIR . $newFilename;
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        if (!empty($oldFilename) && file_exists(UPLOAD_DIR . $oldFilename)) {
            @unlink(UPLOAD_DIR . $oldFilename);
        }
        return ['valid' => true, 'filename' => $newFilename];
    }
    return ['valid' => false, 'error' => 'Failed to save uploaded file.'];
}
