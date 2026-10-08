<?php
/**
 * Global Header Component
 * Student Management System
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

$pageTitle = $pageTitle ?? 'Student Management System';
$currentUser = isset($_SESSION['user_id']) ? [
    'id' => $_SESSION['user_id'],
    'name' => $_SESSION['user_name'] ?? 'User',
    'role' => $_SESSION['role'] ?? ''
] : null;

// Helper to determine relative root path for clean URLs whether in root or admin/student subfolders
$basePath = (str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') || str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/student/')) ? '../' : './';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($pageTitle); ?> - Student Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="<?php echo $basePath; ?>assets/css/style.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm py-2 custom-navbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 font-outfit fw-bold" href="<?php echo $basePath; ?>index.php">
                <div class="brand-icon bg-primary rounded-3 d-flex align-items-center justify-content-center text-white">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <span>EduPortal<span class="text-primary">.</span></span>
            </a>
            
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-3">
                    <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom <?php echo str_contains($_SERVER['SCRIPT_NAME'], 'dashboard.php') ? 'active' : ''; ?>" 
                               href="<?php echo $basePath; ?>admin/dashboard.php">
                                <i class="bi bi-speedometer2 me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom" 
                               href="<?php echo $basePath; ?>admin/dashboard.php#students-table">
                                <i class="bi bi-people me-1"></i> Students
                            </a>
                        </li>
                    <?php elseif ($currentUser && $currentUser['role'] === 'student'): ?>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom <?php echo str_contains($_SERVER['SCRIPT_NAME'], 'dashboard.php') ? 'active' : ''; ?>" 
                               href="<?php echo $basePath; ?>student/dashboard.php">
                                <i class="bi bi-person-badge me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom <?php echo str_contains($_SERVER['SCRIPT_NAME'], 'edit-profile.php') ? 'active' : ''; ?>" 
                               href="<?php echo $basePath; ?>student/edit-profile.php">
                                <i class="bi bi-pencil-square me-1"></i> Edit Profile
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link nav-link-custom" href="<?php echo $basePath; ?>index.php">Home</a>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <?php if ($currentUser): ?>
                        <div class="user-badge px-3 py-1 rounded-pill bg-dark-subtle text-light border border-secondary border-opacity-25 d-flex align-items-center me-2">
                            <i class="bi bi-person-circle text-primary me-2 fs-5"></i>
                            <span class="fw-semibold small"><?php echo e($currentUser['name']); ?></span>
                            <span class="badge <?php echo $currentUser['role'] === 'admin' ? 'bg-danger' : 'bg-primary'; ?> ms-2 text-uppercase" style="font-size: 0.65rem;">
                                <?php echo e($currentUser['role']); ?>
                            </span>
                        </div>
                        <a href="<?php echo $basePath; ?>logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="<?php echo $basePath; ?>login.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login
                        </a>
                        <a href="<?php echo $basePath; ?>signup.php" class="btn btn-primary btn-sm rounded-pill px-3">
                            <i class="bi bi-person-plus me-1"></i> Signup
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="flex-grow-1 py-4">
        <div class="container">
            <?php
            // Display Flash Messages
            $flash = get_flash();
            if ($flash):
                $alertClass = match($flash['type']) {
                    'success' => 'alert-success border-success-subtle',
                    'danger' => 'alert-danger border-danger-subtle',
                    'warning' => 'alert-warning border-warning-subtle',
                    default => 'alert-info border-info-subtle'
                };
                $iconClass = match($flash['type']) {
                    'success' => 'bi-check-circle-fill',
                    'danger' => 'bi-exclamation-triangle-fill',
                    'warning' => 'bi-exclamation-circle-fill',
                    default => 'bi-info-circle-fill'
                };
            ?>
                <div class="alert <?php echo $alertClass; ?> alert-dismissible fade show shadow-sm d-flex align-items-center mb-4 rounded-3" role="alert">
                    <i class="bi <?php echo $iconClass; ?> me-2 fs-5"></i>
                    <div><?php echo e($flash['message']); ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
