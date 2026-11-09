<?php
/**
 * Edit Student Details Form
 * Student Management System
 */

$pageTitle = 'Edit Student Details';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    set_flash('danger', 'Invalid student ID specified.');
    header('Location: ' . BASE_URL . '/students/index.php');
    exit;
}

try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();

    if (!$student) {
        set_flash('danger', 'Student record not found.');
        header('Location: ' . BASE_URL . '/students/index.php');
        exit;
    }

    $departments = get_departments();

} catch (PDOException $e) {
    set_flash('danger', 'Error accessing student record: ' . $e->getMessage());
    header('Location: ' . BASE_URL . '/students/index.php');
    exit;
}

// Retrieve and clear flash errors / old input from session
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

$old = $_SESSION['old_input'] ?? [];
unset($_SESSION['old_input']);

// Merge existing student record with any previous invalid submission
$formData = array_merge($student, $old);

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/students/index.php" class="text-decoration-none">Students</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit <?= e($student['student_id']) ?></li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="fw-bold text-dark mb-0">
            Edit Student: <span class="text-primary font-monospace"><?= e($student['student_id']) ?></span>
        </h4>
        <a href="<?= BASE_URL ?>/students/index.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Student List
        </a>
    </div>
</div>

<!-- Validation General Alert -->
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
            <strong>Please correct the errors indicated below.</strong>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Edit Student Form Card -->
<div class="form-card">
    <form action="<?= BASE_URL ?>/students/update.php" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= e($student['id']) ?>">
        <input type="hidden" name="existing_photo" value="<?= e($formData['photo'] ?? '') ?>">

        <!-- Section 1: Academic & Identification Info -->
        <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
            <i class="bi bi-mortarboard-fill text-primary fs-5 me-2"></i>
            <h6 class="fw-bold text-dark mb-0">Academic & Identification</h6>
        </div>

        <div class="row g-3 mb-4">
            <!-- Student ID (Read Only) -->
            <div class="col-md-4">
                <label class="form-label">Student ID</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock-fill text-muted"></i></span>
                    <input type="text" class="form-control font-monospace bg-light" value="<?= e($student['student_id']) ?>" readonly disabled>
                </div>
                <small class="text-muted">Student ID cannot be modified.</small>
            </div>

            <!-- Department -->
            <div class="col-md-4">
                <label for="department" class="form-label">Department <span class="required-star">*</span></label>
                <select name="department" id="department" class="form-select <?= is_invalid('department', $errors) ?>" required>
                    <option value="">-- Select Department --</option>
                    <?php 
                        $selectedDept = $formData['department'] ?? '';
                        foreach ($departments as $dept): 
                    ?>
                        <option value="<?= e($dept) ?>" <?= $selectedDept === $dept ? 'selected' : '' ?>>
                            <?= e($dept) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?= error_feedback('department', $errors) ?>
            </div>

            <!-- Enrollment Date -->
            <div class="col-md-4">
                <label for="enrollment_date" class="form-label">Enrollment Date <span class="required-star">*</span></label>
                <input type="date" 
                       name="enrollment_date" 
                       id="enrollment_date" 
                       class="form-control <?= is_invalid('enrollment_date', $errors) ?>" 
                       value="<?= e($formData['enrollment_date'] ?? '') ?>" 
                       required>
                <?= error_feedback('enrollment_date', $errors) ?>
            </div>
        </div>

        <!-- Section 2: Personal Information -->
        <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
            <i class="bi bi-person-lines-fill text-primary fs-5 me-2"></i>
            <h6 class="fw-bold text-dark mb-0">Personal Details</h6>
        </div>

        <!-- Student Photo Drag & Drop Zone -->
        <div class="row g-3 mb-4 align-items-center">
            <div class="col-md-3 text-center">
                <label class="form-label d-block text-secondary small fw-semibold">Photo Preview</label>
                <div class="position-relative d-inline-block">
                    <img id="photoPreviewImg" 
                         src="<?= e(get_student_photo_url($formData['photo'] ?? null, $formData['gender'] ?? 'Female')) ?>" 
                         alt="Student Photo" 
                         class="rounded-circle shadow-sm border border-3 border-white object-fit-cover" 
                         style="width: 104px; height: 104px; object-fit: cover;">
                    <span class="position-absolute bottom-0 end-0 badge rounded-pill bg-primary p-2 border border-white shadow-sm" title="Photo Avatar">
                        <i class="bi bi-camera-fill"></i>
                    </span>
                </div>
            </div>
            <div class="col-md-9">
                <label class="form-label">Student Photo <span class="badge bg-light text-primary border">Drag & Drop Supported</span></label>
                <div class="drag-drop-zone p-3 text-center rounded-3" id="dropZone" onclick="document.getElementById('photoInput').click()">
                    <i class="bi bi-cloud-arrow-up-fill fs-2 text-primary d-block mb-1"></i>
                    <div class="fw-semibold text-dark small mb-1">
                        Drag & Drop student photo here, or <span class="text-primary text-decoration-underline fw-bold">Browse Files</span>
                    </div>
                    <div class="text-muted" style="font-size: 0.775rem;">Supports JPG, PNG, WEBP (Max 4MB)</div>
                    <input type="file" name="photo" id="photoInput" class="d-none" accept="image/jpeg,image/png,image/webp">
                    <div id="dropFileName" class="mt-2 small fw-bold text-success d-none">
                        <i class="bi bi-check-circle-fill me-1"></i> <span id="fileNameText"></span>
                    </div>
                </div>
                <?= error_feedback('photo', $errors) ?>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <!-- Full Name -->
            <div class="col-md-6">
                <label for="full_name" class="form-label">Full Name <span class="required-star">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                    <input type="text" 
                           name="full_name" 
                           id="full_name" 
                           class="form-control <?= is_invalid('full_name', $errors) ?>" 
                           placeholder="Full name" 
                           value="<?= e($formData['full_name'] ?? '') ?>" 
                           maxlength="100" 
                           required>
                </div>
                <?= error_feedback('full_name', $errors) ?>
            </div>

            <!-- Email Address -->
            <div class="col-md-6">
                <label for="email" class="form-label">Email Address <span class="required-star">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           class="form-control <?= is_invalid('email', $errors) ?>" 
                           placeholder="student@university.edu" 
                           value="<?= e($formData['email'] ?? '') ?>" 
                           maxlength="100" 
                           required>
                </div>
                <?= error_feedback('email', $errors) ?>
            </div>

            <!-- Phone Number -->
            <div class="col-md-4">
                <label for="phone" class="form-label">Phone Number <span class="required-star">*</span></label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-telephone"></i></span>
                    <input type="tel" 
                           name="phone" 
                           id="phone" 
                           class="form-control <?= is_invalid('phone', $errors) ?>" 
                           placeholder="+1 (555) 000-0000" 
                           value="<?= e($formData['phone'] ?? '') ?>" 
                           maxlength="25" 
                           required>
                </div>
                <?= error_feedback('phone', $errors) ?>
            </div>

            <!-- Date of Birth -->
            <div class="col-md-4">
                <label for="date_of_birth" class="form-label">Date of Birth <span class="required-star">*</span></label>
                <input type="date" 
                       name="date_of_birth" 
                       id="date_of_birth" 
                       class="form-control <?= is_invalid('date_of_birth', $errors) ?>" 
                       value="<?= e($formData['date_of_birth'] ?? '') ?>" 
                       max="<?= date('Y-m-d', strtotime('-14 years')) ?>" 
                       required>
                <?= error_feedback('date_of_birth', $errors) ?>
            </div>

            <!-- Gender -->
            <div class="col-md-4">
                <label class="form-label">Gender <span class="required-star">*</span></label>
                <div class="d-flex align-items-center gap-3 pt-2">
                    <?php $selectedGender = $formData['gender'] ?? 'Male'; ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="gender" id="genderMale" value="Male" <?= $selectedGender === 'Male' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="genderMale">Male</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="Female" <?= $selectedGender === 'Female' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="genderFemale">Female</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="gender" id="genderOther" value="Other" <?= $selectedGender === 'Other' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="genderOther">Other</label>
                    </div>
                </div>
                <?= error_feedback('gender', $errors) ?>
            </div>

            <!-- Residential Address -->
            <div class="col-12">
                <label for="address" class="form-label">Residential Address</label>
                <textarea name="address" 
                          id="address" 
                          rows="3" 
                          class="form-control <?= is_invalid('address', $errors) ?>" 
                          placeholder="Street, City, State, ZIP code..."><?= e($formData['address'] ?? '') ?></textarea>
                <?= error_feedback('address', $errors) ?>
            </div>
        </div>

        <!-- Section 3: Status & Confirmation -->
        <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
            <i class="bi bi-toggle-on text-primary fs-5 me-2"></i>
            <h6 class="fw-bold text-dark mb-0">Status & Save</h6>
        </div>

        <div class="row g-3 align-items-center mb-4">
            <div class="col-md-6">
                <label for="status" class="form-label">Enrollment Status <span class="required-star">*</span></label>
                <?php $selectedStatus = $formData['status'] ?? 'Active'; ?>
                <select name="status" id="status" class="form-select <?= is_invalid('status', $errors) ?>" required>
                    <option value="Active" <?= $selectedStatus === 'Active' ? 'selected' : '' ?>>Active (Currently Enrolled)</option>
                    <option value="Inactive" <?= $selectedStatus === 'Inactive' ? 'selected' : '' ?>>Inactive (Suspended / On Leave / Graduated)</option>
                </select>
                <?= error_feedback('status', $errors) ?>
            </div>
        </div>

        <!-- Form Submit Buttons -->
        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
            <button type="button" 
                    class="btn btn-outline-danger btn-delete-student" 
                    data-id="<?= $student['id'] ?>" 
                    data-name="<?= e($student['full_name']) ?>" 
                    data-code="<?= e($student['student_id']) ?>">
                <i class="bi bi-trash me-1"></i> Delete Record
            </button>
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/students/index.php" class="btn btn-light px-4">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save me-1"></i> Update Student
                </button>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dropZone = document.getElementById('dropZone');
    const photoInput = document.getElementById('photoInput');
    const photoPreviewImg = document.getElementById('photoPreviewImg');
    const dropFileName = document.getElementById('dropFileName');
    const fileNameText = document.getElementById('fileNameText');

    if (!dropZone || !photoInput) return;

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('dragover');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files && files.length > 0) {
            photoInput.files = files;
            handleFileSelect(files[0]);
        }
    });

    photoInput.addEventListener('change', function () {
        if (this.files && this.files.length > 0) {
            handleFileSelect(this.files[0]);
        }
    });

    function handleFileSelect(file) {
        if (!file.type.match('image.*')) {
            alert('Please select an image file (JPG, PNG, or WEBP).');
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            if (photoPreviewImg) {
                photoPreviewImg.src = e.target.result;
            }
            if (dropFileName && fileNameText) {
                fileNameText.textContent = file.name + ' (' + Math.round(file.size / 1024) + ' KB)';
                dropFileName.classList.remove('d-none');
            }
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

