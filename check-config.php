<?php
require_once 'config.php';

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Drive;

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Configuration</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .check-item { padding: 15px; margin: 10px 0; border-radius: 8px; }
        .check-ok { background: #d4edda; border-left: 4px solid #28a745; }
        .check-error { background: #f8d7da; border-left: 4px solid #dc3545; }
        .check-warning { background: #fff3cd; border-left: 4px solid #ffc107; }
        .code { background: #f5f5f5; padding: 10px; border-radius: 4px; font-family: monospace; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <h1>🔍 Configuration Checker</h1>
            <p style="color: var(--text-light); margin-bottom: 30px;">
                Tool ini akan mengecek konfigurasi sistem Anda
            </p>

            <?php
            $checks = [];

            // Check 1: .env file
            if (file_exists(BASE_PATH . '/.env')) {
                $checks[] = ['status' => 'ok', 'message' => '✅ File .env ditemukan'];
            } else {
                $checks[] = ['status' => 'error', 'message' => '❌ File .env tidak ditemukan'];
            }

            // Check 2: credentials.json
            if (file_exists(GOOGLE_CREDENTIALS_PATH)) {
                $checks[] = ['status' => 'ok', 'message' => '✅ File credentials.json ditemukan'];
                
                // Check if it's OAuth or Service Account
                $creds = json_decode(file_get_contents(GOOGLE_CREDENTIALS_PATH), true);
                if (isset($creds['web'])) {
                    $checks[] = ['status' => 'ok', 'message' => '✅ Credentials type: OAuth 2.0 (Correct!)'];
                } elseif (isset($creds['type']) && $creds['type'] === 'service_account') {
                    $checks[] = ['status' => 'error', 'message' => '❌ Credentials type: Service Account (Wrong! Harus OAuth 2.0)'];
                }
            } else {
                $checks[] = ['status' => 'error', 'message' => '❌ File credentials.json tidak ditemukan'];
            }

            // Check 3: token.json
            $tokenPath = BASE_PATH . '/token.json';
            if (file_exists($tokenPath)) {
                $checks[] = ['status' => 'ok', 'message' => '✅ File token.json ditemukan (sudah login)'];
                
                $token = json_decode(file_get_contents($tokenPath), true);
                if (isset($token['access_token'])) {
                    $checks[] = ['status' => 'ok', 'message' => '✅ Access token tersedia'];
                }
                if (isset($token['refresh_token'])) {
                    $checks[] = ['status' => 'ok', 'message' => '✅ Refresh token tersedia'];
                }
            } else {
                $checks[] = ['status' => 'warning', 'message' => '⚠️ File token.json tidak ditemukan (belum login). <a href="oauth.php">Login sekarang</a>'];
            }

            // Check 4: Google Drive Folder ID
            if (defined('GOOGLE_DRIVE_FOLDER_ID') && !empty(GOOGLE_DRIVE_FOLDER_ID)) {
                $checks[] = ['status' => 'ok', 'message' => '✅ Google Drive Folder ID: ' . GOOGLE_DRIVE_FOLDER_ID];
            } else {
                $checks[] = ['status' => 'error', 'message' => '❌ Google Drive Folder ID tidak diset di .env'];
            }

            // Check 5: Google Spreadsheet ID
            if (defined('GOOGLE_SPREADSHEET_ID') && !empty(GOOGLE_SPREADSHEET_ID)) {
                $checks[] = ['status' => 'ok', 'message' => '✅ Google Spreadsheet ID: ' . GOOGLE_SPREADSHEET_ID];
            } else {
                $checks[] = ['status' => 'error', 'message' => '❌ Google Spreadsheet ID tidak diset di .env'];
            }

            // Check 6: Test Google Sheets connection
            if (file_exists($tokenPath) && defined('GOOGLE_SPREADSHEET_ID')) {
                try {
                    $client = new Client();
                    $client->setAuthConfig(GOOGLE_CREDENTIALS_PATH);
                    $client->setAccessToken(json_decode(file_get_contents($tokenPath), true));
                    
                    $service = new Sheets($client);
                    $spreadsheet = $service->spreadsheets->get(GOOGLE_SPREADSHEET_ID);
                    
                    $checks[] = ['status' => 'ok', 'message' => '✅ Koneksi ke Google Sheets berhasil'];
                    $checks[] = ['status' => 'ok', 'message' => '✅ Spreadsheet title: ' . $spreadsheet->getProperties()->getTitle()];
                    
                    // Check sheet names
                    $sheets = $spreadsheet->getSheets();
                    $sheetNames = [];
                    foreach ($sheets as $sheet) {
                        $sheetNames[] = $sheet->getProperties()->getTitle();
                    }
                    
                    if (in_array('Data Dokumen', $sheetNames)) {
                        $checks[] = ['status' => 'ok', 'message' => '✅ Sheet "Data Dokumen" ditemukan'];
                    } else {
                        $checks[] = ['status' => 'error', 'message' => '❌ Sheet "Data Dokumen" tidak ditemukan. Sheet yang ada: ' . implode(', ', $sheetNames)];
                        $checks[] = ['status' => 'warning', 'message' => '⚠️ Silakan rename salah satu sheet menjadi "Data Dokumen" (exact match, case sensitive)'];
                    }
                    
                } catch (Exception $e) {
                    $checks[] = ['status' => 'error', 'message' => '❌ Error koneksi Google Sheets: ' . $e->getMessage()];
                }
            }

            // Check 7: Test Google Drive connection
            if (file_exists($tokenPath) && defined('GOOGLE_DRIVE_FOLDER_ID')) {
                try {
                    $client = new Client();
                    $client->setAuthConfig(GOOGLE_CREDENTIALS_PATH);
                    $client->setAccessToken(json_decode(file_get_contents($tokenPath), true));
                    
                    $service = new Drive($client);
                    $folder = $service->files->get(GOOGLE_DRIVE_FOLDER_ID, ['fields' => 'id,name']);
                    
                    $checks[] = ['status' => 'ok', 'message' => '✅ Koneksi ke Google Drive berhasil'];
                    $checks[] = ['status' => 'ok', 'message' => '✅ Folder name: ' . $folder->getName()];
                    
                } catch (Exception $e) {
                    $checks[] = ['status' => 'error', 'message' => '❌ Error koneksi Google Drive: ' . $e->getMessage()];
                }
            }

            // Display all checks
            foreach ($checks as $check) {
                $class = 'check-' . $check['status'];
                echo "<div class='check-item {$class}'>{$check['message']}</div>";
            }
            ?>

            <div style="margin-top: 30px; padding: 20px; background: var(--bg-light); border-radius: 8px;">
                <h3>📋 Quick Actions</h3>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 15px;">
                    <a href="oauth.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login OAuth
                    </a>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-home"></i> Ke Form
                    </a>
                    <button onclick="location.reload()" class="btn btn-secondary">
                        <i class="fas fa-sync"></i> Refresh Check
                    </button>
                </div>
            </div>

            <div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border-radius: 8px; border-left: 4px solid #2196F3;">
                <strong><i class="fas fa-info-circle"></i> Tips Troubleshooting:</strong>
                <ul style="margin: 10px 0 0 20px; line-height: 1.8;">
                    <li>Jika ada error "Sheet not found", pastikan nama sheet <strong>persis</strong> "Data Dokumen"</li>
                    <li>Jika token expired, delete file <code>token.json</code> dan login ulang</li>
                    <li>Jika folder/spreadsheet tidak accessible, pastikan sudah login dengan akun yang benar</li>
                    <li>Jika credentials type salah, download ulang OAuth 2.0 credentials (bukan Service Account)</li>
                </ul>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
