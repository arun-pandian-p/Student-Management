/**
 * Student Management System - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    // -------------------------------------------------------------
    // 1. Initialize Bootstrap Tooltips
    // -------------------------------------------------------------
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // -------------------------------------------------------------
    // 2. Responsive Mobile Sidebar Toggle
    // -------------------------------------------------------------
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggler = document.querySelector('.sidebar-toggler');
    const closeSidebarBtn = document.getElementById('closeSidebarBtn');
    let backdrop = document.querySelector('.sidebar-backdrop');

    if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.className = 'sidebar-backdrop';
        document.body.appendChild(backdrop);
    }

    function openSidebar() {
        if (sidebar && backdrop) {
            sidebar.classList.add('show');
            backdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeSidebar() {
        if (sidebar && backdrop) {
            sidebar.classList.remove('show');
            backdrop.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    if (sidebarToggler) {
        sidebarToggler.addEventListener('click', function (e) {
            e.stopPropagation();
            if (sidebar.classList.contains('show')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }

    if (closeSidebarBtn) {
        closeSidebarBtn.addEventListener('click', closeSidebar);
    }

    if (backdrop) {
        backdrop.addEventListener('click', closeSidebar);
    }

    // Auto-close on resize if window expands past mobile breakpoint
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992) {
            closeSidebar();
        }
    });

    // -------------------------------------------------------------
    // 3. Auto-Dismiss Flash Alerts after 5 seconds
    // -------------------------------------------------------------
    const flashAlerts = document.querySelectorAll('.alert.alert-dismissible');
    flashAlerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    });

    // -------------------------------------------------------------
    // 4. Delete Confirmation Modal Setup
    // -------------------------------------------------------------
    const deleteModalEl = document.getElementById('deleteConfirmModal');
    if (deleteModalEl) {
        const deleteModal = new bootstrap.Modal(deleteModalEl);
        const deleteStudentIdInput = document.getElementById('deleteStudentId');
        const deleteStudentNameSpan = document.getElementById('deleteStudentName');

        document.querySelectorAll('.btn-delete-student').forEach(function (button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const code = this.getAttribute('data-code');

                if (deleteStudentIdInput) {
                    deleteStudentIdInput.value = id;
                }
                if (deleteStudentNameSpan) {
                    deleteStudentNameSpan.textContent = name + ' (' + code + ')';
                }

                deleteModal.show();
            });
        });
    }

    // -------------------------------------------------------------
    // 5. View Student Quick-Details Modal Setup
    // -------------------------------------------------------------
    const viewModalEl = document.getElementById('viewStudentModal');
    if (viewModalEl) {
        const viewModal = new bootstrap.Modal(viewModalEl);

        document.querySelectorAll('.btn-view-student').forEach(function (button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const student = JSON.parse(this.getAttribute('data-student') || '{}');

                document.getElementById('viewModalCode').textContent = student.student_id || '-';
                document.getElementById('viewModalName').textContent = student.full_name || '-';
                document.getElementById('viewModalEmail').textContent = student.email || '-';
                document.getElementById('viewModalEmail').href = 'mailto:' + (student.email || '');
                document.getElementById('viewModalPhone').textContent = student.phone || '-';
                document.getElementById('viewModalPhone').href = 'tel:' + (student.phone || '');
                document.getElementById('viewModalDept').textContent = student.department || '-';
                document.getElementById('viewModalGender').textContent = student.gender || '-';
                document.getElementById('viewModalDob').textContent = student.date_of_birth || '-';
                document.getElementById('viewModalEnrollment').textContent = student.enrollment_date || '-';
                document.getElementById('viewModalAddress').textContent = student.address || 'No address provided.';

                const statusBadge = document.getElementById('viewModalStatus');
                if (statusBadge) {
                    if (student.status === 'Active') {
                        statusBadge.className = 'badge badge-active';
                        statusBadge.textContent = 'Active';
                    } else {
                        statusBadge.className = 'badge badge-inactive';
                        statusBadge.textContent = 'Inactive';
                    }
                }

                const editBtn = document.getElementById('viewModalEditBtn');
                if (editBtn && student.id) {
                    const baseUrl = editBtn.getAttribute('data-base-url') || '';
                    editBtn.href = baseUrl + '/students/edit.php?id=' + student.id;
                }

                viewModal.show();
            });
        });
    }

    // -------------------------------------------------------------
    // 6. Student QR & AR Modal Setup
    // -------------------------------------------------------------
    const qrModalEl = document.getElementById('qrStudentModal');
    if (qrModalEl) {
        const qrModal = new bootstrap.Modal(qrModalEl);
        const modalQrContainer = document.getElementById('modalQrContainer');
        const qrModalStudentId = document.getElementById('qrModalStudentId');
        const qrModalStudentName = document.getElementById('qrModalStudentName');
        const modalQrLinkInput = document.getElementById('modalQrLinkInput');
        const modalCopyLinkBtn = document.getElementById('modalCopyLinkBtn');
        const modalCopyBtnText = document.getElementById('modalCopyBtnText');
        const modalDownloadQrBtn = document.getElementById('modalDownloadQrBtn');
        const modalVerifyPageBtn = document.getElementById('modalVerifyPageBtn');
        const modalFullGeneratorBtn = document.getElementById('modalFullGeneratorBtn');
        let modalQrInstance = null;

        document.querySelectorAll('.btn-qr-student').forEach(function (button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                const code = this.getAttribute('data-code') || '';
                const name = this.getAttribute('data-name') || '';
                const url = this.getAttribute('data-url') || '';

                if (qrModalStudentId) qrModalStudentId.textContent = code;
                if (qrModalStudentName) qrModalStudentName.textContent = name;
                if (modalQrLinkInput) modalQrLinkInput.value = url;
                if (modalVerifyPageBtn) modalVerifyPageBtn.href = url;
                if (modalFullGeneratorBtn) {
                    const baseHref = modalFullGeneratorBtn.getAttribute('href').split('?')[0];
                    modalFullGeneratorBtn.href = baseHref + '?student_id=' + encodeURIComponent(code);
                }

                // Render QR code in container
                if (modalQrContainer && typeof QRCode !== 'undefined') {
                    modalQrContainer.innerHTML = '';
                    modalQrInstance = new QRCode(modalQrContainer, {
                        text: url,
                        width: 170,
                        height: 170,
                        colorDark: "#1e1b4b",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                }

                // Render Barcode in container
                const modalBarcodeSvg = document.getElementById('modalBarcodeSvg');
                const modalBarcodeNumber = document.getElementById('modalBarcodeNumber');
                if (modalBarcodeSvg && typeof JsBarcode !== 'undefined') {
                    const numCode = code.replace(/[^0-9]/g, '') || '712946';
                    if (modalBarcodeNumber) modalBarcodeNumber.textContent = numCode;
                    try {
                        JsBarcode("#modalBarcodeSvg", numCode, {
                            format: "CODE128",
                            lineColor: "#0f172a",
                            width: 2,
                            height: 55,
                            displayValue: false,
                            margin: 0
                        });
                    } catch (err) {
                        console.error("Barcode render error:", err);
                    }
                }

                qrModal.show();
            });
        });

        // Copy button in QR modal
        if (modalCopyLinkBtn && modalQrLinkInput) {
            modalCopyLinkBtn.addEventListener('click', function () {
                navigator.clipboard.writeText(modalQrLinkInput.value).then(function () {
                    if (modalCopyBtnText) modalCopyBtnText.textContent = 'Copied!';
                    modalCopyLinkBtn.classList.replace('btn-primary', 'btn-success');
                    setTimeout(function () {
                        if (modalCopyBtnText) modalCopyBtnText.textContent = 'Copy';
                        modalCopyLinkBtn.classList.replace('btn-success', 'btn-primary');
                    }, 2000);
                });
            });
        }

        // Download button in QR modal
        if (modalDownloadQrBtn && modalQrContainer) {
            modalDownloadQrBtn.addEventListener('click', function () {
                const img = modalQrContainer.querySelector('img');
                if (img) {
                    const a = document.createElement('a');
                    const code = qrModalStudentId ? qrModalStudentId.textContent : 'student';
                    a.download = 'QR-' + code + '.png';
                    a.href = img.src;
                    a.click();
                }
            });
        }
    }

    // -------------------------------------------------------------
    // 7. Client-Side Form Validation (Bootstrap standard)
    // -------------------------------------------------------------
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});

