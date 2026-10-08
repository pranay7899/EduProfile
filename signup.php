<?php
/**
 * Student Registration / Signup Page
 * Student Management System
 */

require_once __DIR__ . '/includes/auth.php';

// If already logged in, redirect
if (isLoggedIn()) {
    if (isAdmin()) redirect('/admin/dashboard.php');
    else redirect('/student/dashboard.php');
}

$errors = [];
$formData = [
    'name' => '',
    'email' => '',
    'date_of_birth' => '',
    'gender' => '',
    'qualification' => '',
    'interests' => [],
    'class' => '',
    'subject' => '',
    'marks' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid security token. Please refresh and try again.";
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = strtolower(sanitizeInput($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $dob = sanitizeInput($_POST['date_of_birth'] ?? '');
        $gender = sanitizeInput($_POST['gender'] ?? '');
        $qualification = sanitizeInput($_POST['qualification'] ?? '');
        $class = sanitizeInput($_POST['class'] ?? '');
        $subject = sanitizeInput($_POST['subject'] ?? '');
        $marks = sanitizeInput($_POST['marks'] ?? '');
        $interests = isset($_POST['interests']) && is_array($_POST['interests']) ? $_POST['interests'] : [];

        $formData = [
            'name' => $name,
            'email' => $email,
            'date_of_birth' => $dob,
            'gender' => $gender,
            'qualification' => $qualification,
            'interests' => $interests,
            'class' => $class,
            'subject' => $subject,
            'marks' => $marks
        ];

        // Validations
        if (empty($name)) $errors[] = "Full Name is required.";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please provide a valid email address.";
        if (empty($password) || strlen($password) < 6) $errors[] = "Password must be at least 6 characters long.";
        if (empty($dob)) $errors[] = "Date of Birth is required.";
        if (empty($gender)) $errors[] = "Gender selection is required.";
        if (empty($qualification)) $errors[] = "Qualification selection is required.";
        if (empty($class)) $errors[] = "Class is required.";
        if (empty($subject)) $errors[] = "Subject is required.";
        if ($marks === '' || !is_numeric($marks) || $marks < 0 || $marks > 100) $errors[] = "Marks must be a number between 0 and 100.";

        // --- Duplicate Email Check (Strict Error Message Requirement) ---
        $pdo = getDB();
        if (empty($errors)) {
            $checkStmt = $pdo->prepare("SELECT id FROM students WHERE email = ?");
            $checkStmt->execute([$email]);
            if ($checkStmt->fetch()) {
                // EXACT REQUIRED ERROR MESSAGE
                $errors[] = "This email is already registered.";
            }
        }

        // --- Aadhaar PDF Upload Validation ---
        $aadhaarFilename = null;
        if (empty($errors)) {
            if (!isset($_FILES['aadhaar_file']) || $_FILES['aadhaar_file']['error'] === UPLOAD_ERR_NO_FILE) {
                $errors[] = "Please upload a valid PDF file.";
            } else {
                $uploadResult = validateAndProcessAadhaarUpload($_FILES['aadhaar_file']);
                if (!$uploadResult['valid']) {
                    $errors[] = $uploadResult['error'];
                } else {
                    $aadhaarFilename = $uploadResult['filename'];
                }
            }
        }

        // --- Database Insertion ---
        if (empty($errors) && $aadhaarFilename) {
            try {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $interestsStr = implode(', ', array_map('sanitizeInput', $interests));

                $insertStmt = $pdo->prepare("
                    INSERT INTO students (
                        name, email, password, role, date_of_birth, gender, 
                        qualification, interests, class, subject, marks, aadhaar_file, created_at
                    ) VALUES (
                        ?, ?, ?, 'student', ?, ?, 
                        ?, ?, ?, ?, ?, ?, NOW()
                    )
                ");

                $insertStmt->execute([
                    $name,
                    $email,
                    $hashedPassword,
                    $dob,
                    $gender,
                    $qualification,
                    $interestsStr,
                    $class,
                    $subject,
                    $marks,
                    $aadhaarFilename
                ]);

                set_flash('success', 'Registration successful. You can now login.');
                redirect('login.php');

            } catch (\PDOException $ex) {
                if ($ex->getCode() == 23000) {
                    $errors[] = "This email is already registered.";
                } else {
                    $errors[] = "Registration failed: " . $ex->getMessage();
                }
            }
        }
    }
}

$pageTitle = "Student Registration";
require_once __DIR__ . '/includes/header.php';
?>

<div class="row justify-content-center py-3">
    <div class="col-lg-10">
        <div class="card card-custom shadow-lg">
            <div class="card-custom-header d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fw-bold mb-0 font-outfit text-dark"><i class="bi bi-person-plus text-primary me-2"></i> Student Registration Form</h4>
                    <p class="text-muted small mb-0">Fill in all information to create your student account</p>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill">PDF Upload Required</span>
            </div>

            <div class="card-body p-4">
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
                        <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i> Registration Error:</div>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo e($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="signup.php" method="POST" enctype="multipart/form-data" id="signupForm" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <!-- Section 1: Personal Information -->
                    <div class="border-bottom pb-3 mb-4">
                        <h5 class="fw-bold text-primary font-outfit mb-3"><i class="bi bi-person-lines-fill me-2"></i> 1. Personal Information</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required value="<?php echo e($formData['name']); ?>" placeholder="e.g. Rahul Sharma">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required value="<?php echo e($formData['email']); ?>" placeholder="e.g. rahul@example.com">
                            </div>
                            <div class="col-md-4">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" required minlength="6" placeholder="Min. 6 characters">
                                    <button class="btn btn-outline-secondary toggle-password-btn" type="button" data-target="password"><i class="bi bi-eye"></i></button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required value="<?php echo e($formData['date_of_birth']); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label d-block">Gender <span class="text-danger">*</span></label>
                                <div class="pt-1">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="gender_male" value="Male" <?php echo $formData['gender'] === 'Male' ? 'checked' : ''; ?> required>
                                        <label class="form-check-label" for="gender_male">Male</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="gender_female" value="Female" <?php echo $formData['gender'] === 'Female' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="gender_female">Female</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="gender" id="gender_other" value="Other" <?php echo $formData['gender'] === 'Other' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="gender_other">Other</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Education Information -->
                    <div class="border-bottom pb-3 mb-4">
                        <h5 class="fw-bold text-primary font-outfit mb-3"><i class="bi bi-book-half me-2"></i> 2. Education Information</h5>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="qualification" class="form-label">Qualification <span class="text-danger">*</span></label>
                                <select class="form-select" id="qualification" name="qualification" required>
                                    <option value="" disabled <?php echo empty($formData['qualification']) ? 'selected' : ''; ?>>Select Qualification</option>
                                    <?php
                                    $quals = ['SSC', 'Intermediate', 'Diploma', 'Graduation', 'Post Graduation', 'Other'];
                                    foreach ($quals as $q):
                                    ?>
                                        <option value="<?php echo $q; ?>" <?php echo $formData['qualification'] === $q ? 'selected' : ''; ?>><?php echo $q; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="class" class="form-label">Class <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="class" name="class" required value="<?php echo e($formData['class']); ?>" placeholder="e.g. 10th or B.Tech 3rd Year">
                            </div>
                            <div class="col-md-3">
                                <label for="subject" class="form-label">Subject / Stream <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="subject" name="subject" required value="<?php echo e($formData['subject']); ?>" placeholder="e.g. Computer Science">
                            </div>
                            <div class="col-md-3">
                                <label for="marks" class="form-label">Marks (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" max="100" class="form-control" id="marks" name="marks" required value="<?php echo e($formData['marks']); ?>" placeholder="e.g. 88.5">
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Interests -->
                    <div class="border-bottom pb-3 mb-4">
                        <h5 class="fw-bold text-primary font-outfit mb-3"><i class="bi bi-heart me-2"></i> 3. Interests (Select all that apply)</h5>
                        <div class="row g-2">
                            <?php
                            $availableInterests = ['Coding', 'Sports', 'Music', 'Reading', 'Gaming', 'Travelling', 'Art', 'Other'];
                            foreach ($availableInterests as $interest):
                                $checked = in_array($interest, $formData['interests']) ? 'checked' : '';
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
                    </div>

                    <!-- Section 4: Aadhaar Document Upload -->
                    <div class="mb-4">
                        <h5 class="fw-bold text-primary font-outfit mb-3"><i class="bi bi-file-earmark-pdf me-2"></i> 4. Upload Aadhaar Document</h5>
                        <div class="p-3 bg-light rounded-3 border">
                            <label for="aadhaar_file" class="form-label">Select Aadhaar Card Document (<strong>PDF Only</strong>, Max 5MB) <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="aadhaar_file" name="aadhaar_file" accept="application/pdf" required>
                            <div class="form-text mt-2 text-muted" id="aadhaar_feedback">
                                <i class="bi bi-shield-lock me-1"></i> Strictly PDF files accepted. All uploaded files are assigned unique server filenames to prevent overwrites.
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between pt-2">
                        <a href="login.php" class="text-decoration-none text-muted">
                            Already registered? <strong class="text-primary">Login here</strong>
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5">
                            <i class="bi bi-check-circle-fill me-2"></i> Register Student Account
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
