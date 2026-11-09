<?php
/**
 * Footer Component
 * Student Management System
 */
?>
        </main>

        <!-- Application Footer -->
        <footer class="app-footer">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                <div>
                    <strong>Student Management System</strong> &copy; <?= date('Y') ?> &bull; Built with PHP 8+, MySQL, PDO & Bootstrap 5
                </div>
                <div class="d-flex gap-3">
                    <span class="badge bg-light text-secondary border">PHP <?= phpversion() ?></span>
                    <span class="badge bg-light text-secondary border">MySQL</span>
                </div>
            </div>
        </footer>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger d-flex align-items-center" id="deleteConfirmModalLabel">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i> Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="text-secondary mb-2">Are you sure you want to permanently delete this student record?</p>
                    <div class="alert alert-danger mb-0 py-2">
                        <strong id="deleteStudentName" class="text-dark">Student Name</strong>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle me-1"></i> This action cannot be undone and will remove all student data from MySQL.
                    </small>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <form id="deleteStudentForm" action="<?= BASE_URL ?>/students/delete.php" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" id="deleteStudentId" value="">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash-fill me-1"></i> Yes, Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Quick View Modal -->
    <div class="modal fade" id="viewStudentModal" tabindex="-1" aria-labelledby="viewStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <div>
                        <span class="badge bg-primary mb-1" id="viewModalCode">STU-0000-000</span>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="viewModalName">Student Name</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small fw-semibold">EMAIL ADDRESS</label>
                            <div><a href="#" id="viewModalEmail" class="text-decoration-none fw-medium">-</a></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-semibold">PHONE NUMBER</label>
                            <div><a href="#" id="viewModalPhone" class="text-decoration-none fw-medium">-</a></div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-semibold">DEPARTMENT</label>
                            <div class="fw-semibold text-dark" id="viewModalDept">-</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small fw-semibold">STATUS</label>
                            <div><span id="viewModalStatus" class="badge badge-active">Active</span></div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small fw-semibold">GENDER</label>
                            <div class="text-dark" id="viewModalGender">-</div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small fw-semibold">DATE OF BIRTH</label>
                            <div class="text-dark" id="viewModalDob">-</div>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small fw-semibold">ENROLLMENT DATE</label>
                            <div class="text-dark" id="viewModalEnrollment">-</div>
                        </div>
                        <div class="col-12">
                            <label class="text-muted small fw-semibold">RESIDENTIAL ADDRESS</label>
                            <div class="p-3 bg-light rounded text-secondary" id="viewModalAddress">-</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="viewModalEditBtn" data-base-url="<?= BASE_URL ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-pencil-square me-1"></i> Edit Student
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Student QR & Barcode Digital Badge Modal -->
    <div class="modal fade" id="qrStudentModal" tabindex="-1" aria-labelledby="qrStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 pb-0">
                    <div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle mb-1" id="qrModalStudentId">STU-0000-000</span>
                        <h5 class="modal-title fw-bold text-dark" id="qrModalStudentName">Student Name</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-3">
                    
                    <!-- Format Switch Tabs (QR vs Barcode) -->
                    <ul class="nav nav-pills nav-fill mb-3 bg-light p-1 rounded-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-1 small fw-semibold" id="tab-modal-qr" data-bs-toggle="pill" data-bs-target="#modal-pane-qr" type="button" role="tab">
                                <i class="bi bi-qr-code me-1"></i> QR Code
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 small fw-semibold" id="tab-modal-barcode" data-bs-toggle="pill" data-bs-target="#modal-pane-barcode" type="button" role="tab">
                                <i class="bi bi-upc-scan me-1"></i> Barcode
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mb-3">
                        <!-- QR Code Pane -->
                        <div class="tab-pane fade show active" id="modal-pane-qr" role="tabpanel">
                            <div class="p-3 bg-light rounded-3 d-inline-block border shadow-sm">
                                <div id="modalQrContainer" class="d-flex justify-content-center align-items-center" style="min-height: 160px; min-width: 160px;"></div>
                            </div>
                            <small class="d-block text-muted mt-2">Scan to open official student verification card.</small>
                        </div>

                        <!-- Barcode Pane -->
                        <div class="tab-pane fade" id="modal-pane-barcode" role="tabpanel">
                            <div class="p-3 bg-white rounded-3 d-inline-block border shadow-sm w-100">
                                <svg id="modalBarcodeSvg" style="max-width: 100%;"></svg>
                                <div class="font-monospace fw-bold fs-6 text-dark mt-1" id="modalBarcodeNumber">000000</div>
                            </div>
                            <small class="d-block text-muted mt-2">Standard Code-128 institutional barcode.</small>
                        </div>
                    </div>
                    
                    <!-- Shareable Link -->
                    <div class="input-group input-group-sm mb-3">
                        <span class="input-group-text bg-light text-muted"><i class="bi bi-link-45deg"></i></span>
                        <input type="text" id="modalQrLinkInput" class="form-control font-monospace bg-light small" readonly>
                        <button class="btn btn-primary" type="button" id="modalCopyLinkBtn">
                            <i class="bi bi-clipboard me-1"></i> <span id="modalCopyBtnText">Copy</span>
                        </button>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" id="modalDownloadQrBtn" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-download me-1"></i> Download
                        </button>
                        <a href="#" id="modalVerifyPageBtn" target="_blank" class="btn btn-sm btn-success">
                            <i class="bi bi-person-badge-fill me-1"></i> Open SmartPass ID Card
                        </a>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0 py-2">
                    <a href="<?= BASE_URL ?>/students/qr-generator.php" id="modalFullGeneratorBtn" class="small text-decoration-none text-primary">
                        <i class="bi bi-sliders me-1"></i> Open in Full QR & Barcode Generator &raquo;
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 Bundle JS (with Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- QRCode JS Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <!-- JsBarcode Library for Barcode generation -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

    <!-- Custom Application JS -->
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>

