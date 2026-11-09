<?php
/**
 * Student Listing with Search, Filter & Pagination
 * Student Management System
 */

$pageTitle = 'Student Records';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    $pdo = get_db_connection();

    // Query parameters
    $search = sanitize($_GET['search'] ?? '');
    $department = sanitize($_GET['department'] ?? '');
    $status = sanitize($_GET['status'] ?? '');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 8;
    $offset = ($page - 1) * $perPage;

    // Build SQL query dynamically with prepared statements
    $conditions = [];
    $params = [];

    if (!empty($search)) {
        $conditions[] = "(full_name LIKE :search_name OR email LIKE :search_email OR student_id LIKE :search_id OR phone LIKE :search_phone)";
        $params['search_name'] = "%{$search}%";
        $params['search_email'] = "%{$search}%";
        $params['search_id'] = "%{$search}%";
        $params['search_phone'] = "%{$search}%";
    }

    if (!empty($department)) {
        $conditions[] = "department = :dept";
        $params['dept'] = $department;
    }

    if (!empty($status)) {
        $conditions[] = "status = :status";
        $params['status'] = $status;
    }

    $whereSql = '';
    if (!empty($conditions)) {
        $whereSql = ' WHERE ' . implode(' AND ', $conditions);
    }

    // 1. Get total matching records for pagination
    $countSql = "SELECT COUNT(*) FROM students" . $whereSql;
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalRecords = (int)$countStmt->fetchColumn();

    $totalPages = (int)ceil($totalRecords / $perPage);
    if ($totalPages > 0 && $page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $perPage;
    }

    // 2. Fetch page records
    $dataSql = "SELECT * FROM students" . $whereSql . " ORDER BY id DESC LIMIT :limit OFFSET :offset";
    $dataStmt = $pdo->prepare($dataSql);

    // Bind search/filter params
    foreach ($params as $key => $val) {
        $dataStmt->bindValue(':' . $key, $val, PDO::PARAM_STR);
    }
    // Bind limit & offset as integers
    $dataStmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
    $dataStmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $dataStmt->execute();
    $students = $dataStmt->fetchAll();

    // Departments for dropdown filter
    $allDepartments = get_departments();

} catch (PDOException $e) {
    set_flash('danger', 'Error loading students: ' . $e->getMessage());
    $students = [];
    $totalRecords = 0;
    $totalPages = 1;
    $allDepartments = get_departments();
}

// Helper to preserve filter query parameters in links
function filter_query(array $override = []): string {
    $current = $_GET;
    foreach ($override as $key => $val) {
        if ($val === null) {
            unset($current[$key]);
        } else {
            $current[$key] = $val;
        }
    }
    return http_build_query($current);
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$verifyBase = $protocol . $host . BASE_URL . '/students/verify.php?code=';

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Header Actions -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Students</li>
            </ol>
        </nav>
        <h4 class="fw-bold text-dark mb-0">Student Management</h4>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= BASE_URL ?>/students/qr-generator.php" class="btn btn-outline-dark d-inline-flex align-items-center">
            <i class="bi bi-qr-code-scan me-2"></i> QR & AR Generator
        </a>
        <a href="<?= BASE_URL ?>/students/create.php" class="btn btn-primary d-inline-flex align-items-center">
            <i class="bi bi-person-plus-fill me-2"></i> Register New Student
        </a>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="<?= BASE_URL ?>/students/index.php" class="row g-3 align-items-end">
            <!-- Search Input -->
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-secondary">Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" 
                           name="search" 
                           class="form-control border-start-0 ps-0" 
                           placeholder="Search by name, ID, or email..." 
                           value="<?= e($search) ?>">
                </div>
            </div>

            <!-- Department Filter -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary">Department</label>
                <select name="department" class="form-select">
                    <option value="">All Departments</option>
                    <?php foreach ($allDepartments as $dept): ?>
                        <option value="<?= e($dept) ?>" <?= $department === $dept ? 'selected' : '' ?>>
                            <?= e($dept) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary">Enrollment Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="Active" <?= $status === 'Active' ? 'selected' : '' ?>>Active</option>
                    <option value="Inactive" <?= $status === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
                <?php if (!empty($search) || !empty($department) || !empty($status)): ?>
                    <a href="<?= BASE_URL ?>/students/index.php" class="btn btn-outline-secondary" title="Reset Filters" data-bs-toggle="tooltip">
                        <i class="bi bi-x-circle"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Students Table Card -->
<div class="table-card mb-4">
    <div class="p-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h6 class="fw-bold text-dark mb-0">Registered Students</h6>
            <small class="text-muted">
                Showing <strong><?= $totalRecords > 0 ? $offset + 1 : 0 ?></strong> to 
                <strong><?= min($offset + $perPage, $totalRecords) ?></strong> of 
                <strong><?= $totalRecords ?></strong> students
                <?php if (!empty($search) || !empty($department) || !empty($status)): ?>
                    (Filtered)
                <?php endif; ?>
            </small>
        </div>
        <div>
            <span class="badge bg-light text-dark border">
                Page <?= $page ?> of <?= max(1, $totalPages) ?>
            </span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Student Details</th>
                    <th>Contact</th>
                    <th>Department</th>
                    <th>DOB & Gender</th>
                    <th>Enrolled</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="bi bi-person-x empty-state-icon"></i>
                                <h5 class="fw-bold text-dark">No Students Found</h5>
                                <?php if (!empty($search) || !empty($department) || !empty($status)): ?>
                                    <p class="text-muted small mb-3">No student records match your current filter criteria.</p>
                                    <a href="<?= BASE_URL ?>/students/index.php" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Clear All Filters
                                    </a>
                                <?php else: ?>
                                    <p class="text-muted small mb-3">No students have been enrolled yet. Get started by adding your first student.</p>
                                    <a href="<?= BASE_URL ?>/students/create.php" class="btn btn-sm btn-primary">
                                        <i class="bi bi-person-plus-fill me-1"></i> Add Student
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <!-- Student ID -->
                            <td>
                                <span class="badge bg-light text-primary border font-monospace fw-semibold">
                                    <?= e($student['student_id']) ?>
                                </span>
                            </td>

                            <!-- Student Name & Avatar -->
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($student['photo']) && file_exists(dirname(__DIR__) . '/' . ltrim($student['photo'], '/'))): ?>
                                        <img src="<?= BASE_URL . '/' . ltrim(e($student['photo']), '/') ?>" alt="<?= e($student['full_name']) ?>" class="rounded-circle me-2 object-fit-cover shadow-sm border" style="width: 36px; height: 36px;">
                                    <?php else: ?>
                                        <div class="avatar-initial me-2">
                                            <?= strtoupper(substr($student['full_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="fw-semibold text-dark"><?= e($student['full_name']) ?></div>
                                        <small class="text-muted d-block text-truncate" style="max-width: 180px;">
                                            <?= e($student['address'] ?: 'No address provided') ?>
                                        </small>
                                    </div>
                                </div>
                            </td>

                            <!-- Contact -->
                            <td>
                                <div class="small">
                                    <div><a href="mailto:<?= e($student['email']) ?>" class="text-decoration-none text-dark"><?= e($student['email']) ?></a></div>
                                    <div class="text-muted"><i class="bi bi-telephone me-1"></i><?= e($student['phone']) ?></div>
                                </div>
                            </td>

                            <!-- Department -->
                            <td>
                                <span class="badge badge-dept"><?= e($student['department']) ?></span>
                            </td>

                            <!-- DOB & Gender -->
                            <td>
                                <div class="small">
                                    <div><?= e($student['gender']) ?></div>
                                    <div class="text-muted"><?= date('M d, Y', strtotime($student['date_of_birth'])) ?></div>
                                </div>
                            </td>

                            <!-- Enrollment Date -->
                            <td>
                                <small class="text-secondary"><?= date('M d, Y', strtotime($student['enrollment_date'])) ?></small>
                            </td>

                            <!-- Status -->
                            <td>
                                <?php if ($student['status'] === 'Active'): ?>
                                    <span class="badge badge-active"><i class="bi bi-check2 me-1"></i>Active</span>
                                <?php else: ?>
                                    <span class="badge badge-inactive"><i class="bi bi-x me-1"></i>Inactive</span>
                                <?php endif; ?>
                            </td>

                            <!-- Actions -->
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <!-- QR Code & Link Trigger -->
                                    <button type="button" 
                                            class="btn btn-outline-dark btn-action btn-qr-student" 
                                            title="Generate QR Code & Link" 
                                            data-bs-toggle="tooltip" 
                                            data-code="<?= e($student['student_id']) ?>" 
                                            data-name="<?= e($student['full_name']) ?>" 
                                            data-url="<?= e($verifyBase . urlencode($student['student_id'])) ?>">
                                        <i class="bi bi-qr-code"></i>
                                    </button>

                                    <!-- View Modal Trigger -->
                                    <button type="button" 
                                            class="btn btn-outline-secondary btn-action btn-view-student" 
                                            title="View Full Profile" 
                                            data-bs-toggle="tooltip" 
                                            data-student="<?= htmlspecialchars(json_encode($student), ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    <!-- Edit Link -->
                                    <a href="<?= BASE_URL ?>/students/edit.php?id=<?= $student['id'] ?>" 
                                       class="btn btn-outline-primary btn-action" 
                                       title="Edit Student" 
                                       data-bs-toggle="tooltip">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>

                                    <!-- Delete Modal Trigger -->
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

    <!-- Pagination Controls -->
    <?php if ($totalPages > 1): ?>
        <div class="p-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
            <div class="small text-muted">
                Showing page <?= $page ?> of <?= $totalPages ?>
            </div>
            <nav aria-label="Student table pagination">
                <ul class="pagination pagination-sm mb-0">
                    <!-- Previous Button -->
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= filter_query(['page' => $page - 1]) ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo; Previous</span>
                        </a>
                    </li>

                    <!-- Page Numbers -->
                    <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                        <?php if ($p == 1 || $p == $totalPages || ($p >= $page - 2 && $p <= $page + 2)): ?>
                            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= filter_query(['page' => $p]) ?>"><?= $p ?></a>
                            </li>
                        <?php elseif ($p == 2 && $page > 4): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php elseif ($p == $totalPages - 1 && $page < $totalPages - 3): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= filter_query(['page' => $page + 1]) ?>" aria-label="Next">
                            <span aria-hidden="true">Next &raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

