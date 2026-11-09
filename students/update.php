<?php
/**
 * Update Student Action Handler
 * Student Management System
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/students/index.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    set_flash('danger', 'Invalid student identifier provided.');
    header('Location: ' . BASE_URL . '/students/index.php');
    exit;
}

// 1. Verify CSRF Token
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('danger', 'Invalid or expired session security token. Please try again.');
    header('Location: ' . BASE_URL . '/students/edit.php?id=' . $id);
    exit;
}

try {
    $pdo = get_db_connection();

    // Check if student exists
    $checkStmt = $pdo->prepare("SELECT id, student_id, photo FROM students WHERE id = ?");
    $checkStmt->execute([$id]);
    $existing = $checkStmt->fetch();

    if (!$existing) {
        set_flash('danger', 'Student record does not exist or was deleted.');
        header('Location: ' . BASE_URL . '/students/index.php');
        exit;
    }

    // 2. Validate input fields (passing $id so unique email check ignores current record)
    $errors = validate_student($_POST, $pdo, $id);

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_input'] = $_POST;
        header('Location: ' . BASE_URL . '/students/edit.php?id=' . $id);
        exit;
    }

    // Handle Photo Upload (with old photo replacement)
    $existingPhoto = $existing['photo'] ?? ($_POST['existing_photo'] ?? null);
    $uploadResult = handle_student_photo_upload($_FILES['photo'] ?? null, $existingPhoto);

    if (!$uploadResult['success']) {
        $_SESSION['errors'] = ['photo' => $uploadResult['error']];
        $_SESSION['old_input'] = $_POST;
        header('Location: ' . BASE_URL . '/students/edit.php?id=' . $id);
        exit;
    }
    $photoPath = $uploadResult['path'];

    // 3. Update student record using prepared statement
    $sql = "UPDATE students SET 
            full_name = :full_name,
            email = :email,
            phone = :phone,
            department = :department,
            date_of_birth = :date_of_birth,
            gender = :gender,
            photo = :photo,
            address = :address,
            enrollment_date = :enrollment_date,
            status = :status
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'id'              => $id,
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

    set_flash('success', "Student {$existing['student_id']} (" . sanitize($_POST['full_name']) . ") updated successfully!");
    header('Location: ' . BASE_URL . '/students/index.php');
    exit;

} catch (PDOException $e) {
    // Check for duplicate key error (MySQL 1062)
    if ($e->getCode() == 23000) {
        $_SESSION['errors'] = ['email' => 'This email address is already in use by another student.'];
        $_SESSION['old_input'] = $_POST;
        header('Location: ' . BASE_URL . '/students/edit.php?id=' . $id);
        exit;
    }

    set_flash('danger', 'Failed to update student: ' . $e->getMessage());
    $_SESSION['old_input'] = $_POST;
    header('Location: ' . BASE_URL . '/students/edit.php?id=' . $id);
    exit;
}

