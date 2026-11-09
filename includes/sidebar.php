<?php
/**
 * Responsive Sidebar Navigation Component
 * Student Management System
 */

$currentPage = basename($_SERVER['PHP_SELF'] ?? '');
$currentDir = basename(dirname($_SERVER['PHP_SELF'] ?? ''));

$isDashboard = ($currentPage === 'index.php' && $currentDir !== 'students' && $currentDir !== 'admin');
$isStudentList = ($currentDir === 'students' && $currentPage === 'index.php');
$isStudentCreate = ($currentDir === 'students' && $currentPage === 'create.php');
$isQrGenerator = ($currentDir === 'students' && $currentPage === 'qr-generator.php');
$isAdminProfile = ($currentDir === 'admin' && $currentPage === 'profile.php');

$currentAdmin = current_admin();
?>
<aside class="sidebar" id="appSidebar">
    <!-- Brand / Logo Header -->
    <div class="brand d-flex justify-content-between align-items-center">
        <a href="<?= BASE_URL ?>/index.php" class="text-white text-decoration-none d-flex align-items-center">
            <i class="bi bi-mortarboard-fill me-2 fs-4 text-primary"></i>
            <span class="fw-bold tracking-wide">EduManage</span>
        </a>
        <button type="button" class="btn-close btn-close-white d-lg-none" id="closeSidebarBtn" aria-label="Close Sidebar"></button>
    </div>

    <!-- Navigation Menu Items -->
    <ul class="sidebar-menu">
        <li class="menu-header">Overview</li>
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/index.php" class="nav-link <?= $isDashboard ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="menu-header">Student Management</li>
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/students/index.php" class="nav-link <?= $isStudentList ? 'active' : '' ?>">
                <i class="bi bi-people-fill"></i>
                <span>All Students</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/students/create.php" class="nav-link <?= $isStudentCreate ? 'active' : '' ?>">
                <i class="bi bi-person-plus-fill"></i>
                <span>Add Student</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/students/qr-generator.php" class="nav-link <?= $isQrGenerator ? 'active' : '' ?>">
                <i class="bi bi-qr-code-scan"></i>
                <span>QR & AR Generator</span>
            </a>
        </li>

        <li class="menu-header">Administration</li>
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/admin/profile.php" class="nav-link <?= $isAdminProfile ? 'active' : '' ?>">
                <i class="bi bi-person-badge-fill"></i>
                <span>Admin Profile</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/database/schema.sql" download class="nav-link">
                <i class="bi bi-database-down"></i>
                <span>Export Schema</span>
            </a>
        </li>
    </ul>

    <!-- Bottom Admin Profile Widget (High Contrast & Clickable) -->
    <div class="sidebar-footer">
        <div class="d-flex align-items-center justify-content-between">
            <a href="<?= BASE_URL ?>/admin/profile.php" class="d-flex align-items-center text-decoration-none overflow-hidden" title="View Admin Profile">
                <div class="avatar-initial me-2 bg-primary text-white shadow-sm flex-shrink-0" style="width: 40px; height: 40px; font-size: 0.9rem; font-weight: 700;">
                    <?= strtoupper(substr($currentAdmin['name'] ?? 'A', 0, 2)) ?>
                </div>
                <div class="overflow-hidden">
                    <div class="text-white small fw-bold text-truncate"><?= e($currentAdmin['name'] ?? 'Arun') ?></div>
                    <div class="text-light text-opacity-75 text-truncate" style="font-size: 0.775rem;"><?= e($currentAdmin['email'] ?? 'arun@gmail.com') ?></div>
                </div>
            </a>
            <a href="<?= BASE_URL ?>/logout.php" class="btn btn-sm btn-outline-light border-0 p-1 text-danger ms-1" title="Sign Out" data-bs-toggle="tooltip">
                <i class="bi bi-box-arrow-right fs-5"></i>
            </a>
        </div>
    </div>
</aside>
