<?php
/**
 * Admin Authorization Guard
 * Protects all /admin/ pages
 */

require_once __DIR__ . '/auth.php';

if (!isLoggedIn()) {
    set_flash('danger', 'Please login with an administrator account.');
    redirect('/login.php');
}

if (!isAdmin()) {
    set_flash('danger', 'Access denied. Administrator privileges are required to view that page.');
    if (isStudent()) {
        redirect('/student/dashboard.php');
    } else {
        redirect('/login.php');
    }
}
