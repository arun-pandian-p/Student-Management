<?php
/**
 * Dedicated QR Code & AR Digital Badge Generator Tool
 * Student Management System
 */

$pageTitle = 'QR & AR Generator';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = get_db_connection();

// Fetch all students for quick selection
$stmt = $pdo->query("SELECT id, student_id, full_name, email, department, status FROM students ORDER BY full_name ASC");
$students = $stmt->fetchAll();

// Determine if a specific student is pre-selected via query param
$selectedStudentId = sanitize($_GET['student_id'] ?? '');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseVerifyUrl = $protocol . $host . BASE_URL . '/students/verify.php?code=';

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumbs -->
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/students/index.php" class="text-decoration-none">Students</a></li>
            <li class="breadcrumb-item active" aria-current="page">QR & AR Generator</li>
        </ol>
    </nav>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div>
            <h4 class="fw-bold text-dark mb-0">Student QR Code & AR Badge Generator</h4>
            <p class="text-secondary small mb-0">Generate scannable verification QR codes, shareable links, and 3D digital badges.</p>
        </div>
        <div>
            <a href="<?= BASE_URL ?>/students/index.php" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-people-fill me-1"></i> View All Students
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Generator Controls Column -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-sliders me-2 text-primary"></i>Configuration & Source
                </h6>
            </div>
            <div class="card-body p-4">
                <!-- Mode Toggle -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Generation Mode</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="genMode" id="modeStudent" value="student" checked>
                        <label class="btn btn-outline-primary" for="modeStudent"><i class="bi bi-person-badge me-1"></i> Student Profile</label>
                        
                        <input type="radio" class="btn-check" name="genMode" id="modeCustom" value="custom">
                        <label class="btn btn-outline-primary" for="modeCustom"><i class="bi bi-link-45deg me-1"></i> Custom URL / Text</label>
                    </div>
                </div>

                <!-- Student Select Box -->
                <div id="studentSelectWrapper" class="mb-3">
                    <label for="studentSelect" class="form-label small fw-semibold text-secondary">Select Student</label>
                    <select id="studentSelect" class="form-select">
                        <option value="">-- Choose a Student --</option>
                        <?php foreach ($students as $stu): ?>
                            <option value="<?= e($stu['student_id']) ?>" 
                                    data-name="<?= e($stu['full_name']) ?>"
                                    data-dept="<?= e($stu['department']) ?>"
                                    data-email="<?= e($stu['email']) ?>"
                                    data-status="<?= e($stu['status']) ?>"
                                    <?= $selectedStudentId === $stu['student_id'] ? 'selected' : '' ?>>
                                <?= e($stu['full_name']) ?> (<?= e($stu['student_id']) ?>) &bull; <?= e($stu['department']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Custom Text / URL Input Box -->
                <div id="customInputWrapper" class="mb-3 d-none">
                    <label for="customContentInput" class="form-label small fw-semibold text-secondary">Custom Target URL / Text</label>
                    <textarea id="customContentInput" rows="2" class="form-control" placeholder="https://university.edu/student-verify"></textarea>
                </div>

                <!-- Direct Generated Link Output -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Target Verification Link</label>
                    <div class="input-group">
                        <input type="text" id="targetLinkInput" class="form-control font-monospace bg-light small" readonly>
                        <button class="btn btn-primary" type="button" id="copyLinkBtn">
                            <i class="bi bi-clipboard me-1"></i> <span id="copyLinkBtnText">Copy</span>
                        </button>
                    </div>
                </div>

                <hr class="my-3 text-secondary opacity-25">

                <!-- Styling Controls -->
                <h6 class="fw-bold text-dark small mb-3"><i class="bi bi-palette-fill me-2 text-primary"></i>Customization</h6>
                
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label for="qrFgColor" class="form-label small text-secondary">QR Color</label>
                        <div class="input-group">
                            <input type="color" id="qrFgColor" class="form-control form-control-color w-100" value="#1e1b4b">
                        </div>
                    </div>
                    <div class="col-6">
                        <label for="qrBgColor" class="form-label small text-secondary">Background</label>
                        <div class="input-group">
                            <input type="color" id="qrBgColor" class="form-control form-control-color w-100" value="#ffffff">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="qrSizeSlider" class="form-label small text-secondary d-flex justify-content-between">
                        <span>Resolution</span>
                        <span id="sizeLabel" class="fw-bold text-dark">220px</span>
                    </label>
                    <input type="range" class="form-range" id="qrSizeSlider" min="140" max="360" step="20" value="220">
                </div>
            </div>
        </div>
    </div>

    <!-- Live Preview Column -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-qr-code-scan me-2 text-primary"></i>Live QR Code & Badge Preview
                </h6>
                <span class="badge bg-success-subtle text-success border border-success-subtle" id="previewBadgeStatus">
                    Ready
                </span>
            </div>
            <div class="card-body p-4 text-center">
                
                <!-- QR Code Display Box -->
                <div class="p-4 bg-light rounded-3 d-inline-block border shadow-sm mb-3">
                    <div id="liveQrContainer" class="d-flex justify-content-center align-items-center"></div>
                </div>

                <!-- Target Meta Info -->
                <div id="studentMetaBox" class="mb-3">
                    <h5 class="fw-bold text-dark mb-0" id="previewStudentName">Select a student above</h5>
                    <p class="text-muted small mb-1" id="previewStudentDept">Choose from the dropdown or enter custom text to preview.</p>
                    <span class="badge bg-light text-primary border font-monospace" id="previewStudentId">-</span>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex flex-wrap justify-content-center gap-2 pt-3 border-top">
                    <button type="button" id="downloadQrBtn" class="btn btn-outline-primary px-3">
                        <i class="bi bi-download me-1"></i> Download PNG
                    </button>
                    <a href="#" id="openVerificationLink" target="_blank" class="btn btn-primary px-3">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Open Verification Page
                    </a>
                </div>

            </div>
        </div>

        <!-- 3D AR Digital Student Card Preview -->
        <div class="card border-0 shadow-sm rounded-3 mt-4" id="card3dSection">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-badge-ad-fill me-2 text-indigo" style="color: #6366f1;"></i>Interactive 3D / AR Digital Card
                </h6>
                <small class="text-muted"><i class="bi bi-hand-index-thumb me-1"></i>Click card to flip</small>
            </div>
            <div class="card-body p-4 d-flex justify-content-center" style="background: #0f172a; border-radius: 0 0 12px 12px;">
                <div class="card-3d-preview-wrapper" style="perspective: 1000px; width: 100%; max-width: 400px; height: 240px; cursor: pointer;">
                    <div class="card-3d-inner" style="width: 100%; height: 100%; position: relative; transform-style: preserve-3d; transition: transform 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275); border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.6);">
                        
                        <!-- Front Face -->
                        <div class="p-3 text-white d-flex flex-column justify-content-between" style="position: absolute; width: 100%; height: 100%; backface-visibility: hidden; border-radius: 16px; background: linear-gradient(135deg, #1e1b4b 0%, #4f46e5 100%); border: 1px solid rgba(255,255,255,0.2);">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-mortarboard-fill text-warning fs-6"></i>
                                    <span class="fw-bold small text-white" style="letter-spacing: 0.05em;">EDUMANAGE ID</span>
                                </div>
                                <div style="width: 36px; height: 26px; background: linear-gradient(135deg, #ffd700, #ff8c00); border-radius: 4px;"></div>
                            </div>
                            <div class="d-flex align-items-center gap-3 my-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold fs-5 text-white" id="cardAvatar" style="width: 48px; height: 48px; background: #818cf8; border: 2px solid #fff;">
                                    ?
                                </div>
                                <div class="overflow-hidden">
                                    <div class="fw-bold text-white text-truncate" id="cardName">Student Name</div>
                                    <div class="small text-warning text-truncate" id="cardDept">Department</div>
                                    <div class="small font-monospace text-light opacity-75" id="cardId">STU-XXXX-XXX</div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-end pt-2 border-top border-white border-opacity-10 small">
                                <span class="text-light opacity-75" style="font-size: 0.7rem;">OFFICIAL CREDENTIAL</span>
                                <span class="badge bg-success" id="cardStatusBadge">ACTIVE</span>
                            </div>
                        </div>

                        <!-- Back Face -->
                        <div class="p-3 text-white d-flex flex-column justify-content-center align-items-center text-center" style="position: absolute; width: 100%; height: 100%; backface-visibility: hidden; transform: rotateY(180deg); border-radius: 16px; background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); border: 1px solid rgba(255,255,255,0.2);">
                            <div class="bg-white p-2 rounded-2 mb-2 shadow-sm">
                                <div id="cardBackQrContainer"></div>
                            </div>
                            <div class="small fw-bold font-monospace" id="cardBackId">STU-XXXX-XXX</div>
                            <small class="text-light opacity-50" style="font-size: 0.65rem;">Tap to flip back</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QRCode.js CDN Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const baseVerifyUrl = "<?= e($baseVerifyUrl) ?>";
    const modeStudentRadio = document.getElementById('modeStudent');
    const modeCustomRadio = document.getElementById('modeCustom');
    const studentSelectWrapper = document.getElementById('studentSelectWrapper');
    const customInputWrapper = document.getElementById('customInputWrapper');
    const studentSelect = document.getElementById('studentSelect');
    const customContentInput = document.getElementById('customContentInput');
    const targetLinkInput = document.getElementById('targetLinkInput');
    const copyLinkBtn = document.getElementById('copyLinkBtn');
    const copyLinkBtnText = document.getElementById('copyLinkBtnText');
    const qrFgColor = document.getElementById('qrFgColor');
    const qrBgColor = document.getElementById('qrBgColor');
    const qrSizeSlider = document.getElementById('qrSizeSlider');
    const sizeLabel = document.getElementById('sizeLabel');
    const liveQrContainer = document.getElementById('liveQrContainer');
    const cardBackQrContainer = document.getElementById('cardBackQrContainer');
    const downloadQrBtn = document.getElementById('downloadQrBtn');
    const openVerificationLink = document.getElementById('openVerificationLink');

    // 3D Card Elements
    const card3dWrapper = document.querySelector('.card-3d-preview-wrapper');
    const card3dInner = document.querySelector('.card-3d-inner');
    const cardName = document.getElementById('cardName');
    const cardDept = document.getElementById('cardDept');
    const cardId = document.getElementById('cardId');
    const cardBackId = document.getElementById('cardBackId');
    const cardAvatar = document.getElementById('cardAvatar');
    const cardStatusBadge = document.getElementById('cardStatusBadge');

    let qrCodeInstance = null;
    let cardQrInstance = null;

    // Flip 3D Card on click
    if (card3dWrapper && card3dInner) {
        card3dWrapper.addEventListener('click', function () {
            const isFlipped = card3dInner.style.transform === 'rotateY(180deg)';
            card3dInner.style.transform = isFlipped ? 'rotateY(0deg)' : 'rotateY(180deg)';
        });
    }

    // Toggle between Student Mode & Custom URL Mode
    function updateMode() {
        if (modeStudentRadio.checked) {
            studentSelectWrapper.classList.remove('d-none');
            customInputWrapper.classList.add('d-none');
        } else {
            studentSelectWrapper.classList.add('d-none');
            customInputWrapper.classList.remove('d-none');
        }
        generateLiveQr();
    }

    modeStudentRadio.addEventListener('change', updateMode);
    modeCustomRadio.addEventListener('change', updateMode);

    // Get active URL/text
    function getTargetContent() {
        if (modeStudentRadio.checked) {
            const val = studentSelect.value;
            if (val) {
                return baseVerifyUrl + encodeURIComponent(val);
            }
            return baseVerifyUrl;
        } else {
            return customContentInput.value.trim() || window.location.origin;
        }
    }

    // Re-render QR code
    function generateLiveQr() {
        const text = getTargetContent();
        const size = parseInt(qrSizeSlider.value, 10);
        const fg = qrFgColor.value;
        const bg = qrBgColor.value;

        targetLinkInput.value = text;
        openVerificationLink.href = text;

        // Clear containers
        liveQrContainer.innerHTML = '';
        cardBackQrContainer.innerHTML = '';

        if (!text) return;

        // Generate Main Preview QR
        qrCodeInstance = new QRCode(liveQrContainer, {
            text: text,
            width: size,
            height: size,
            colorDark: fg,
            colorLight: bg,
            correctLevel: QRCode.CorrectLevel.H
        });

        // Generate 3D Card Back QR
        cardQrInstance = new QRCode(cardBackQrContainer, {
            text: text,
            width: 100,
            height: 100,
            colorDark: "#1e1b4b",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.M
        });

        // Update Student details meta & 3D Card
        if (modeStudentRadio.checked && studentSelect.selectedIndex > 0) {
            const opt = studentSelect.options[studentSelect.selectedIndex];
            const name = opt.getAttribute('data-name');
            const dept = opt.getAttribute('data-dept');
            const code = opt.value;
            const status = opt.getAttribute('data-status');

            document.getElementById('previewStudentName').textContent = name;
            document.getElementById('previewStudentDept').textContent = dept;
            document.getElementById('previewStudentId').textContent = code;

            // 3D Card
            cardName.textContent = name;
            cardDept.textContent = dept;
            cardId.textContent = code;
            cardBackId.textContent = code;
            cardAvatar.textContent = name.charAt(0).toUpperCase();

            if (status === 'Active') {
                cardStatusBadge.className = 'badge bg-success';
                cardStatusBadge.textContent = 'ACTIVE';
            } else {
                cardStatusBadge.className = 'badge bg-danger';
                cardStatusBadge.textContent = 'INACTIVE';
            }
        } else if (modeCustomRadio.checked) {
            document.getElementById('previewStudentName').textContent = "Custom Target Link";
            document.getElementById('previewStudentDept').textContent = text;
            document.getElementById('previewStudentId').textContent = "CUSTOM QR";
        }
    }

    // Events
    studentSelect.addEventListener('change', generateLiveQr);
    customContentInput.addEventListener('input', generateLiveQr);
    qrFgColor.addEventListener('input', generateLiveQr);
    qrBgColor.addEventListener('input', generateLiveQr);
    qrSizeSlider.addEventListener('input', function () {
        sizeLabel.textContent = this.value + 'px';
        generateLiveQr();
    });

    // Copy link button
    copyLinkBtn.addEventListener('click', function () {
        navigator.clipboard.writeText(targetLinkInput.value).then(() => {
            copyLinkBtnText.textContent = 'Copied!';
            copyLinkBtn.classList.replace('btn-primary', 'btn-success');
            setTimeout(() => {
                copyLinkBtnText.textContent = 'Copy';
                copyLinkBtn.classList.replace('btn-success', 'btn-primary');
            }, 2500);
        });
    });

    // Download QR image
    downloadQrBtn.addEventListener('click', function () {
        const img = liveQrContainer.querySelector('img');
        if (img) {
            const a = document.createElement('a');
            const id = studentSelect.value || 'custom-qr';
            a.download = `QR-${id}.png`;
            a.href = img.src;
            a.click();
        }
    });

    // Initial Trigger (auto select first student if available)
    if (studentSelect.options.length > 1 && !studentSelect.value) {
        studentSelect.selectedIndex = 1;
    }
    generateLiveQr();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

