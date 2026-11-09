<?php
/**
 * Administrator Login Screen - Modern Half-to-Half Zero-Scroll Redesign
 * Student Management System
 */

$pageTitle = 'Admin Login';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
require_guest();

$error = '';
$emailInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($token)) {
        $error = 'Security session expired. Please refresh and try again.';
    } else {
        $email = strtolower(sanitize($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $emailInput = $email;

        if (empty($email) || empty($password)) {
            $error = 'Please enter both your email address and password.';
        } else {
            try {
                $pdo = get_db_connection();
                $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = :email LIMIT 1");
                $stmt->execute(['email' => $email]);
                $admin = $stmt->fetch();

                $isValid = false;
                if ($admin) {
                    if (password_verify($password, $admin['password']) || ($email === 'arun@gmail.com' && $password === 'arun4709s')) {
                        $isValid = true;
                    }
                } elseif ($email === 'arun@gmail.com' && $password === 'arun4709s') {
                    $isValid = true;
                    $admin = [
                        'id' => 1,
                        'name' => 'Arun',
                        'email' => 'arun@gmail.com',
                        'role' => 'Super Admin'
                    ];
                }

                if ($isValid) {
                    $_SESSION['admin'] = [
                        'id' => $admin['id'] ?? 1,
                        'name' => $admin['name'] ?? 'Arun',
                        'email' => $admin['email'] ?? 'arun@gmail.com',
                        'role' => $admin['role'] ?? 'Super Admin'
                    ];

                    set_flash('success', "Welcome back, " . htmlspecialchars($admin['name'] ?? 'Arun') . "! You have successfully signed in.");

                    $redirect = $_SESSION['redirect_after_login'] ?? BASE_URL . '/index.php';
                    unset($_SESSION['redirect_after_login']);
                    header('Location: ' . $redirect);
                    exit;
                } else {
                    $error = 'Invalid email address or password. Please verify your credentials.';
                }
            } catch (PDOException $e) {
                if ($email === 'arun@gmail.com' && $password === 'arun4709s') {
                    $_SESSION['admin'] = [
                        'id' => 1,
                        'name' => 'Arun',
                        'email' => 'arun@gmail.com',
                        'role' => 'Super Admin'
                    ];
                    set_flash('success', "Welcome back, Arun! Logged in successfully.");
                    header('Location: ' . BASE_URL . '/index.php');
                    exit;
                }
                $error = 'Authentication error: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - EduManage Student Portal</title>
    
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --primary-purple: #6366f1;
            --primary-dark: #4f46e5;
            --bg-lavender: #f1f3fd;
        }

        /* Strict No-Scroll Fullscreen Viewport */
        html, body {
            height: 100vh;
            width: 100vw;
            margin: 0;
            padding: 0;
            overflow: hidden;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-lavender);
            background-image: 
                radial-gradient(at 10% 15%, rgba(99, 102, 241, 0.16) 0px, transparent 40%),
                radial-gradient(at 90% 85%, rgba(139, 92, 246, 0.16) 0px, transparent 40%);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        /* Decorative Floating Background Shapes */
        .decor-circle-1 {
            position: absolute;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: #6366f1;
            top: 40px;
            left: 60px;
            opacity: 0.85;
            z-index: 1;
        }

        .decor-circle-2 {
            position: absolute;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: #ffffff;
            bottom: 40px;
            right: 60px;
            opacity: 0.9;
            z-index: 1;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        /* Main Split 50/50 Card (Fits 100% inside 100vh) */
        .split-card-container {
            width: 92%;
            max-width: 940px;
            height: auto;
            max-height: 92vh;
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 20px 50px -12px rgba(99, 102, 241, 0.22), 0 0 1px 1px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            position: relative;
            z-index: 2;
            display: flex;
        }

        /* Left Half: Form */
        .split-left-panel {
            flex: 1 1 50%;
            padding: 2.25rem 2.75rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        /* Right Half: Purple Showcase with Graphic & Photo */
        .split-right-panel {
            flex: 1 1 50%;
            background: linear-gradient(145deg, #6b66f6 0%, #4f46e5 100%);
            padding: 2.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Wavy Organic Texture */
        .split-right-panel::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -20%;
            width: 140%;
            height: 140%;
            background: radial-gradient(ellipse at center, rgba(255, 255, 255, 0.12) 0%, transparent 65%);
            pointer-events: none;
        }

        /* Inner Glass Card around Photo */
        .hero-photo-wrapper {
            position: relative;
            width: 100%;
            max-width: 300px;
            height: 330px;
            border-radius: 24px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.18);
            border: 2px solid rgba(255, 255, 255, 0.35);
            backdrop-filter: blur(14px);
            box-shadow: 0 16px 36px rgba(30, 27, 75, 0.3);
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .hero-student-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: top center;
            transition: transform 0.4s ease;
        }

        .hero-photo-wrapper:hover .hero-student-img {
            transform: scale(1.03);
        }

        /* Floating Accent Badge */
        .floating-badge {
            position: absolute;
            left: -18px;
            bottom: 65px;
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #ffffff;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: #f59e0b;
            z-index: 3;
            animation: pulse-badge 3s infinite ease-in-out;
        }

        @keyframes pulse-badge {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.08); }
        }

        /* Typography & Form Elements */
        .login-main-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.85rem;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin-bottom: 2px;
            line-height: 1.1;
        }

        .login-subtitle {
            color: #64748b;
            font-size: 0.85rem;
            margin-bottom: 1rem;
            line-height: 1.35;
        }

        .custom-input-group {
            background: #f4f5fd;
            border: 1px solid #e0e4f6;
            border-radius: 12px;
            padding: 2px 12px;
            display: flex;
            align-items: center;
            transition: all 0.2s ease;
            margin-bottom: 0.85rem;
        }

        .custom-input-group:focus-within {
            background: #ffffff;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
        }

        .custom-input-group i {
            color: #64748b;
            font-size: 1.05rem;
            margin-right: 10px;
        }

        .custom-input {
            border: none;
            background: transparent;
            width: 100%;
            padding: 8px 0;
            font-size: 0.925rem;
            color: #1e293b;
            outline: none;
        }

        .custom-input::placeholder {
            color: #94a3b8;
        }

        .password-toggle-btn {
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 2px;
            font-size: 1rem;
        }
        .password-toggle-btn:hover {
            color: #4f46e5;
        }

        /* Submit Button */
        .btn-login-now {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            width: 100%;
            box-shadow: 0 6px 18px rgba(99, 102, 241, 0.32);
            transition: all 0.2s ease;
            margin-top: 0.25rem;
        }

        .btn-login-now:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            transform: translateY(-1px);
            box-shadow: 0 8px 22px rgba(99, 102, 241, 0.4);
            color: #ffffff;
        }

        /* Quick Credentials Box */
        .quick-cred-chip {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 6px 12px;
            margin-bottom: 0.9rem;
            font-size: 0.775rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Social / Alternative Buttons */
        .social-divider {
            position: relative;
            text-align: center;
            margin: 0.9rem 0 0.75rem;
        }

        .social-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
        }

        .social-divider span {
            position: relative;
            background: #ffffff;
            padding: 0 10px;
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 500;
        }

        .social-buttons-row {
            display: flex;
            gap: 8px;
        }

        .btn-social {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 7px 10px;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            color: #334155;
            text-decoration: none;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .btn-social:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            html, body {
                height: auto;
                min-height: 100vh;
                overflow-y: auto;
                padding: 1.5rem 1rem;
            }
            .split-card-container {
                max-width: 480px;
                border-radius: 24px;
                flex-direction: column;
            }
            .split-left-panel {
                flex: 1 1 100%;
                padding: 2rem 1.75rem;
            }
            .split-right-panel {
                display: none;
            }
            .decor-circle-1, .decor-circle-2 {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- Floating Background Shapes -->
    <div class="decor-circle-1"></div>
    <div class="decor-circle-2"></div>

    <div class="split-card-container">
        
        <!-- LEFT HALF: Compact Zero-Scroll Login Form -->
        <div class="split-left-panel">
            
            <div class="mb-1">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill small mb-2 d-inline-block" style="font-size: 0.725rem;">
                    <i class="bi bi-mortarboard-fill me-1"></i> EduManage System
                </span>
                <h2 class="login-main-title">LOGIN</h2>
                <p class="login-subtitle">How to get started? Enter your administrator credentials below.</p>
            </div>

            <!-- Flash alerts (e.g. Logout confirmation) -->
            <?php render_flash(); ?>

            <!-- Error Feedback -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger d-flex align-items-center small py-1 px-3 mb-2 rounded-3" role="alert" style="font-size: 0.8rem;">
                    <i class="bi bi-exclamation-triangle-fill fs-6 me-2 flex-shrink-0"></i>
                    <div><?= e($error) ?></div>
                </div>
            <?php endif; ?>

            <!-- Quick Credentials Helper -->
            <div class="quick-cred-chip">
                <div>
                    <span class="fw-bold text-dark"><i class="bi bi-key-fill text-primary me-1"></i> Default Admin:</span>
                    <span class="text-secondary font-monospace">arun@gmail.com</span> &bull;
                    <span class="text-secondary font-monospace">arun4709s</span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0" style="font-size: 0.725rem;" onclick="fillCredentials()">
                    <i class="bi bi-magic me-1"></i> Fill
                </button>
            </div>

            <!-- Login Form -->
            <form action="<?= BASE_URL ?>/login.php" method="POST" novalidate>
                <?= csrf_field() ?>

                <!-- Username / Email Input -->
                <div class="custom-input-group">
                    <i class="bi bi-person"></i>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           class="custom-input" 
                           placeholder="Username or Email" 
                           value="<?= e($emailInput ?: 'arun@gmail.com') ?>" 
                           required 
                           autofocus>
                </div>

                <!-- Password Input -->
                <div class="custom-input-group">
                    <i class="bi bi-lock"></i>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           class="custom-input" 
                           placeholder="Password" 
                           value="arun4709s" 
                           required>
                    <button class="password-toggle-btn" type="button" onclick="togglePasswordVisibility()" aria-label="Toggle password visibility">
                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>

                <!-- Remember Me & Student Pass Link -->
                <div class="d-flex justify-content-between align-items-center mb-2 px-1" style="font-size: 0.8rem;">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="rememberMe" checked>
                        <label class="form-check-label text-secondary" for="rememberMe">Remember me</label>
                    </div>
                    <a href="<?= BASE_URL ?>/students/verify.php?code=STU-2024-008" class="text-primary text-decoration-none fw-medium">
                        Student Pass &raquo;
                    </a>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-login-now">
                    Login Now
                </button>

                <!-- Social / Quick Access Links (Side-by-side row) -->
                <div class="social-divider">
                    <span>Login with Others</span>
                </div>

                <div class="social-buttons-row">
                    <a href="javascript:void(0)" onclick="fillCredentials()" class="btn-social">
                        <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="16" height="16" alt="Google">
                        <span>Admin Login</span>
                    </a>

                    <a href="<?= BASE_URL ?>/students/verify.php?code=STU-2024-008" class="btn-social">
                        <i class="bi bi-qr-code text-primary"></i>
                        <span>SmartPass</span>
                    </a>
                </div>

            </form>
        </div>

        <!-- RIGHT HALF: Purple Showcase with Photo & Floating Badge -->
        <div class="split-right-panel">
            <div class="hero-photo-wrapper">
                <!-- Floating Glow Badge from Reference Design -->
                <div class="floating-badge">
                    <i class="bi bi-lightning-charge-fill"></i>
                </div>
                <!-- Student / Professional Image -->
                <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=800&q=80" 
                     alt="EduManage Student Portal" 
                     class="hero-student-img">
            </div>
        </div>

    </div>

    <script>
        function fillCredentials() {
            document.getElementById('email').value = 'arun@gmail.com';
            document.getElementById('password').value = 'arun4709s';
        }

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePasswordIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.className = 'bi bi-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleIcon.className = 'bi bi-eye';
            }
        }
    </script>
</body>
</html>
