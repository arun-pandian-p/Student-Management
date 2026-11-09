<?php
/**
 * Delete Student Action Handler
 * Student Management System
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

// Only accept POST requests to prevent CSRF / prefetch deletions
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    set_flash('danger', 'Method not allowed. Deletion requires a valid POST request.');
    header('Location: ' . BASE_URL . '/students/index.php');
    exit;
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    set_flash('danger', 'Invalid student record requested for deletion.');
    header('Location: ' . BASE_URL . '/students/index.php');
    exit;
}

// 1. Verify CSRF Token
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    set_flash('danger', 'Security validation failed (invalid CSRF token). Please try again.');
    header('Location: ' . BASE_URL . '/students/index.php');
    exit;
}

try {
    $pdo = get_db_connection();

    // Check if student exists before deleting
    $stmt = $pdo->prepare("SELECT student_id, full_name, photo FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $student = $stmt->fetch();

    if (!$student) {
        set_flash('warning', 'Student record was not found or has already been deleted.');
        header('Location: ' . BASE_URL . '/students/index.php');
        exit;
    }

    // Delete photo file if present
    if (!empty($student['photo'])) {
        $photoFilePath = dirname(__DIR__) . '/' . ltrim($student['photo'], '/');
        if (file_exists($photoFilePath) && is_file($photoFilePath)) {
            @unlink($photoFilePath);
        }
    }

    // Delete record using prepared statement
    $delStmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $delStmt->execute([$id]);

    set_flash('success', "Student {$student['student_id']} ({$student['full_name']}) has been permanently deleted.");
    header('Location: ' . BASE_URL . '/students/index.php');
    exit;

} catch (PDOException $e) {
    set_flash('danger', 'Unable to delete student record: ' . $e->getMessage());
    header('Location: ' . BASE_URL . '/students/index.php');
    exit;
}

