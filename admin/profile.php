<?php
/**
 * Administrator Profile & Security Settings
 * Student Management System
 */

$pageTitle = 'Admin Profile';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();
$admin = current_admin();

$pdo = get_db_connection();
$errors = [];
$successMsg = '';

// Handle Profile Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors['csrf'] = 'Invalid session security token. Please refresh.';
    } else {
        $action = $_POST['action'] ?? 'update_profile';

        if ($action === 'update_profile') {
            $name = sanitize($_POST['name'] ?? '');
            $email = strtolower(sanitize($_POST['email'] ?? ''));

            if (empty($name)) {
                $errors['name'] = 'Admin name cannot be empty.';
            }
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Please provide a valid email address.';
            }

            if (empty($errors)) {
                try {
                    $stmt = $pdo->prepare("UPDATE admins SET name = :name, email = :email WHERE id = :id");
                    $stmt->execute([
                        'name' => $name,
                        'email' => $email,
                        'id' => $admin['id']
                    ]);

                    $_SESSION['admin']['name'] = $name;
                    $_SESSION['admin']['email'] = $email;
                    $admin['name'] = $name;
                    $admin['email'] = $email;

                    set_flash('success', 'Admin profile updated successfully!');
                    header('Location: ' . BASE_URL . '/admin/profile.php');
                    exit;
                } catch (PDOException $e) {
                    $errors['general'] = 'Failed to update profile: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'change_password') {
            $currentPass = (string)($_POST['current_password'] ?? '');
            $newPass = (string)($_POST['new_password'] ?? '');
            $confirmPass = (string)($_POST['confirm_password'] ?? '');

            if (strlen($newPass) < 6) {
                $errors['new_password'] = 'New password must be at least 6 characters.';
            } elseif ($newPass !== $confirmPass) {
                $errors['confirm_password'] = 'Password confirmation does not match.';
            } else {
                try {
                    $hash = password_hash($newPass, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE admins SET password = :pass WHERE id = :id");
                    $stmt->execute([
                        'pass' => $hash,
                        'id' => $admin['id']
                    ]);

                    set_flash('success', 'Your password has been changed successfully!');
                    header('Location: ' . BASE_URL . '/admin/profile.php');
                    exit;
                } catch (PDOException $e) {
                    $errors['general'] = 'Failed to change password: ' . $e->getMessage();
                }
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Admin Profile</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold text-dark mb-0">Administrator Profile</h4>
            <p class="text-secondary small mb-0">Manage your credentials, personal info, and security settings.</p>
        </div>
        <a href="<?= BASE_URL ?>/logout.php" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-box-arrow-right me-1"></i> Sign Out
        </a>
    </div>
</div>

<?php if (!empty($errors['general'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= e($errors['general']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Left Column: Profile Card Overview -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-3 text-center p-4">
            <div class="avatar-initial mx-auto mb-3 bg-primary text-white shadow" style="width: 80px; height: 80px; font-size: 2rem;">
                <?= strtoupper(substr($admin['name'] ?? 'A', 0, 2)) ?>
            </div>
            <h5 class="fw-bold text-dark mb-1"><?= e($admin['name']) ?></h5>
            <p class="text-muted small mb-2"><?= e($admin['email']) ?></p>
            <div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1">
                    <i class="bi bi-shield-check me-1"></i> <?= e($admin['role'] ?? 'Super Admin') ?>
                </span>
            </div>

            <hr class="my-3 text-secondary opacity-25">

            <div class="text-start small">
                <div class="mb-2 text-secondary">
                    <i class="bi bi-person-badge me-2 text-primary"></i> <strong>Admin ID:</strong> #<?= e($admin['id']) ?>
                </div>
                <div class="mb-2 text-secondary">
                    <i class="bi bi-envelope-at me-2 text-primary"></i> <strong>Official Email:</strong> <?= e($admin['email']) ?>
                </div>
                <div class="mb-2 text-secondary">
                    <i class="bi bi-hdd-network me-2 text-primary"></i> <strong>Session Status:</strong> <span class="badge bg-success small">Active</span>
                </div>
                <div class="text-secondary">
                    <i class="bi bi-shield-lock me-2 text-primary"></i> <strong>Auth Protocol:</strong> Bcrypt Hash
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Edit Forms -->
    <div class="col-lg-8">
        
        <!-- General Information Card -->
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-person-circle me-2 text-primary"></i>Personal Information
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>/admin/profile.php" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_profile">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Admin Name</label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                   value="<?= e($admin['name']) ?>" 
                                   required>
                            <?= error_feedback('name', $errors) ?>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                   value="<?= e($admin['email']) ?>" 
                                   required>
                            <?= error_feedback('email', $errors) ?>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Security & Password Change Card -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-shield-lock-fill me-2 text-primary"></i>Change Password
                </h6>
            </div>
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>/admin/profile.php" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="change_password">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" 
                                   name="new_password" 
                                   id="new_password" 
                                   class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" 
                                   placeholder="At least 6 characters" 
                                   required>
                            <?= error_feedback('new_password', $errors) ?>
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" 
                                   name="confirm_password" 
                                   id="confirm_password" 
                                   class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                   placeholder="Re-enter password" 
                                   required>
                            <?= error_feedback('confirm_password', $errors) ?>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-outline-primary px-4">
                            <i class="bi bi-key-fill me-1"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

