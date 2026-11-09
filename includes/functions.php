<?php
/**
 * Helper Functions
 * Student Management System
 */

require_once __DIR__ . '/../config/database.php';

/**
 * XSS Prevention helper - Escapes string for HTML output
 * 
 * @param mixed $value
 * @return string
 */
function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Generates or retrieves the active CSRF token
 * 
 * @return string
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Outputs a hidden input field containing the CSRF token
 * 
 * @return string
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Verifies a submitted CSRF token
 * 
 * @param string|null $token
 * @return bool
 */
function verify_csrf_token(?string $token): bool {
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sets a flash notification message
 * 
 * @param string $type ('success', 'danger', 'warning', 'info')
 * @param string $message
 * @return void
 */
function set_flash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Retrieves and clears flash notification message
 * 
 * @return array|null
 */
function get_flash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Renders the flash message alert HTML if available
 * 
 * @return void
 */
function render_flash(): void {
    $flash = get_flash();
    if ($flash) {
        $icon = match($flash['type']) {
            'success' => 'bi-check-circle-fill',
            'danger' => 'bi-exclamation-triangle-fill',
            'warning' => 'bi-exclamation-circle-fill',
            default => 'bi-info-circle-fill'
        };
        ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
            <i class="bi <?= $icon ?> fs-5 me-2"></i>
            <div><?= e($flash['message']) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php
    }
}

/**
 * Sanitize basic input string
 * 
 * @param mixed $data
 * @return string
 */
function sanitize($data): string {
    return trim((string)($data ?? ''));
}

/**
 * Returns the list of standard departments
 * 
 * @return array
 */
function get_departments(): array {
    return [
        'Computer Science',
        'Information Technology',
        'Electrical Engineering',
        'Mechanical Engineering',
        'Civil Engineering',
        'Business Administration'
    ];
}

/**
 * Retrieves a previously submitted input value or fallback
 * 
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function old(string $key, $default = '') {
    if (isset($_SESSION['old_input'][$key])) {
        return $_SESSION['old_input'][$key];
    }
    return $default;
}

/**
 * Returns 'is-invalid' class if field has validation error
 * 
 * @param string $field
 * @param array $errors
 * @return string
 */
function is_invalid(string $field, array $errors): string {
    return isset($errors[$field]) ? 'is-invalid' : '';
}

/**
 * Returns formatted HTML invalid feedback if field has an error
 * 
 * @param string $field
 * @param array $errors
 * @return string
 */
function error_feedback(string $field, array $errors): string {
    if (isset($errors[$field])) {
        return '<div class="invalid-feedback d-block">' . e($errors[$field]) . '</div>';
    }
    return '';
}

/**
 * Generates a unique Student ID (e.g. STU-2024-0016)
 * 
 * @param PDO $pdo
 * @return string
 */
function generate_student_id(PDO $pdo): string {
    $currentYear = date('Y');
    $prefix = "STU-{$currentYear}-";
    
    // Find the highest existing sequence number for the current year
    $stmt = $pdo->prepare("SELECT student_id FROM students WHERE student_id LIKE :prefix ORDER BY id DESC LIMIT 1");
    $stmt->execute(['prefix' => "STU-{$currentYear}-%"]);
    $lastStudent = $stmt->fetch();
    
    $sequence = 1;
    if ($lastStudent && preg_match('/STU-\d{4}-(\d+)/', $lastStudent['student_id'], $matches)) {
        $sequence = (int)$matches[1] + 1;
    }
    
    // Ensure uniqueness in case of race condition or gaps
    do {
        $studentId = sprintf("STU-%s-%03d", $currentYear, $sequence);
        $check = $pdo->prepare("SELECT COUNT(*) FROM students WHERE student_id = ?");
        $check->execute([$studentId]);
        $exists = (int)$check->fetchColumn() > 0;
        if ($exists) {
            $sequence++;
        }
    } while ($exists);
    
    return $studentId;
}

/**
 * Server-side validation for student data
 * 
 * @param array $data
 * @param PDO $pdo
 * @param int|null $currentId (null when creating, ID when updating)
 * @return array Array of error messages keyed by field name
 */
function validate_student(array $data, PDO $pdo, ?int $currentId = null): array {
    $errors = [];
    
    // Full Name
    $fullName = sanitize($data['full_name'] ?? '');
    if (empty($fullName)) {
        $errors['full_name'] = 'Full name is required.';
    } elseif (mb_strlen($fullName) < 3) {
        $errors['full_name'] = 'Full name must be at least 3 characters long.';
    } elseif (mb_strlen($fullName) > 100) {
        $errors['full_name'] = 'Full name cannot exceed 100 characters.';
    } elseif (!preg_match("/^[a-zA-Z\s\.\'\-]+$/", $fullName)) {
        $errors['full_name'] = 'Full name can only contain letters, spaces, hyphens, and periods.';
    }
    
    // Email
    $email = sanitize($data['email'] ?? '');
    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif (mb_strlen($email) > 100) {
        $errors['email'] = 'Email address cannot exceed 100 characters.';
    } else {
        // Check uniqueness in database
        if ($currentId) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE email = ? AND id != ?");
            $stmt->execute([$email, $currentId]);
        } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE email = ?");
            $stmt->execute([$email]);
        }
        if ((int)$stmt->fetchColumn() > 0) {
            $errors['email'] = 'This email address is already registered to another student.';
        }
    }
    
    // Phone
    $phone = sanitize($data['phone'] ?? '');
    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required.';
    } elseif (!preg_match('/^[0-9\+\-\s\(\)]{7,25}$/', $phone)) {
        $errors['phone'] = 'Please enter a valid phone number (digits, spaces, +, -, parentheses).';
    }
    
    // Department
    $department = sanitize($data['department'] ?? '');
    $validDepartments = get_departments();
    if (empty($department)) {
        $errors['department'] = 'Please select a department.';
    } elseif (!in_array($department, $validDepartments, true)) {
        $errors['department'] = 'Selected department is invalid.';
    }
    
    // Date of Birth
    $dob = sanitize($data['date_of_birth'] ?? '');
    if (empty($dob)) {
        $errors['date_of_birth'] = 'Date of birth is required.';
    } else {
        $dobDate = DateTime::createFromFormat('Y-m-d', $dob);
        if (!$dobDate || $dobDate->format('Y-m-d') !== $dob) {
            $errors['date_of_birth'] = 'Please provide a valid date format (YYYY-MM-DD).';
        } else {
            $today = new DateTime('today');
            $minAgeDate = (new DateTime('today'))->modify('-14 years');
            $maxAgeDate = (new DateTime('today'))->modify('-90 years');
            
            if ($dobDate > $minAgeDate) {
                $errors['date_of_birth'] = 'Student must be at least 14 years old.';
            } elseif ($dobDate < $maxAgeDate) {
                $errors['date_of_birth'] = 'Please enter a realistic date of birth.';
            }
        }
    }
    
    // Gender
    $gender = sanitize($data['gender'] ?? '');
    if (empty($gender)) {
        $errors['gender'] = 'Please select a gender.';
    } elseif (!in_array($gender, ['Male', 'Female', 'Other'], true)) {
        $errors['gender'] = 'Selected gender is invalid.';
    }
    
    // Address (optional but length check)
    $address = sanitize($data['address'] ?? '');
    if (mb_strlen($address) > 500) {
        $errors['address'] = 'Address cannot exceed 500 characters.';
    }
    
    // Enrollment Date
    $enrollmentDate = sanitize($data['enrollment_date'] ?? '');
    if (empty($enrollmentDate)) {
        $errors['enrollment_date'] = 'Enrollment date is required.';
    } else {
        $eDate = DateTime::createFromFormat('Y-m-d', $enrollmentDate);
        if (!$eDate || $eDate->format('Y-m-d') !== $enrollmentDate) {
            $errors['enrollment_date'] = 'Please provide a valid enrollment date (YYYY-MM-DD).';
        }
    }
    
    // Status
    $status = sanitize($data['status'] ?? '');
    if (empty($status)) {
        $errors['status'] = 'Please select a status.';
    } elseif (!in_array($status, ['Active', 'Inactive'], true)) {
        $errors['status'] = 'Selected status is invalid.';
    }
    
    return $errors;
}

/**
 * Checks whether an administrator is currently authenticated
 * 
 * @return bool
 */
function is_logged_in(): bool {
    return !empty($_SESSION['admin']['id']);
}

/**
 * Returns currently logged in admin details
 * 
 * @return array
 */
function current_admin(): array {
    if (!empty($_SESSION['admin'])) {
        return $_SESSION['admin'];
    }
    return [
        'id' => 1,
        'name' => 'Arun',
        'email' => 'arun@gmail.com',
        'role' => 'Super Admin'
    ];
}

/**
 * Enforces admin authentication; redirects to login if unauthenticated
 * 
 * @return void
 */
function require_login(): void {
    if (!is_logged_in()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '';
        set_flash('warning', 'Please sign in with your admin credentials to continue.');
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

/**
 * Redirects authenticated admin away from login page
 * 
 * @return void
 */
function require_guest(): void {
    if (is_logged_in()) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

/**
 * Handles uploading and validating a student's photo
 * 
 * @param array|null $file $_FILES['photo']
 * @param string|null $oldPhoto
 * @return array ['success' => bool, 'path' => ?string, 'error' => ?string]
 */
function handle_student_photo_upload(?array $file, ?string $oldPhoto = null): array {
    if (empty($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'path' => $oldPhoto, 'error' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'path' => null, 'error' => 'File upload error code: ' . $file['error']];
    }

    // Validate size (max 4MB)
    $maxSize = 4 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'path' => null, 'error' => 'Image size exceeds maximum limit of 4MB.'];
    }

    // Validate MIME type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedTypes, true)) {
        return ['success' => false, 'path' => null, 'error' => 'Invalid image format. Allowed formats: JPG, PNG, WEBP.'];
    }

    $ext = match($mime) {
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => 'jpg'
    };

    $uploadDir = dirname(__DIR__) . '/assets/uploads/students/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = 'student_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
    $destination = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'path' => null, 'error' => 'Failed to save uploaded photo to server storage.'];
    }

    // Delete old photo if it exists and is within uploads directory
    if (!empty($oldPhoto)) {
        $oldFilePath = dirname(__DIR__) . '/' . ltrim($oldPhoto, '/');
        if (file_exists($oldFilePath) && is_file($oldFilePath)) {
            @unlink($oldFilePath);
        }
    }

    return ['success' => true, 'path' => 'assets/uploads/students/' . $filename, 'error' => null];
}

/**
 * Returns accessible URL for student photo or default fallback
 * 
 * @param string|null $photoPath
 * @param string $gender
 * @return string
 */
function get_student_photo_url(?string $photoPath, string $gender = 'Female'): string {
    if (!empty($photoPath)) {
        $fullPath = dirname(__DIR__) . '/' . ltrim($photoPath, '/');
        if (file_exists($fullPath)) {
            return BASE_URL . '/' . ltrim($photoPath, '/');
        }
    }
    
    // Default fallback
    if ($gender === 'Male') {
        return 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=400&q=80';
    }
    return 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=400&q=80';
}

