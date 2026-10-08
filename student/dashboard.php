<?php
/**
 * Student Dashboard Page
 * Student Management System
 */

require_once __DIR__ . '/../includes/student-auth.php';

$pdo = getDB();
$userId = $_SESSION['user_id'];

// Fetch latest logged-in student details from DB
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ? AND role = 'student'");
$stmt->execute([$userId]);
$student = $stmt->fetch();

if (!$student) {
    set_flash('danger', 'Student profile data could not be retrieved.');
    redirect('/logout.php');
}

$age = calculateAge($student['date_of_birth']);
$aadhaarUrl = "../uploads/aadhaar/" . urlencode($student['aadhaar_file']);

$pageTitle = "Student Dashboard";
require_once __DIR__ . '/../includes/header.php';
?>

<!-- EXACT REQUIRED WELCOME BANNER FORMAT: Welcome, [User Name] (User ID: #[ID]) -->
<div class="welcome-banner mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between position-relative z-1">
        <div>
            <span class="badge bg-white text-primary border px-3 py-1 rounded-pill fw-bold mb-2">
                <i class="bi bi-person-badge-fill me-1"></i> Student Portal
            </span>
            <!-- EXACT STRING FORMAT REQUIRED -->
            <h2 class="fw-bold mb-1 font-outfit">Welcome, <?php echo e($student['name']); ?> (User ID: #<?php echo $student['id']; ?>)</h2>
            <p class="mb-0 text-white-50">Logged in as <?php echo e($student['email']); ?> | Class: <?php echo e($student['class']); ?></p>
        </div>
        <div class="mt-3 mt-md-0 d-flex gap-2">
            <a href="edit-profile.php" class="btn btn-light rounded-pill px-4 fw-semibold text-primary">
                <i class="bi bi-pencil-square me-1"></i> Edit Profile
            </a>
            <a href="../logout.php" class="btn btn-outline-light rounded-pill px-4">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Main Profile Card -->
    <div class="col-lg-8">
        <div class="card card-custom shadow-sm mb-4">
            <div class="card-custom-header d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0 font-outfit text-dark"><i class="bi bi-person-circle text-primary me-2"></i> Complete Student Profile Details</h5>
                <a href="edit-profile.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="bi bi-pencil me-1"></i> Edit Profile
                </a>
            </div>

            <div class="card-body p-4">
                <div class="row g-4">
                    <!-- Personal Information -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100 border">
                            <div class="profile-detail-label mb-1">Full Name</div>
                            <div class="profile-detail-value mb-3 text-dark"><?php echo e($student['name']); ?></div>

                            <div class="profile-detail-label mb-1">Email Address</div>
                            <div class="profile-detail-value mb-3 text-dark"><?php echo e($student['email']); ?></div>

                            <div class="profile-detail-label mb-1">Date of Birth & Age</div>
                            <div class="profile-detail-value text-dark">
                                <?php echo date('d M Y', strtotime($student['date_of_birth'])); ?> 
                                <span class="badge bg-primary-subtle text-primary border ms-2"><?php echo $age; ?> Years Old</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 h-100 border">
                            <div class="profile-detail-label mb-1">Gender</div>
                            <div class="profile-detail-value mb-3 text-dark"><?php echo e($student['gender']); ?></div>

                            <div class="profile-detail-label mb-1">Qualification</div>
                            <div class="profile-detail-value mb-3 text-dark"><?php echo e($student['qualification']); ?></div>

                            <div class="profile-detail-label mb-1">Registration Date</div>
                            <div class="profile-detail-value text-dark"><?php echo date('F d, Y - h:i A', strtotime($student['created_at'])); ?></div>
                        </div>
                    </div>

                    <!-- Academic Details -->
                    <div class="col-12">
                        <div class="p-3 bg-white rounded-3 border">
                            <h6 class="fw-bold text-primary font-outfit mb-3"><i class="bi bi-journal-bookmark me-2"></i> Academic Performance</h6>
                            <div class="row g-3">
                                <div class="col-sm-4">
                                    <div class="profile-detail-label mb-1">Class</div>
                                    <div class="fs-5 fw-bold text-dark"><?php echo e($student['class']); ?></div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="profile-detail-label mb-1">Subject / Stream</div>
                                    <div class="fs-5 fw-bold text-dark"><?php echo e($student['subject']); ?></div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="profile-detail-label mb-1">Marks Obtained</div>
                                    <div>
                                        <span class="badge fs-6 <?php echo $student['marks'] >= 75 ? 'bg-success' : 'bg-primary'; ?> px-3 py-2 rounded-pill">
                                            <?php echo number_format($student['marks'], 2); ?>%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Interests -->
                    <div class="col-12">
                        <div class="p-3 bg-white rounded-3 border">
                            <div class="profile-detail-label mb-2">Interests & Hobbies</div>
                            <div class="d-flex flex-wrap gap-2">
                                <?php 
                                $interests = array_filter(array_map('trim', explode(',', $student['interests'] ?? '')));
                                if (empty($interests)):
                                ?>
                                    <span class="text-muted small">No interests recorded.</span>
                                <?php else: ?>
                                    <?php foreach ($interests as $interest): ?>
                                        <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill font-outfit fw-medium">
                                            <i class="bi bi-tag-fill me-1 text-primary"></i> <?php echo e($interest); ?>
                                        </span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Aadhaar Document Sidebar Card -->
    <div class="col-lg-4">
        <div class="card card-custom shadow-sm mb-4">
            <div class="card-custom-header">
                <h5 class="fw-bold mb-0 font-outfit text-dark"><i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i> Aadhaar Document</h5>
            </div>
            <div class="card-body p-4 text-center">
                <div class="p-4 bg-light rounded-4 border mb-3">
                    <i class="bi bi-file-pdf fs-1 text-danger d-block mb-2"></i>
                    <h6 class="fw-bold mb-1">Uploaded PDF Document</h6>
                    <p class="text-muted small mb-0 font-monospace text-truncate">
                        <?php echo e($student['aadhaar_file']); ?>

                    </p>
                </div>

                <?php if (!empty($student['aadhaar_file'])): ?>
                    <a href="<?php echo $aadhaarUrl; ?>" target="_blank" class="btn btn-danger btn-lg rounded-pill w-100 mb-2 shadow-sm">
                        <i class="bi bi-box-arrow-up-right me-2"></i> View Aadhaar PDF
                    </a>
                <?php else: ?>
                    <div class="alert alert-warning mb-2 small">No Aadhaar PDF document available.</div>
                <?php endif; ?>

                <a href="edit-profile.php" class="btn btn-outline-secondary btn-sm rounded-pill w-100 mt-2">
                    <i class="bi bi-arrow-repeat me-1"></i> Replace Aadhaar Document
                </a>
            </div>
        </div>

        <div class="card card-custom shadow-sm p-3 bg-primary-subtle border-primary-subtle">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-shield-lock-fill fs-2 text-primary"></i>
                <div class="small">
                    <strong>Secure Identity Record:</strong>
                    <div class="text-muted">Your profile is stored with encrypted sessions and server-side validated uploads.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
