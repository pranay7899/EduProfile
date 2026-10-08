<?php
/**
 * Reset Password Handler Page
 * Student Management System
 */

require_once __DIR__ . '/includes/auth.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$email = $_GET['email'] ?? $_POST['email'] ?? '';

$error = null;
$validToken = false;
$userId = null;

$pdo = getDB();

if (!empty($token) && !empty($email)) {
    $stmt = $pdo->prepare("
        SELECT id FROM students 
        WHERE email = ? AND reset_token = ? AND reset_token_expires > NOW()
    ");
    $stmt->execute([$email, $token]);
    $user = $stmt->fetch();

    if ($user) {
        $validToken = true;
        $userId = $user['id'];
    } else {
        $error = "This password reset token is invalid or has expired. Please request a new link.";
    }
} else {
    $error = "Missing password reset parameters. Please use the reset link provided.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token. Please try again.";
    } else {
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($newPassword) || strlen($newPassword) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "Passwords do not match.";
        } else {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("
                UPDATE students 
                SET password = ?, reset_token = NULL, reset_token_expires = NULL 
                WHERE id = ?
            ");
            $updateStmt->execute([$hashedPassword, $userId]);

            // EXACT REQUIRED MESSAGE
            set_flash('success', 'Your password has been reset successfully.');
            redirect('login.php');
        }
    }
}

$pageTitle = "Reset Password";
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center py-5">
    <div class="col-md-6 col-lg-5">
        <div class="card card-custom shadow-lg">
            <div class="card-custom-header text-center py-4">
                <div class="brand-icon bg-success text-white mx-auto rounded-circle d-flex align-items-center justify-content-center mb-2" style="width: 48px; height: 48px;">
                    <i class="bi bi-shield-check fs-3"></i>
                </div>
                <h4 class="fw-bold mb-1 font-outfit text-dark">Set New Password</h4>
                <p class="text-muted small mb-0">Create a new secure password for your account</p>
            </div>

            <div class="card-body p-4">
                <?php if ($error && !$validToken): ?>
                    <div class="alert alert-danger rounded-3 mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo e($error); ?>
                    </div>
                    <div class="d-grid">
                        <a href="forgot-password.php" class="btn btn-outline-primary rounded-pill">
                            <i class="bi bi-arrow-left me-1"></i> Request New Reset Link
                        </a>
                    </div>
                <?php else: ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger rounded-3 mb-4">
                            <i class="bi bi-exclamation-circle-fill me-2"></i> <?php echo e($error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="reset-password.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="token" value="<?php echo e($token); ?>">
                        <input type="hidden" name="email" value="<?php echo e($email); ?>">

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6" placeholder="At least 6 characters">
                                <button class="btn btn-outline-secondary toggle-password-btn" type="button" data-target="new_password"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="bi bi-check-circle"></i></span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Re-enter password">
                                <button class="btn btn-outline-secondary toggle-password-btn" type="button" data-target="confirm_password"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-semibold">
                                <i class="bi bi-save-fill me-2"></i> Update Password
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
