<?php
/**
 * Administrator Logout Action
 * Student Management System
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Clear admin session
if (isset($_SESSION['admin'])) {
    unset($_SESSION['admin']);
}

session_regenerate_id(true);
set_flash('success', 'You have been logged out securely.');
header('Location: ' . BASE_URL . '/login.php');
exit;

