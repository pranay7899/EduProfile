<?php
/**
 * Student Edit Profile Page
 * Student Management System
 */

require_once __DIR__ . '/../includes/student-auth.php';

$pdo = getDB();
$userId = $_SESSION['user_id'];

// Fetch logged-in student details
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ? AND role = 'student'");
$stmt->execute([$userId]);
$student = $stmt->fetch();

if (!$student) {
    set_flash('danger', 'Student record not found.');
    redirect('/logout.php');
}

$errors = [];
$interestsArr = array_filter(array_map('trim', explode(',', $student['interests'] ?? '')));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid security token. Please try again.";
    } else {
        // Enforce backend locked fields: Name, Email & User ID CANNOT BE MODIFIED BY STUDENT
        $lockedName = $student['name'];
        $lockedEmail = $student['email'];

        // Editable fields
        $dob = sanitizeInput($_POST['date_of_birth'] ?? '');
        $gender = sanitizeInput($_POST['gender'] ?? '');
        $qualification = sanitizeInput($_POST['qualification'] ?? '');
        $class = sanitizeInput($_POST['class'] ?? '');
        $subject = sanitizeInput($_POST['subject'] ?? '');
        $marks = sanitizeInput($_POST['marks'] ?? '');
        $interests = isset($_POST['interests']) && is_array($_POST['interests']) ? $_POST['interests'] : [];

        // Validations
        if (empty($dob)) $errors[] = "Date of Birth is required.";
        if (empty($gender)) $errors[] = "Gender is required.";
        if (empty($qualification)) $errors[] = "Qualification is required.";
        if (empty($class)) $errors[] = "Class is required.";
        if (empty($subject)) $errors[] = "Subject is required.";
        if ($marks === '' || !is_numeric($marks) || $marks < 0 || $marks > 100) $errors[] = "Marks must be between 0 and 100.";

        // Handle Optional Aadhaar File Replacement
        $aadhaarFilename = $student['aadhaar_file'];
        if (empty($errors) && isset($_FILES['aadhaar_file']) && $_FILES['aadhaar_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = validateAndProcessAadhaarUpload($_FILES['aadhaar_file'], $student['aadhaar_file']);
            if (!$uploadResult['valid']) {
                $errors[] = $uploadResult['error'];
            } else {
                $aadhaarFilename = $uploadResult['filename'];
            }
        }

        // Database Update
        if (empty($errors)) {
            $interestsStr = implode(', ', array_map('sanitizeInput', $interests));

            $updateStmt = $pdo->prepare("
                UPDATE students SET
                    date_of_birth = ?,
                    gender = ?,
                    qualification = ?,
                    interests = ?,
                    class = ?,
                    subject = ?,
                    marks = ?,
                    aadhaar_file = ?
                WHERE id = ? AND role = 'student'
            ");

            $updateStmt->execute([
                $dob,
                $gender,
                $qualification,
                $interestsStr,
                $class,
                $subject,
                $marks,
                $aadhaarFilename,
                $userId
            ]);

            // EXACT REQUIRED SUCCESS MESSAGE
            set_flash('success', 'Profile updated successfully.');
            redirect('/student/dashboard.php');
        }
    }
}

$pageTitle = "Edit My Profile";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center py-3">
    <div class="col-lg-9">
        <div class="card card-custom shadow-lg">
            <div class="card-custom-header d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fw-bold mb-0 font-outfit text-dark"><i class="bi bi-pencil-square text-primary me-2"></i> Edit Student Profile</h4>
                    <p class="text-muted small mb-0">Update your details and Aadhaar PDF document</p>
                </div>
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>

            <div class="card-body p-4">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4">
                        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i> Update Error:</div>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo e($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="edit-profile.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <!-- Immutable Account Identity Box -->
                    <div class="bg-light p-3 rounded-3 border mb-4">
                        <h6 class="fw-bold text-dark font-outfit mb-2"><i class="bi bi-shield-lock-fill text-primary me-2"></i> Account Identity (Locked)</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Student Name</label>
                                <input type="text" class="form-control bg-white" value="<?php echo e($student['name']); ?>" disabled readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-1">Email Address (Read-only)</label>
                                <input type="email" class="form-control bg-white" value="<?php echo e($student['email']); ?>" disabled readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Editable Personal Information -->
                    <h5 class="fw-bold text-primary font-outfit mb-3"><i class="bi bi-person-lines-fill me-2"></i> Personal Details</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required value="<?php echo e($student['date_of_birth']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block">Gender <span class="text-danger">*</span></label>
                            <div class="pt-1">
                                <?php foreach (['Male', 'Female', 'Other'] as $g): ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="gender_<?php echo strtolower($g); ?>" value="<?php echo $g; ?>" <?php echo $student['gender'] === $g ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="gender_<?php echo strtolower($g); ?>"><?php echo $g; ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Editable Education Details -->
                    <h5 class="fw-bold text-primary font-outfit mb-3"><i class="bi bi-journal-check me-2"></i> Education Information</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="qualification" class="form-label">Qualification <span class="text-danger">*</span></label>
                            <select class="form-select" id="qualification" name="qualification" required>
                                <?php foreach (['SSC', 'Intermediate', 'Diploma', 'Graduation', 'Post Graduation', 'Other'] as $q): ?>
                                    <option value="<?php echo $q; ?>" <?php echo $student['qualification'] === $q ? 'selected' : ''; ?>><?php echo $q; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="class" class="form-label">Class <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="class" name="class" required value="<?php echo e($student['class']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="subject" name="subject" required value="<?php echo e($student['subject']); ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="marks" class="form-label">Marks (%) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" max="100" class="form-control" id="marks" name="marks" required value="<?php echo e($student['marks']); ?>">
                        </div>
                    </div>

                    <!-- Editable Interests -->
                    <h5 class="fw-bold text-primary font-outfit mb-3"><i class="bi bi-heart me-2"></i> Interests (Select all that apply)</h5>
                    <div class="row g-2 mb-4">
                        <?php
                        $availableInterests = ['Coding', 'Sports', 'Music', 'Reading', 'Gaming', 'Travelling', 'Art', 'Other'];
                        foreach ($availableInterests as $interest):
                            $checked = in_array($interest, $interestsArr) ? 'checked' : '';
                        ?>
                            <div class="col-6 col-sm-4 col-md-3">
                                <div class="form-check p-2 border rounded-3 bg-light">
                                    <input class="form-check-input ms-1 me-2" type="checkbox" name="interests[]" id="interest_<?php echo strtolower($interest); ?>" value="<?php echo $interest; ?>" <?php echo $checked; ?>>
                                    <label class="form-check-label fw-medium text-dark" for="interest_<?php echo strtolower($interest); ?>">
                                        <?php echo $interest; ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Replace Aadhaar PDF Document -->
                    <h5 class="fw-bold text-primary font-outfit mb-3"><i class="bi bi-file-earmark-pdf me-2"></i> Aadhaar PDF Document</h5>
                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <span class="fw-semibold">Current Document:</span>
                                <?php if (!empty($student['aadhaar_file'])): ?>
                                    <a href="../uploads/aadhaar/<?php echo urlencode($student['aadhaar_file']); ?>" target="_blank" class="btn btn-sm btn-outline-danger ms-2">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> View Current PDF
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted ms-2">No document attached</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <label for="aadhaar_file" class="form-label fw-semibold">Replace Aadhaar PDF (Optional, PDF only, Max 5MB)</label>
                        <input type="file" class="form-control" id="aadhaar_file" name="aadhaar_file" accept="application/pdf">
                        <div class="form-text mt-2 text-muted" id="aadhaar_feedback">
                            Uploading a new PDF file will safely replace your current document and generate a new unique filename on the server.
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2">
                        <a href="dashboard.php" class="btn btn-light border rounded-pill px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary rounded-pill px-5">
                            <i class="bi bi-check-circle-fill me-2"></i> Update Profile
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
