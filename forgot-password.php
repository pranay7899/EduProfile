<?php
/**
 * Forgot Password Request Page
 * Student Management System
 */

require_once __DIR__ . '/includes/auth.php';

$message = null;
$messageType = 'info';
$resetLink = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = "Invalid security token. Please try again.";
        $messageType = "danger";
    } else {
        $email = strtolower(sanitizeInput($_POST['email'] ?? ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter a valid email address.";
            $messageType = "danger";
        } else {
            $pdo = getDB();
            $stmt = $pdo->prepare("SELECT id, name FROM students WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user) {
                // Generate 64-char token & set 30 min expiry
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+30 minutes'));

                $updateStmt = $pdo->prepare("UPDATE students SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
                $updateStmt->execute([$token, $expires, $user['id']]);

                // Local development reset link generator
                $resetLink = "reset-password.php?token=" . urlencode($token) . "&email=" . urlencode($email);
                
                $message = "Password reset link generated successfully! (In production, this is sent via email).";
                $messageType = "success";
            } else {
                // Secure generic message to prevent account enumeration
                $message = "If an account exists for " . e($email) . ", a password reset link has been processed.";
                $messageType = "info";
            }
        }
    }
}

$pageTitle = "Forgot Password";
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center py-5">
    <div class="col-md-6 col-lg-5">
        <div class="card card-custom shadow-lg">
            <div class="card-custom-header text-center py-4">
                <div class="brand-icon bg-warning text-dark mx-auto rounded-circle d-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px;">
                    <i class="bi bi-key-fill fs-3"></i>
                </div>
                <h4 class="fw-bold mb-1 font-outfit text-dark">Forgot Password</h4>
                <p class="text-muted small mb-0">Enter your registered email to reset your account password</p>
            </div>

            <div class="card-body p-4">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i> <?php echo e($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if ($resetLink): ?>
                    <!-- Local Development Fallback Link Display -->
                    <div class="p-3 bg-light border border-success-subtle rounded-3 mb-4">
                        <div class="fw-bold text-success mb-1"><i class="bi bi-link-45deg me-1"></i> Local Development Reset Link:</div>
                        <p class="small text-muted mb-2">Click the link below to open the Reset Password screen directly:</p>
                        <a href="<?php echo e($resetLink); ?>" class="btn btn-sm btn-success rounded-pill w-100 font-monospace">
                            Open Reset Password Link
                        </a>
                    </div>
                <?php endif; ?>

                <form action="forgot-password.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label">Registered Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="name@example.com">
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-semibold">
                            <i class="bi bi-send-fill me-2"></i> Generate Password Reset Link
                        </button>
                    </div>
                </form>

                <div class="mt-4 pt-3 border-top text-center">
                    <a href="login.php" class="text-secondary text-decoration-none small">
                        <i class="bi bi-arrow-left me-1"></i> Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
