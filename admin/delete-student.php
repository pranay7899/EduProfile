<?php
/**
 * Admin Delete Student POST Handler
 * Student Management System
 */

require_once __DIR__ . '/../includes/admin-auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('danger', 'Invalid request method for deletion.');
    redirect('/admin/dashboard.php');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    set_flash('danger', 'Invalid security token for delete operation.');
    redirect('/admin/dashboard.php');
}

$studentId = $_POST['student_id'] ?? null;

if (!$studentId || !is_numeric($studentId)) {
    set_flash('danger', 'Invalid student ID specified.');
    redirect('/admin/dashboard.php');
}

$pdo = getDB();

// Fetch student to retrieve aadhaar file before deleting record
$stmt = $pdo->prepare("SELECT id, aadhaar_file FROM students WHERE id = ? AND role = 'student'");
$stmt->execute([$studentId]);
$student = $stmt->fetch();

if (!$student) {
    set_flash('danger', 'Student record not found or already deleted.');
    redirect('/admin/dashboard.php');
}

// 1. Delete student record from MySQL
$deleteStmt = $pdo->prepare("DELETE FROM students WHERE id = ? AND role = 'student'");
$deleteStmt->execute([$studentId]);

// 2. Delete student's Aadhaar file from server if it exists
if (!empty($student['aadhaar_file'])) {
    $filePath = UPLOAD_DIR . $student['aadhaar_file'];
    if (file_exists($filePath)) {
        @unlink($filePath);
    }
}

// 3. EXACT REQUIRED SUCCESS MESSAGE
set_flash('success', 'Student deleted successfully.');
redirect('/admin/dashboard.php');
