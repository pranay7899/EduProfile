<?php
/**
 * Global Footer Component
 * Student Management System
 */
$basePath = (str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin/') || str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/student/')) ? '../' : './';
?>
        </div>
    </main>

    <!-- Global Footer -->
    <footer class="footer mt-auto py-3 bg-white border-top text-muted text-center text-md-start">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    <span class="small font-outfit fw-medium">
                        &copy; <?php echo date('Y'); ?> <strong class="text-dark">Student Management System</strong>. All rights reserved.
                    </span>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill small">
                        <i class="bi bi-shield-lock-fill text-primary me-1"></i> PDO & Prepared Statements Active
                    </span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Application JS -->
    <script src="<?php echo $basePath; ?>assets/js/script.js"></script>
</body>
</html>
