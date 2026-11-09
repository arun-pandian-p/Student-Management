<?php
/**
 * Header Component with Responsive Topbar & Admin Profile
 * Student Management System
 */

require_once __DIR__ . '/functions.php';

// Protect administrative pages
require_login();
$adminUser = current_admin();

$pageTitle = $pageTitle ?? 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - Student Management System</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Application CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>

    <!-- Responsive Sidebar Navigation -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="main-wrapper">
        
        <!-- Top Navbar with Responsive Toggler & Admin Dropdown -->
        <header class="top-navbar">
            <button class="sidebar-toggler me-3 d-lg-none" type="button" aria-label="Toggle Navigation" id="mobileSidebarToggle">
                <i class="bi bi-list fs-4"></i>
            </button>

            <div class="d-flex align-items-center">
                <h5 class="mb-0 fw-bold text-dark"><?= e($pageTitle) ?></h5>
            </div>

            <div class="ms-auto d-flex align-items-center gap-2 gap-sm-3">
                <a href="<?= BASE_URL ?>/students/create.php" class="btn btn-sm btn-primary d-none d-sm-inline-flex align-items-center">
                    <i class="bi bi-plus-lg me-1"></i> New Student
                </a>
                
                <div class="vr d-none d-sm-block text-secondary"></div>
                
                <!-- Admin Profile Dropdown (Arun) -->
                <div class="dropdown">
                    <button class="btn btn-light btn-sm d-flex align-items-center gap-2 border-0 bg-transparent text-secondary p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="avatar-initial bg-primary text-white shadow-sm" style="width: 34px; height: 34px; font-size: 0.825rem; font-weight: 700;">
                            <?= strtoupper(substr($adminUser['name'] ?? 'A', 0, 2)) ?>
                        </div>
                        <div class="d-none d-md-block text-start lh-1">
                            <span class="fw-bold text-dark d-block" style="font-size: 0.875rem;"><?= e($adminUser['name'] ?? 'Arun') ?></span>
                            <small class="text-muted" style="font-size: 0.725rem;"><?= e($adminUser['role'] ?? 'Super Admin') ?></small>
                        </div>
                        <i class="bi bi-chevron-down small text-muted ms-1"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-3" style="min-width: 210px;">
                        <li class="px-3 py-2 bg-light border-bottom rounded-top-3">
                            <div class="fw-bold text-dark small"><?= e($adminUser['name'] ?? 'Arun') ?></div>
                            <div class="text-muted small text-truncate"><?= e($adminUser['email'] ?? 'arun@gmail.com') ?></div>
                        </li>
                        <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/admin/profile.php"><i class="bi bi-person-circle me-2 text-primary"></i>Admin Profile</a></li>
                        <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/students/qr-generator.php"><i class="bi bi-qr-code-scan me-2 text-indigo"></i>QR & AR Badges</a></li>
                        <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>/index.php"><i class="bi bi-speedometer2 me-2 text-secondary"></i>Dashboard</a></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item py-2 text-danger" href="<?= BASE_URL ?>/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>Sign Out
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <main class="content-area">
            <?php render_flash(); ?>
