<?php
/**
 * SmartPass Digital ID Card & Verification Portal
 * Interactive 3D Tap-to-Flip Card with Front Barcode & Back QR Code
 * Student Management System
 */

$pageTitle = 'SmartPass Digital ID Card';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$code = sanitize($_GET['code'] ?? $_GET['id'] ?? 'STU-2024-008');

$student = null;
if (!empty($code)) {
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = :code OR id = :id LIMIT 1");
        $stmt->execute([
            'code' => $code,
            'id' => is_numeric($code) ? (int)$code : 0
        ]);
        $student = $stmt->fetch();
    } catch (PDOException $e) {
        $student = null;
    }
}

// Current verification URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$currentUrl = $protocol . $host . BASE_URL . '/students/verify.php?code=' . urlencode($student['student_id'] ?? $code);

// Numeric barcode value derived from student ID (e.g., STU-2024-008 -> 2024008)
$numericCode = preg_replace('/[^0-9]/', '', $student['student_id'] ?? '712946');
if (empty($numericCode)) {
    $numericCode = '712946';
}

// Department theme colors
$deptColors = [
    'Information Technology'  => ['header' => '#059669', 'accent' => '#10b981', 'name' => 'Emerald Green'],
    'Computer Science'        => ['header' => '#4f46e5', 'accent' => '#6366f1', 'name' => 'Indigo Blue'],
    'Business Administration' => ['header' => '#d97706', 'accent' => '#f59e0b', 'name' => 'Amber Orange'],
    'Electrical Engineering'  => ['header' => '#2563eb', 'accent' => '#3b82f6', 'name' => 'Royal Blue'],
    'Mechanical Engineering'  => ['header' => '#dc2626', 'accent' => '#ef4444', 'name' => 'Crimson Red'],
    'Civil Engineering'       => ['header' => '#0891b2', 'accent' => '#06b6d4', 'name' => 'Cyan Teal']
];
$activeTheme = $deptColors[$student['department'] ?? ''] ?? ['header' => '#059669', 'accent' => '#10b981', 'name' => 'Emerald Green'];

// Avatar photo selection matching the reference photo or uploaded custom photo
$avatarUrl = get_student_photo_url($student['photo'] ?? null, $student['gender'] ?? 'Female');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $student ? e($student['full_name']) . ' - SmartPass ID Card' : 'Student Verification' ?> | EduManage</title>
    
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- QRCode JS Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    
    <!-- JsBarcode Library for Barcode generation -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

    <!-- html2canvas for 1-Click Card Image Download -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        :root {
            --card-theme-header: <?= $activeTheme['header'] ?>;
            --card-theme-accent: <?= $activeTheme['accent'] ?>;
        }

        body {
            background-color: #12132b;
            background-image: 
                radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.28) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.22) 0px, transparent 50%);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            color: #f8fafc;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        .main-container {
            max-width: 1050px;
            margin: auto;
            padding: 2rem 1rem;
            width: 100%;
        }

        /* SmartPass Brand Title */
        .smartpass-brand {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .smartpass-chevrons {
            color: #818cf8;
            font-weight: 900;
            letter-spacing: -0.15em;
            margin-left: 2px;
        }

        /* -------------------------------------------------------------
           3D Tap-To-Flip Card Container
           ------------------------------------------------------------- */
        .card-flipper-container {
            perspective: 1400px;
            width: 100%;
            max-width: 340px;
            height: 485px;
            margin: 0 auto;
            cursor: pointer;
            user-select: none;
        }

        .card-flipper-inner {
            width: 100%;
            height: 100%;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.75s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border-radius: 28px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7), 0 0 35px rgba(16, 185, 129, 0.18);
        }

        .card-flipper-inner.is-flipped {
            transform: rotateY(180deg);
        }

        /* Front & Back Faces */
        .card-side {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            backface-visibility: hidden;
            -webkit-backface-visibility: hidden;
            border-radius: 28px;
            overflow: hidden;
            background: #ffffff;
            display: flex;
            flex-direction: column;
        }

        .card-side-front {
            z-index: 2;
            transform: rotateY(0deg);
        }

        .card-side-back {
            transform: rotateY(180deg);
            z-index: 1;
        }

        /* Top Colored Header Section (Shared between Front & Back) */
        .card-top-section {
            background: var(--card-theme-header);
            background: linear-gradient(150deg, var(--card-theme-accent) 0%, var(--card-theme-header) 100%);
            padding: 22px 20px 22px;
            color: #ffffff;
            position: relative;
            text-align: center;
        }

        .card-top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.825rem;
            font-weight: 600;
            margin-bottom: 12px;
            letter-spacing: 0.02em;
        }

        .card-school-name {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.925rem;
        }

        /* Side Chevron Indicator Banner */
        .card-side-indicator {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 6px;
            align-items: center;
        }

        .side-chevron-pill {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #ffffff;
            color: var(--card-theme-header);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.05rem;
            font-weight: 900;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .side-chevrons-faded {
            display: flex;
            flex-direction: column;
            gap: 3px;
            opacity: 0.45;
            font-size: 0.95rem;
            font-weight: 800;
        }

        /* Student Photo */
        .student-photo-frame {
            width: 106px;
            height: 106px;
            border-radius: 50%;
            margin: 0 auto 10px;
            padding: 4px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.22);
            position: relative;
            z-index: 2;
        }

        .student-photo-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            display: block;
        }

        .student-photo-initials {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            background: linear-gradient(135deg, #1e1b4b, #4338ca);
            color: #ffffff;
            font-size: 2.2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
        }

        /* Student Name & Role */
        .student-card-name {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.45rem;
            line-height: 1.2;
            margin-bottom: 3px;
            color: #ffffff;
            letter-spacing: -0.01em;
        }

        .student-card-role {
            font-size: 0.85rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.92);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        /* Bottom White Section */
        .card-bottom-section {
            background: #ffffff;
            padding: 16px 20px 18px;
            color: #1e293b;
            text-align: center;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Pass Badges Row (Trophy, Coffee, Book, Wifi) */
        .card-badges-row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .activity-badge {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #ffffff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }

        .badge-trophy { background: #ea580c; }
        .badge-coffee { background: #8b5cf6; }
        .badge-book { background: #0284c7; }
        .badge-wifi { background: #059669; }

        /* FRONT: Barcode View */
        .barcode-frame {
            margin: auto 0;
            text-align: center;
        }

        #frontBarcode {
            max-width: 100%;
            height: 60px;
        }

        .barcode-number-text {
            font-family: 'Inter', monospace;
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: 0.18em;
            color: #0f172a;
            margin-top: -2px;
        }

        /* BACK: QR Code View (Matching User Image) */
        .back-qr-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            margin: auto 0;
        }

        .qr-box-bordered {
            background: #ffffff;
            padding: 4px;
            display: inline-block;
        }

        .qr-id-meta {
            text-align: left;
        }

        .qr-id-code {
            font-family: 'Inter', monospace;
            font-weight: 600;
            font-size: 0.95rem;
            color: #334155;
            margin-bottom: 8px;
        }

        .badge-verified-pill {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
            font-weight: 700;
            font-size: 0.775rem;
            padding: 0.35rem 0.8rem;
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            letter-spacing: 0.04em;
        }

        /* Glass Control Panels */
        .glass-panel {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 22px;
            backdrop-filter: blur(14px);
            padding: 1.75rem;
        }

        /* Theme Buttons */
        .theme-pill {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            border: 2px solid #ffffff;
            cursor: pointer;
            transition: transform 0.15s;
        }
        .theme-pill:hover {
            transform: scale(1.18);
        }

        @media print {
            body { background: white !important; color: black !important; }
            .no-print { display: none !important; }
            .card-flipper-container { height: auto; perspective: none; }
            .card-side { position: relative; margin-bottom: 30px; box-shadow: none; border: 1px solid #ccc; }
            .card-side-back { transform: none; }
        }
    </style>
</head>
<body>

    <div class="main-container">
        
        <!-- Header Nav & Branding -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-white text-dark rounded-4 p-2 px-3 shadow d-flex align-items-center">
                    <i class="bi bi-person-badge-fill fs-3 text-primary me-2"></i>
                    <div>
                        <span class="smartpass-brand fs-4 text-dark mb-0">SmartPass</span>
                        <span class="smartpass-chevrons fs-4">&raquo;&raquo;</span>
                    </div>
                </div>
                <h4 class="text-white fw-bold mb-0 d-none d-sm-block">Digital ID Cards</h4>
            </div>
            
            <div class="d-flex gap-2">
                <?php if (is_logged_in()): ?>
                    <a href="<?= BASE_URL ?>/students/qr-generator.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                        <i class="bi bi-qr-code-scan me-1"></i> QR & Barcode Generator
                    </a>
                    <a href="<?= BASE_URL ?>/students/index.php" class="btn btn-primary btn-sm rounded-pill px-3">
                        <i class="bi bi-speedometer2 me-1"></i> Admin Portal
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login.php" class="btn btn-outline-light btn-sm rounded-pill px-3">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Admin Sign In
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$student): ?>
            <!-- Student Not Found State -->
            <div class="glass-panel text-center my-5 py-5">
                <i class="bi bi-person-x fs-1 text-danger mb-3 d-block"></i>
                <h3 class="fw-bold text-white mb-2">Student Record Not Found</h3>
                <p class="text-light text-opacity-75 mb-4">No student matching code <code><?= e($code) ?></code> exists in the database.</p>
                <a href="<?= BASE_URL ?>/students/index.php" class="btn btn-primary rounded-pill px-4">
                    Return to Directory
                </a>
            </div>
        <?php else: ?>

            <div class="row g-4 align-items-center">
                
                <!-- Left Column: The SmartPass 3D Tap-To-Flip ID Card -->
                <div class="col-lg-5 text-center">

                    <!-- 3D Card Flipper Container -->
                    <div class="card-flipper-container" id="flipperContainer" onclick="toggleCardFlip()">
                        <div class="card-flipper-inner" id="cardFlipper">
                            
                            <!-- ==============================================
                                 FRONT SIDE: PHOTO + DETAILS + BARCODE
                                 ============================================== -->
                            <div class="card-side card-side-front" id="cardFrontSide">
                                <!-- Top Section -->
                                <div class="card-top-section theme-header-area">
                                    <div class="card-top-header">
                                        <div class="card-school-name">
                                            <i class="bi bi-mortarboard-fill"></i>
                                            <span>EduManage Academy</span>
                                        </div>
                                        <div>2024-2026</div>
                                    </div>

                                    <!-- Side Chevron Indicator -->
                                    <div class="card-side-indicator">
                                        <div class="side-chevron-pill theme-chevron-pill">
                                            <i class="bi bi-chevron-double-right"></i>
                                        </div>
                                        <div class="side-chevrons-faded">
                                            <i class="bi bi-chevron-double-right"></i>
                                            <i class="bi bi-chevron-double-right"></i>
                                            <i class="bi bi-chevron-double-right"></i>
                                        </div>
                                    </div>

                                    <!-- Student Photo -->
                                    <div class="student-photo-frame">
                                        <img src="<?= e($avatarUrl) ?>" 
                                             alt="<?= e($student['full_name']) ?>" 
                                             class="student-photo-img" 
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="student-photo-initials" style="display: none;">
                                            <?= strtoupper(substr($student['full_name'], 0, 1)) ?>
                                        </div>
                                    </div>

                                    <!-- Student Name & Role -->
                                    <div class="student-card-name"><?= e($student['full_name']) ?></div>
                                    <div class="student-card-role">
                                        <span>Student</span>
                                        <span>&bull;</span>
                                        <span><?= e($student['department']) ?></span>
                                    </div>
                                </div>

                                <!-- Bottom Section (Front: Barcode) -->
                                <div class="card-bottom-section">
                                    <!-- 4 Activity Badges -->
                                    <div class="card-badges-row">
                                        <div class="activity-badge badge-trophy" title="Sports & Athletics">
                                            <i class="bi bi-trophy-fill"></i>
                                        </div>
                                        <div class="activity-badge badge-coffee" title="Cafeteria & Dining">
                                            <i class="bi bi-cup-hot-fill"></i>
                                        </div>
                                        <div class="activity-badge badge-book" title="Library Pass">
                                            <i class="bi bi-book-fill"></i>
                                        </div>
                                        <div class="activity-badge badge-wifi" title="Campus Wi-Fi">
                                            <i class="bi bi-wifi"></i>
                                        </div>
                                    </div>

                                    <!-- FRONT BARCODE -->
                                    <div class="barcode-frame my-auto">
                                        <svg id="frontBarcode"></svg>
                                        <div class="barcode-number-text"><?= e($numericCode) ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- ==============================================
                                 BACK SIDE: PHOTO + DETAILS + QR CODE
                                 ============================================== -->
                            <div class="card-side card-side-back" id="cardBackSide">
                                <!-- Top Section -->
                                <div class="card-top-section theme-header-area">
                                    <div class="card-top-header">
                                        <div class="card-school-name">
                                            <i class="bi bi-mortarboard-fill"></i>
                                            <span>EduManage Academy</span>
                                        </div>
                                        <div>2024-2026</div>
                                    </div>

                                    <!-- Side Chevron Indicator -->
                                    <div class="card-side-indicator">
                                        <div class="side-chevron-pill theme-chevron-pill">
                                            <i class="bi bi-chevron-double-right"></i>
                                        </div>
                                        <div class="side-chevrons-faded">
                                            <i class="bi bi-chevron-double-right"></i>
                                            <i class="bi bi-chevron-double-right"></i>
                                            <i class="bi bi-chevron-double-right"></i>
                                        </div>
                                    </div>

                                    <!-- Student Photo -->
                                    <div class="student-photo-frame">
                                        <img src="<?= e($avatarUrl) ?>" 
                                             alt="<?= e($student['full_name']) ?>" 
                                             class="student-photo-img" 
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="student-photo-initials" style="display: none;">
                                            <?= strtoupper(substr($student['full_name'], 0, 1)) ?>
                                        </div>
                                    </div>

                                    <!-- Student Name & Role -->
                                    <div class="student-card-name"><?= e($student['full_name']) ?></div>
                                    <div class="student-card-role">
                                        <span>Student</span>
                                        <span>&bull;</span>
                                        <span><?= e($student['department']) ?></span>
                                    </div>
                                </div>

                                <!-- Bottom Section (Back: QR Code + Verification) -->
                                <div class="card-bottom-section">
                                    <!-- 4 Activity Badges -->
                                    <div class="card-badges-row">
                                        <div class="activity-badge badge-trophy" title="Sports & Athletics">
                                            <i class="bi bi-trophy-fill"></i>
                                        </div>
                                        <div class="activity-badge badge-coffee" title="Cafeteria & Dining">
                                            <i class="bi bi-cup-hot-fill"></i>
                                        </div>
                                        <div class="activity-badge badge-book" title="Library Pass">
                                            <i class="bi bi-book-fill"></i>
                                        </div>
                                        <div class="activity-badge badge-wifi" title="Campus Wi-Fi">
                                            <i class="bi bi-wifi"></i>
                                        </div>
                                    </div>

                                    <!-- BACK QR CODE (Matching Reference Image) -->
                                    <div class="back-qr-row my-auto">
                                        <div class="qr-box-bordered">
                                            <div id="backQrCode"></div>
                                        </div>
                                        <div class="qr-id-meta">
                                            <div class="qr-id-code">ID: <?= e($student['student_id']) ?></div>
                                            <div>
                                                <span class="badge-verified-pill">
                                                    <i class="bi bi-check-circle-fill"></i> VERIFIED
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>

                <!-- Right Column: Verification Details & Action Controls -->
                <div class="col-lg-7 no-print">
                    <div class="glass-panel">
                        
                        <!-- Verification Status Banner -->
                        <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom border-white border-opacity-10">
                            <div class="badge bg-success-subtle text-success p-3 rounded-circle fs-3">
                                <i class="bi bi-shield-fill-check"></i>
                            </div>
                            <div>
                                <span class="badge bg-success text-white mb-1"><i class="bi bi-check2-circle me-1"></i>OFFICIALLY VERIFIED</span>
                                <h4 class="fw-bold text-white mb-0">SmartPass Digital ID Authenticated</h4>
                                <small class="text-light text-opacity-75">Active Institutional Credential for Academic Year 2024-2026</small>
                            </div>
                        </div>

                        <!-- Theme Palette Switcher -->
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-uppercase text-light text-opacity-75 d-flex justify-content-between">
                                <span><i class="bi bi-palette-fill me-1 text-warning"></i> Card Theme Color</span>
                                <span class="text-light text-opacity-50" id="currentThemeName"><?= e($activeTheme['name']) ?></span>
                            </label>
                            <div class="d-flex gap-2">
                                <button class="theme-pill" style="background: #059669;" onclick="setCardTheme('#059669', '#10b981', 'Emerald Green')" title="Emerald Green"></button>
                                <button class="theme-pill" style="background: #d97706;" onclick="setCardTheme('#d97706', '#f59e0b', 'Amber Orange')" title="Amber Orange"></button>
                                <button class="theme-pill" style="background: #4f46e5;" onclick="setCardTheme('#4f46e5', '#6366f1', 'Indigo Blue')" title="Indigo Blue"></button>
                                <button class="theme-pill" style="background: #dc2626;" onclick="setCardTheme('#dc2626', '#ef4444', 'Crimson Red')" title="Crimson Red"></button>
                                <button class="theme-pill" style="background: #0891b2;" onclick="setCardTheme('#0891b2', '#06b6d4', 'Cyan Teal')" title="Cyan Teal"></button>
                                <button class="theme-pill" style="background: #0f172a;" onclick="setCardTheme('#0f172a', '#334155', 'Midnight Slate')" title="Midnight Slate"></button>
                            </div>
                        </div>

                        <!-- Verification URL -->
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-uppercase text-light text-opacity-75">
                                <i class="bi bi-link-45deg me-1 text-primary"></i> Verification URL
                            </label>
                            <div class="input-group">
                                <input type="text" id="verifyUrlInput" class="form-control bg-dark text-light border-secondary font-monospace small" value="<?= e($currentUrl) ?>" readonly>
                                <button class="btn btn-primary" type="button" onclick="copyVerifyUrl()">
                                    <i class="bi bi-clipboard me-1" id="copyBtnIcon"></i> <span id="copyBtnText">Copy Link</span>
                                </button>
                            </div>
                        </div>

                        <!-- Quick Export & Action Buttons -->
                        <div class="d-flex flex-wrap gap-2 pt-2 border-top border-white border-opacity-10">
                            <button type="button" class="btn btn-light rounded-pill px-3" onclick="downloadActiveCardSide()">
                                <i class="bi bi-image-fill me-1 text-primary"></i> Download Card Image (.PNG)
                            </button>
                            <button type="button" class="btn btn-outline-light rounded-pill px-3" onclick="window.print()">
                                <i class="bi bi-printer-fill me-1"></i> Print ID Badge
                            </button>
                            <a href="<?= BASE_URL ?>/students/edit.php?id=<?= $student['id'] ?>" class="btn btn-outline-light rounded-pill px-3">
                                <i class="bi bi-pencil-square me-1"></i> Edit Student Details
                            </a>
                        </div>

                    </div>
                </div>

            </div>

        <?php endif; ?>

        <!-- Footer Notice -->
        <div class="text-center text-light text-opacity-50 small mt-5 pt-3 no-print">
            SmartPass Academy &bull; Integrated RFID, Barcode & QR Credential System &bull; <?= date('Y') ?>
        </div>

    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php if ($student): ?>
    <script>
        const verifyUrl = "<?= e($currentUrl) ?>";
        const numericCode = "<?= e($numericCode) ?>";
        const studentCode = "<?= e($student['student_id']) ?>";
        const cardFlipper = document.getElementById('cardFlipper');

        document.addEventListener('DOMContentLoaded', function () {
            // 1. Generate Front Barcode (Code-128 format)
            try {
                JsBarcode("#frontBarcode", numericCode, {
                    format: "CODE128",
                    lineColor: "#0f172a",
                    width: 1.85,
                    height: 52,
                    displayValue: false,
                    margin: 0
                });
            } catch (e) {
                console.error("Barcode generation error:", e);
            }

            // 2. Generate Back QR Code
            new QRCode(document.getElementById("backQrCode"), {
                text: verifyUrl,
                width: 96,
                height: 96,
                colorDark: "#0f172a",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        });

        // Toggle 3D Card Flip
        function toggleCardFlip() {
            cardFlipper.classList.toggle('is-flipped');
        }

        // Change Theme Color
        function setCardTheme(headerColor, accentColor, themeName) {
            document.querySelectorAll('.theme-header-area').forEach(el => {
                el.style.background = `linear-gradient(150deg, ${accentColor} 0%, ${headerColor} 100%)`;
            });
            document.querySelectorAll('.theme-chevron-pill').forEach(el => {
                el.style.color = headerColor;
            });
            document.getElementById('currentThemeName').textContent = themeName;
        }

        // Copy Verification URL
        function copyVerifyUrl() {
            const input = document.getElementById('verifyUrlInput');
            navigator.clipboard.writeText(input.value).then(() => {
                const icon = document.getElementById('copyBtnIcon');
                const text = document.getElementById('copyBtnText');
                icon.className = 'bi bi-check-lg text-success';
                text.textContent = 'Copied!';
                setTimeout(() => {
                    icon.className = 'bi bi-clipboard';
                    text.textContent = 'Copy Link';
                }, 2000);
            });
        }

        // Download currently visible card side as PNG
        function downloadActiveCardSide() {
            const isFlipped = cardFlipper.classList.contains('is-flipped');
            const targetSide = isFlipped ? document.getElementById('cardBackSide') : document.getElementById('cardFrontSide');
            const suffix = isFlipped ? 'Back-QR' : 'Front-Barcode';

            html2canvas(targetSide, {
                scale: 3,
                useCORS: true,
                backgroundColor: null,
                borderRadius: 28
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = `SmartPass-${studentCode}-${suffix}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        }
    </script>
    <?php endif; ?>

</body>
</html>
