<?php
/**
 * Unified Login Page for Admins and Students
 * Student Management System
 */

require_once __DIR__ . '/includes/auth.php';

// If user is already logged in, redirect to respective dashboard
if (isLoggedIn()) {
    if (isAdmin()) redirect('/admin/dashboard.php');
    else redirect('/student/dashboard.php');
}

$error = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token. Please try again.";
    } else {
        $email = strtolower(sanitizeInput($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = "Invalid email or password.";
        } else {
            $pdo = getDB();
            $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM students WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Secure Session Regeneration
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                set_flash('success', 'Welcome back, ' . e($user['name']) . '!');

                if ($user['role'] === 'admin') {
                    redirect('/admin/dashboard.php');
                } else {
                    redirect('/student/dashboard.php');
                }
            } else {
                // EXACT REQUIRED ERROR MESSAGE
                $error = "Invalid email or password.";
            }
        }
    }
}

$pageTitle = "Portal Login";
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center py-5">
    <div class="col-md-6 col-lg-5">
        <div class="card card-custom shadow-lg">
            <div class="card-custom-header text-center py-4">
                <div class="brand-icon bg-primary text-white mx-auto rounded-circle d-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px;">
                    <i class="bi bi-shield-lock fs-3"></i>
                </div>
                <h4 class="fw-bold mb-1 font-outfit text-dark">Portal Login</h4>
                <p class="text-muted small mb-0">Single login for both Students and Administrators</p>
            </div>

            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                        <i class="bi bi-exclamation-circle-fill me-2"></i> <?php echo e($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required value="<?php echo e($email); ?>" placeholder="name@example.com">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="password" class="form-label mb-0">Password</label>
                            <a href="forgot-password.php" class="small text-primary text-decoration-none">Forgot Password?</a>
                        </div>
                        <div class="input-group mt-1">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-key"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required placeholder="Enter password">
                            <button class="btn btn-outline-secondary toggle-password-btn" type="button" data-target="password"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Login to Account
                        </button>
                    </div>
                </form>

                <div class="mt-4 pt-3 border-top text-center">
                    <p class="text-muted small mb-0">
                        Don't have a student account? 
                        <a href="signup.php" class="text-primary fw-semibold text-decoration-none">Register Here</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Demo Accounts Helper Box -->
        <div class="card card-custom mt-3 p-3 bg-light border">
            <div class="small text-muted">
                <div class="fw-bold text-dark mb-1"><i class="bi bi-lightbulb text-warning me-1"></i> Predefined Admin Credentials:</div>
                <code>Email: admin@example.com | Password: Admin@123</code>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
