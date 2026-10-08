<?php
/**
 * Home / Landing Page
 * Student Management System
 */

require_once __DIR__ . '/includes/auth.php';

// If user is already logged in, redirect to respective dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('/admin/dashboard.php');
    } else {
        redirect('/student/dashboard.php');
    }
}

$pageTitle = "Welcome to Student Management Portal";
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<div class="row align-items-center py-5">
    <div class="col-lg-7 mb-4 mb-lg-0">
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold mb-3">
            <i class="bi bi-shield-check me-1"></i> Full-Stack Student Portal
        </span>
        <h1 class="display-4 fw-bold font-outfit text-dark mb-3">
            Modern & Secure <br><span class="text-primary">Student Management System</span>
        </h1>
        <p class="lead text-secondary mb-4 me-lg-4">
            A complete, production-ready portal featuring unified authentication, secure Aadhaar PDF storage, dynamic age computation, instant filtering, and role-based student & admin dashboards.
        </p>
        <div class="d-flex flex-wrap gap-3 mb-4">
            <a href="signup.php" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">
                <i class="bi bi-person-plus-fill me-2"></i> Register as Student
            </a>
            <a href="login.php" class="btn btn-outline-dark btn-lg rounded-pill px-4">
                <i class="bi bi-box-arrow-in-right me-2"></i> Login to Portal
            </a>
        </div>
        <div class="d-flex align-items-center gap-4 text-muted small">
            <div><i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> PDF Validation</div>
            <div><i class="bi bi-funnel-fill text-info me-1"></i> Instant Filtering</div>
            <div><i class="bi bi-key-fill text-success me-1"></i> CSRF & SQL Injection Protection</div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card card-custom p-4 border-0 shadow-lg position-relative">
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-primary text-white p-3 rounded-4 me-3">
                        <i class="bi bi-shield-lock-fill fs-2"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 font-outfit">Quick Access Login</h5>
                        <p class="text-muted small mb-0">Use test credentials or create a new student account</p>
                    </div>
                </div>

                <!-- Admin Test Account Banner -->
                <div class="bg-light p-3 rounded-3 border mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-danger text-uppercase">Predefined Admin</span>
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Ready to test</small>
                    </div>
                    <div class="small text-dark font-monospace">
                        <strong>Email:</strong> admin@example.com<br>
                        <strong>Password:</strong> Admin@123
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="login.php" class="btn btn-dark rounded-3 py-2 fw-semibold">
                        <i class="bi bi-door-open-fill me-2"></i> Open Unified Login Form
                    </a>
                    <a href="signup.php" class="btn btn-light border rounded-3 py-2 text-muted fw-semibold">
                        <i class="bi bi-clipboard-check me-2"></i> Open Signup Form
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="row g-4 my-4">
    <div class="col-md-4">
        <div class="card card-custom h-100 p-3">
            <div class="card-body">
                <div class="text-primary fs-1 mb-3"><i class="bi bi-shield-fill-check"></i></div>
                <h5 class="fw-bold font-outfit">Strict File Upload Security</h5>
                <p class="text-muted small">
                    Server-side MIME validation, 5MB file limit, extension checking, and unique filename generation (`student_TIMESTAMP_HASH.pdf`) preventing overwrites and execution risks.
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom h-100 p-3">
            <div class="card-body">
                <div class="text-primary fs-1 mb-3"><i class="bi bi-funnel"></i></div>
                <h5 class="fw-bold font-outfit">Real-Time Search & Filtering</h5>
                <p class="text-muted small">
                    Admin dashboard features multi-attribute filtering by Name (as you type), Class selection, Minimum Age, and Maximum Age operating concurrently.
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-custom h-100 p-3">
            <div class="card-body">
                <div class="text-primary fs-1 mb-3"><i class="bi bi-person-lock"></i></div>
                <h5 class="fw-bold font-outfit">Field Immutability Safeguards</h5>
                <p class="text-muted small">
                    Enforces locked Name and Email fields on Admin edits, and locked Email field on Student edits, enforced both on UI and strictly on PHP backend logic.
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
