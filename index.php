<?php
/**
 * Dashboard Overview
 * Student Management System
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

try {
    $pdo = get_db_connection();

    // 1. Total Students Count
    $totalStudents = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();

    // 2. Active Students Count
    $activeStudents = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Active'")->fetchColumn();

    // 3. Inactive Students Count
    $inactiveStudents = (int)$pdo->query("SELECT COUNT(*) FROM students WHERE status = 'Inactive'")->fetchColumn();

    // 4. Department Distribution
    $deptStmt = $pdo->query("
        SELECT department, COUNT(*) as count 
        FROM students 
        GROUP BY department 
        ORDER BY count DESC
    ");
    $departmentStats = $deptStmt->fetchAll();
    $totalDepartments = count($departmentStats);

    // 5. Recent Student Records (Top 6 latest)
    $recentStmt = $pdo->query("
        SELECT * FROM students 
        ORDER BY id DESC 
        LIMIT 6
    ");
    $recentStudents = $recentStmt->fetchAll();

} catch (PDOException $e) {
    set_flash('danger', 'Database error: ' . $e->getMessage());
    $totalStudents = 0;
    $activeStudents = 0;
    $inactiveStudents = 0;
    $totalDepartments = 0;
    $departmentStats = [];
    $recentStudents = [];
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$verifyBase = $protocol . $host . BASE_URL . '/students/verify.php?code=';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Welcome Banner -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold text-dark mb-1">Welcome to Student Management Portal</h4>
        <p class="text-secondary mb-0">Overview of student enrollments, status distribution, and recent activity.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/students/qr-generator.php" class="btn btn-outline-dark d-inline-flex align-items-center">
            <i class="bi bi-qr-code-scan me-2"></i> QR & AR Generator
        </a>
        <a href="<?= BASE_URL ?>/students/create.php" class="btn btn-primary d-inline-flex align-items-center">
            <i class="bi bi-person-plus-fill me-2"></i> Add New Student
        </a>
        <a href="<?= BASE_URL ?>/students/index.php" class="btn btn-outline-secondary d-inline-flex align-items-center">
            <i class="bi bi-people-fill me-2"></i> Manage Students
        </a>
    </div>
</div>

<!-- Metrics Cards Row -->
<div class="row g-3 mb-4">
    <!-- Total Students -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-secondary small text-uppercase fw-bold">Total Students</span>
                <h3 class="fw-bold text-dark mb-0 mt-1"><?= number_format($totalStudents) ?></h3>
                <span class="text-muted small">Registered in system</span>
            </div>
            <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                <i class="bi bi-people-fill"></i>
            </div>
        </div>
    </div>

    <!-- Active Students -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-secondary small text-uppercase fw-bold">Active Students</span>
                <h3 class="fw-bold text-success mb-0 mt-1"><?= number_format($activeStudents) ?></h3>
                <span class="text-muted small">
                    <?= $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100) : 0 ?>% of total roster
                </span>
            </div>
            <div class="stat-icon bg-success bg-opacity-10 text-success">
                <i class="bi bi-person-check-fill"></i>
            </div>
        </div>
    </div>

    <!-- Inactive Students -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-secondary small text-uppercase fw-bold">Inactive Students</span>
                <h3 class="fw-bold text-danger mb-0 mt-1"><?= number_format($inactiveStudents) ?></h3>
                <span class="text-muted small">Suspended / Graduated</span>
            </div>
            <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                <i class="bi bi-person-dash-fill"></i>
            </div>
        </div>
    </div>

    <!-- Total Departments -->
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center justify-content-between">
            <div>
                <span class="text-secondary small text-uppercase fw-bold">Departments</span>
                <h3 class="fw-bold text-indigo mb-0 mt-1" style="color: #6366f1;"><?= number_format($totalDepartments) ?></h3>
                <span class="text-muted small">Academic faculties</span>
            </div>
            <div class="stat-icon bg-indigo bg-opacity-10" style="background-color: rgba(99, 102, 241, 0.1); color: #6366f1;">
                <i class="bi bi-buildings-fill"></i>
            </div>
        </div>
    </div>
</div>

<!-- Main Section: Recent Students & Department Distribution -->
<div class="row g-4">
    <!-- Recent Students Table -->
    <div class="col-xl-8">
        <div class="table-card">
            <div class="p-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="fw-bold text-dark mb-0">Recently Added Students</h6>
                    <small class="text-muted">Latest student enrollments</small>
                </div>
                <a href="<?= BASE_URL ?>/students/index.php" class="btn btn-sm btn-light border text-secondary fw-semibold">
                    View All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student</th>
                            <th>Department</th>
                            <th>Enrollment Date</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentStudents)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-person-x fs-1 d-block mb-2 text-secondary"></i>
                                        No student records found in the database.
                                    </div>
                                    <a href="<?= BASE_URL ?>/students/create.php" class="btn btn-sm btn-primary mt-3">
                                        <i class="bi bi-plus-lg me-1"></i> Add First Student
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentStudents as $student): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-primary border font-monospace">
                                            <?= e($student['student_id']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($student['photo']) && file_exists(__DIR__ . '/' . ltrim($student['photo'], '/'))): ?>
                                                <img src="<?= BASE_URL . '/' . ltrim(e($student['photo']), '/') ?>" alt="<?= e($student['full_name']) ?>" class="rounded-circle me-2 object-fit-cover shadow-sm border" style="width: 36px; height: 36px;">
                                            <?php else: ?>
                                                <div class="avatar-initial me-2">
                                                    <?= strtoupper(substr($student['full_name'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-semibold text-dark"><?= e($student['full_name']) ?></div>
                                                <small class="text-muted"><?= e($student['email']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-dept"><?= e($student['department']) ?></span>
                                    </td>
                                    <td>
                                        <small class="text-secondary"><?= date('M d, Y', strtotime($student['enrollment_date'])) ?></small>
                                    </td>
                                    <td>
                                        <?php if ($student['status'] === 'Active'): ?>
                                            <span class="badge badge-active">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-inactive">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" 
                                                    class="btn btn-outline-dark btn-action btn-qr-student" 
                                                    title="Generate QR Code & Link" 
                                                    data-bs-toggle="tooltip" 
                                                    data-code="<?= e($student['student_id']) ?>" 
                                                    data-name="<?= e($student['full_name']) ?>" 
                                                    data-url="<?= e($verifyBase . urlencode($student['student_id'])) ?>">
                                                <i class="bi bi-qr-code"></i>
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-outline-secondary btn-action btn-view-student" 
                                                    title="View Profile" 
                                                    data-bs-toggle="tooltip" 
                                                    data-student="<?= htmlspecialchars(json_encode($student), ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <a href="<?= BASE_URL ?>/students/edit.php?id=<?= $student['id'] ?>" 
                                               class="btn btn-outline-primary btn-action" 
                                               title="Edit Student" 
                                               data-bs-toggle="tooltip">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-outline-danger btn-action btn-delete-student" 
                                                    title="Delete Student" 
                                                    data-bs-toggle="tooltip" 
                                                    data-id="<?= $student['id'] ?>" 
                                                    data-name="<?= e($student['full_name']) ?>" 
                                                    data-code="<?= e($student['student_id']) ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Sidebar: Department Breakdown & System Info -->
    <div class="col-xl-4">
        <!-- Department Distribution -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>Department Breakdown</h6>
            </div>
            <div class="card-body p-3">
                <?php if (empty($departmentStats)): ?>
                    <p class="text-muted small text-center my-3">No department data available.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($departmentStats as $dept): ?>
                            <?php 
                                $pct = $totalStudents > 0 ? round(($dept['count'] / $totalStudents) * 100) : 0;
                            ?>
                            <li class="list-group-item px-0 py-2 border-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="small fw-medium text-dark"><?= e($dept['department']) ?></span>
                                    <span class="badge bg-light text-dark border"><?= $dept['count'] ?> (<?= $pct ?>%)</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pct ?>%" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick System Status Card -->
        <div class="card border-0 shadow-sm rounded-3 bg-light">
            <div class="card-body p-3">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-shield-check text-success me-2"></i>System Status</h6>
                <div class="small text-secondary mb-2">
                    <div><strong>PHP Version:</strong> <?= phpversion() ?></div>
                    <div><strong>Database Driver:</strong> PDO MySQL</div>
                    <div><strong>Database Name:</strong> <?= DB_NAME ?></div>
                    <div><strong>Server:</strong> <?= e($_SERVER['SERVER_SOFTWARE'] ?? 'Apache/XAMPP') ?></div>
                </div>
                <hr class="my-2">
                <div class="d-grid">
                    <a href="<?= BASE_URL ?>/students/create.php" class="btn btn-sm btn-primary">
                        <i class="bi bi-person-plus me-1"></i> Register New Student
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

