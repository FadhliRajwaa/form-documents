<?php 
require_once 'config.php';

// Check if token exists, redirect to OAuth if not
$tokenPath = BASE_PATH . '/token.json';
if (!file_exists($tokenPath)) {
    header('Location: oauth.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <div class="form-header">
                <i class="fas fa-file-upload"></i>
                <h1><?= APP_NAME ?></h1>
                <p>Upload dokumen dan data Anda akan tersimpan di Google Drive & Sheets</p>
            </div>

            <!-- Alert Messages -->
            <div id="alertContainer"></div>

            <!-- Form -->
            <form id="documentForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nama">
                        <i class="fas fa-user"></i> Nama Lengkap <span class="required">*</span>
                    </label>
                    <input type="text" id="nama" name="nama" required placeholder="Masukkan nama lengkap Anda">
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email <span class="required">*</span>
                    </label>
                    <input type="email" id="email" name="email" required placeholder="contoh@email.com">
                </div>

                <div class="form-group">
                    <label for="telepon">
                        <i class="fas fa-phone"></i> Nomor Telepon <span class="required">*</span>
                    </label>
                    <input type="tel" id="telepon" name="telepon" required placeholder="08xxxxxxxxxx">
                </div>

                <div class="form-group">
                    <label for="keterangan">
                        <i class="fas fa-comment-dots"></i> Keterangan
                    </label>
                    <textarea id="keterangan" name="keterangan" rows="4" placeholder="Tambahkan keterangan atau catatan tambahan..."></textarea>
                </div>

                <div class="form-group">
                    <label for="dokumen">
                        <i class="fas fa-file-alt"></i> Upload Dokumen <span class="required">*</span>
                    </label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="dokumen" name="dokumen[]" multiple required accept="<?= implode(',', array_map(function($ext) { return '.'.$ext; }, ALLOWED_EXTENSIONS)) ?>">
                        <label for="dokumen" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Klik untuk pilih file atau drag & drop</span>
                            <small>Format: <?= implode(', ', ALLOWED_EXTENSIONS) ?> (Max <?= UPLOAD_MAX_SIZE / 1048576 ?>MB per file)</small>
                        </label>
                    </div>
                    <div id="fileList" class="file-list"></div>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-paper-plane"></i> Submit
                    </button>
                </div>
            </form>

            <!-- Loading Overlay -->
            <div id="loadingOverlay" class="loading-overlay" style="display: none;">
                <div class="spinner"></div>
                <p>Sedang mengupload dokumen...</p>
                <small>Mohon tunggu, jangan tutup halaman ini</small>
            </div>
        </div>

        <!-- Success Modal -->
        <div id="successModal" class="modal" style="display: none;">
            <div class="modal-content">
                <div class="modal-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Berhasil!</h2>
                <p>Dokumen dan data Anda telah tersimpan</p>
                <div class="modal-links">
                    <a id="driveLink" href="#" target="_blank" class="modal-link">
                        <i class="fab fa-google-drive"></i> Lihat di Google Drive
                    </a>
                    <a id="sheetsLink" href="#" target="_blank" class="modal-link">
                        <i class="fas fa-table"></i> Lihat di Google Sheets
                    </a>
                </div>
                <button onclick="closeModal()" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Submit Dokumen Baru
                </button>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
