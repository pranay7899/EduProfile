<?php
/**
 * Admin Dashboard Page
 * Student Management System
 */

require_once __DIR__ . '/../includes/admin-auth.php';

$pdo = getDB();

// Fetch all registered students (excluding admins from list)
$stmt = $pdo->query("SELECT * FROM students WHERE role = 'student' ORDER BY id DESC");
$students = $stmt->fetchAll();

// Calculate metrics
$totalStudents = count($students);
$classesList = array_unique(array_filter(array_column($students, 'class')));
sort($classesList);

$totalMarks = 0;
$withAadhaar = 0;
foreach ($students as $s) {
    $totalMarks += (float)$s['marks'];
    if (!empty($s['aadhaar_file'])) $withAadhaar++;
}
$avgMarks = $totalStudents > 0 ? round($totalMarks / $totalStudents, 2) : 0;

$pageTitle = "Admin Dashboard";
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Header Banner -->
<div class="welcome-banner mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between position-relative z-1">
        <div>
            <span class="badge bg-white text-primary border px-3 py-1 rounded-pill fw-bold mb-2">
                <i class="bi bi-shield-lock-fill me-1"></i> Control Panel
            </span>
            <h2 class="fw-bold mb-1 font-outfit">Admin Dashboard</h2>
            <p class="mb-0 text-white-50">Welcome, Administrator (Logged in as <?php echo e($_SESSION['user_email']); ?>)</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="../logout.php" class="btn btn-outline-light rounded-pill px-4">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </div>
</div>

<!-- Key Performance Indicators (KPI Cards) -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card card-custom p-3 border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold">TOTAL STUDENTS</div>
                    <div class="fs-3 fw-bold font-outfit text-dark"><?php echo $totalStudents; ?></div>
                </div>
                <div class="bg-primary-subtle text-primary p-3 rounded-circle">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-custom p-3 border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold">AVG MARKS</div>
                    <div class="fs-3 fw-bold font-outfit text-dark"><?php echo $avgMarks; ?>%</div>
                </div>
                <div class="bg-success-subtle text-success p-3 rounded-circle">
                    <i class="bi bi-award-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-custom p-3 border-start border-4 border-info">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold">ACTIVE CLASSES</div>
                    <div class="fs-3 fw-bold font-outfit text-dark"><?php echo count($classesList); ?></div>
                </div>
                <div class="bg-info-subtle text-info p-3 rounded-circle">
                    <i class="bi bi-journal-bookmark-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-custom p-3 border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small fw-semibold">AADHAAR VERIFIED</div>
                    <div class="fs-3 fw-bold font-outfit text-dark"><?php echo $withAadhaar; ?></div>
                </div>
                <div class="bg-warning-subtle text-warning p-3 rounded-circle">
                    <i class="bi bi-file-earmark-pdf-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Instant Filter Controls Card -->
<div class="card card-custom mb-4 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="fw-bold mb-0 font-outfit text-dark"><i class="bi bi-funnel-fill text-primary me-2"></i> Search & Filter Students</h5>
        <button id="resetFilters" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filters
        </button>
    </div>
    <div class="card-body bg-light-subtle">
        <div class="row g-3">
            <!-- 1. Search by Name -->
            <div class="col-md-3">
                <label for="searchName" class="form-label">Search by Name</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchName" class="form-control" placeholder="Type student name...">
                </div>
            </div>

            <!-- 2. Class Filter -->
            <div class="col-md-3">
                <label for="filterClass" class="form-label">Class Filter</label>
                <select id="filterClass" class="form-select">
                    <option value="">All Classes</option>
                    <?php foreach ($classesList as $c): ?>
                        <option value="<?php echo e($c); ?>"><?php echo e($c); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- 3. Minimum Age -->
            <div class="col-md-3">
                <label for="filterMinAge" class="form-label">Minimum Age</label>
                <input type="number" id="filterMinAge" min="0" max="100" class="form-control" placeholder="e.g. 15">
            </div>

            <!-- 4. Maximum Age -->
            <div class="col-md-3">
                <label for="filterMaxAge" class="form-label">Maximum Age</label>
                <input type="number" id="filterMaxAge" min="0" max="100" class="form-control" placeholder="e.g. 25">
            </div>
        </div>
    </div>
</div>

<!-- Students Records Table Card -->
<div class="card card-custom shadow-sm" id="students-table">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="fw-bold mb-0 font-outfit text-dark">
            <i class="bi bi-table text-primary me-2"></i> Registered Students Directory 
            <span class="badge bg-secondary rounded-pill ms-2 fs-6"><?php echo $totalStudents; ?></span>
        </h5>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-custom align-middle mb-0" id="studentsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Qualification</th>
                        <th>Interests</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Marks</th>
                        <th>Aadhaar</th>
                        <th>Created At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($students)): ?>
                        <tr id="noRecordsRow">
                            <td colspan="13" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                No student records found in the system.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($students as $s): 
                            $age = calculateAge($s['date_of_birth']);
                            $aadhaarUrl = "../uploads/aadhaar/" . urlencode($s['aadhaar_file']);
                        ?>
                            <tr data-name="<?php echo e($s['name']); ?>" 
                                data-class="<?php echo e($s['class']); ?>" 
                                data-age="<?php echo $age; ?>">
                                <td class="fw-bold text-secondary">#<?php echo $s['id']; ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo e($s['name']); ?></div>
                                </td>
                                <td class="text-muted small"><?php echo e($s['email']); ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1"><?php echo $age; ?> yrs</span>
                                </td>
                                <td><?php echo e($s['gender']); ?></td>
                                <td><span class="badge bg-primary-subtle text-primary border"><?php echo e($s['qualification']); ?></span></td>
                                <td style="max-width: 180px;">
                                    <span class="small text-truncate d-block" title="<?php echo e($s['interests']); ?>">
                                        <?php echo e($s['interests']); ?>
                                    </span>
                                </td>
                                <td class="fw-semibold text-dark"><?php echo e($s['class']); ?></td>
                                <td><?php echo e($s['subject']); ?></td>
                                <td>
                                    <span class="badge <?php echo $s['marks'] >= 75 ? 'bg-success-subtle text-success' : ($s['marks'] >= 50 ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger'); ?> border px-2 py-1">
                                        <?php echo number_format($s['marks'], 2); ?>%
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($s['aadhaar_file'])): ?>
                                        <a href="<?php echo $aadhaarUrl; ?>" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" style="font-size: 0.8rem;">
                                            <i class="bi bi-file-earmark-pdf me-1"></i> View PDF
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small"><?php echo date('d M Y', strtotime($s['created_at'])); ?></td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="edit-student.php?id=<?php echo $s['id']; ?>" class="btn btn-outline-primary" title="Edit Student">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $s['id']; ?>" title="Delete Student">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>

                                    <!-- Safe Delete Confirmation Modal -->
                                    <div class="modal fade text-start" id="deleteModal<?php echo $s['id']; ?>" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title font-outfit fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Student Deletion</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p class="mb-2">Are you sure you want to delete this student?</p>
                                                    <div class="p-3 bg-light border rounded-3 mb-2 small">
                                                        <strong>Student Name:</strong> <?php echo e($s['name']); ?><br>
                                                        <strong>Email:</strong> <?php echo e($s['email']); ?><br>
                                                        <strong>ID:</strong> #<?php echo $s['id']; ?>
                                                    </div>
                                                    <div class="text-danger small"><i class="bi bi-info-circle me-1"></i> This action is permanent. MySQL record and uploaded Aadhaar document will be deleted.</div>
                                                </div>
                                                <div class="modal-footer bg-light">
                                                    <button type="button" class="btn btn-secondary rounded-pill btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="delete-student.php" method="POST" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                        <input type="hidden" name="student_id" value="<?php echo $s['id']; ?>">
                                                        <button type="submit" class="btn btn-danger rounded-pill btn-sm">
                                                            <i class="bi bi-trash-fill me-1"></i> Delete Student
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="noRecordsRow" style="display: none;">
                            <td colspan="13" class="text-center py-5 text-muted">
                                <i class="bi bi-search fs-1 d-block mb-2 text-secondary"></i>
                                No students match your current search and filter criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
