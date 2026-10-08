<?php
/**
 * Student Authorization Guard
 * Protects all /student/ pages
 */

require_once __DIR__ . '/auth.php';

if (!isLoggedIn()) {
    set_flash('danger', 'Please login to access your student account.');
    redirect('/login.php');
}

if (!isStudent()) {
    set_flash('danger', 'Access denied. Student authorization required.');
    if (isAdmin()) {
        redirect('/admin/dashboard.php');
    } else {
        redirect('/login.php');
    }
}
