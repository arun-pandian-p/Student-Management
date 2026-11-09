<?php
/**
 * Store New Student Action Handler
 * Student Management System
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/students/create.php');
    exit;
}

// 1. Verify CSRF Token
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('danger', 'Invalid or expired session security token. Please try again.');
    header('Location: ' . BASE_URL . '/students/create.php');
    exit;
}

try {
    $pdo = get_db_connection();

    // 2. Validate input fields
    $errors = validate_student($_POST, $pdo);

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_input'] = $_POST;
        header('Location: ' . BASE_URL . '/students/create.php');
        exit;
    }

    // Handle Photo Upload
    $uploadResult = handle_student_photo_upload($_FILES['photo'] ?? null);

    if (!$uploadResult['success']) {
        $_SESSION['errors'] = ['photo' => $uploadResult['error']];
        $_SESSION['old_input'] = $_POST;
        header('Location: ' . BASE_URL . '/students/create.php');
        exit;
    }
    $photoPath = $uploadResult['path'];

    // 3. Generate unique student ID
    $studentId = generate_student_id($pdo);

    // 4. Insert student record using prepared statement
    $sql = "INSERT INTO students 
            (student_id, full_name, email, phone, department, date_of_birth, gender, photo, address, enrollment_date, status) 
            VALUES 
            (:student_id, :full_name, :email, :phone, :department, :date_of_birth, :gender, :photo, :address, :enrollment_date, :status)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'student_id'      => $studentId,
        'full_name'       => sanitize($_POST['full_name']),
        'email'           => strtolower(sanitize($_POST['email'])),
        'phone'           => sanitize($_POST['phone']),
        'department'      => sanitize($_POST['department']),
        'date_of_birth'   => sanitize($_POST['date_of_birth']),
        'gender'          => sanitize($_POST['gender']),
        'photo'           => $photoPath,
        'address'         => sanitize($_POST['address']),
        'enrollment_date' => sanitize($_POST['enrollment_date']),
        'status'          => sanitize($_POST['status']),
    ]);

    set_flash('success', "Student {$studentId} (" . sanitize($_POST['full_name']) . ") registered successfully!");
    header('Location: ' . BASE_URL . '/students/index.php');
    exit;

} catch (PDOException $e) {
    // Check for duplicate key error (MySQL 1062)
    if ($e->getCode() == 23000) {
        $_SESSION['errors'] = ['email' => 'This email address is already in use by another student.'];
        $_SESSION['old_input'] = $_POST;
        header('Location: ' . BASE_URL . '/students/create.php');
        exit;
    }

    set_flash('danger', 'Failed to create student: ' . $e->getMessage());
    $_SESSION['old_input'] = $_POST;
    header('Location: ' . BASE_URL . '/students/create.php');
    exit;
}

