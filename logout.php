<?php
/**
 * Session Logout Handler
 * Student Management System
 */

require_once __DIR__ . '/includes/auth.php';

// Unset all session variables
$_SESSION = array();

// Destroy session cookie if set
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Restart session just to pass flash notification to login page
session_start();
set_flash('info', 'You have been logged out successfully.');
redirect('login.php');
