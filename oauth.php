<?php
require_once 'config.php';
require_once 'token-storage.php';

use Google\Client;
use Google\Service\Drive;
use Google\Service\Sheets;

// Detect current URL for redirect URI
// Check X-Forwarded-Proto for reverse proxy (Render, Heroku, etc)
$protocol = 'http';
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $protocol = 'https';
} elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $protocol = 'https';
} elseif (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https') {
    $protocol = 'https';
}

$host = $_SERVER['HTTP_HOST'];
$currentUrl = $protocol . '://' . $host . '/oauth.php';

$client = new Client();
$client->setApplicationName(APP_NAME);
$client->setScopes([
    Drive::DRIVE_FILE,
    Sheets::SPREADSHEETS
]);
$client->setAuthConfig(GOOGLE_CREDENTIALS_PATH);
$client->setAccessType('offline');
$client->setPrompt('consent');
$client->setRedirectUri($currentUrl);

// Initialize token storage
$tokenStorage = new TokenStorage();
$tokenKey = 'google_oauth_token'; // Fixed key untuk aplikasi ini

// Handle OAuth callback
if (isset($_GET['code'])) {
    try {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        if (array_key_exists('error', $token)) {
            throw new Exception('Error fetching token: ' . $token['error']);
        }
        
        // Save token ke database
        $tokenStorage->saveToken($tokenKey, json_encode($token));
        
        echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAuth Berhasil</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper" style="text-align: center;">
            <div class="modal-icon success">
                <i class="fas fa-check-circle" style="font-size: 64px; color: var(--success-color);"></i>
            </div>
            <h2>Autentikasi Berhasil!</h2>
            <p>Aplikasi sudah terhubung dengan Google Account Anda.</p>
            <p>Token telah disimpan dan akan digunakan untuk upload dokumen.</p>
            <a href="index.php" class="btn btn-primary" style="margin-top: 20px;">
                <i class="fas fa-home"></i> Kembali ke Form
            </a>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>';
        
    } catch (Exception $e) {
        echo '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAuth Error</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper" style="text-align: center;">
            <div class="modal-icon">
                <i class="fas fa-exclamation-circle" style="font-size: 64px; color: var(--danger-color);"></i>
            </div>
            <h2>Autentikasi Gagal</h2>
            <p>' . htmlspecialchars($e->getMessage()) . '</p>
            <a href="oauth.php" class="btn btn-primary" style="margin-top: 20px;">
                <i class="fas fa-redo"></i> Coba Lagi
            </a>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>';
    }
    exit;
}

// Check if token exists and valid
if ($tokenStorage->hasToken($tokenKey)) {
    $tokenData = $tokenStorage->loadToken($tokenKey);
    $accessToken = json_decode($tokenData, true);
    $client->setAccessToken($accessToken);
    
    // Jika token expired, refresh token
    if ($client->isAccessTokenExpired()) {
        if ($client->getRefreshToken()) {
            $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            $tokenStorage->saveToken($tokenKey, json_encode($newToken));
        }
    }
    
    // Jika masih valid atau sudah di-refresh, redirect ke index
    if (!$client->isAccessTokenExpired()) {
        header('Location: index.php');
        exit;
    }
}

// Show authorization page
$authUrl = $client->createAuthUrl();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google Authorization - <?= APP_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="form-wrapper" style="text-align: center;">
            <div style="font-size: 64px; color: var(--primary-color); margin-bottom: 20px;">
                <i class="fab fa-google"></i>
            </div>
            <h1>Autentikasi Google Diperlukan</h1>
            <p style="margin: 20px 0; color: var(--text-light);">
                Aplikasi memerlukan akses ke Google Drive dan Google Sheets Anda<br>
                untuk menyimpan dokumen dan data.
            </p>
            
            <div style="background: var(--bg-light); padding: 20px; border-radius: 8px; margin: 20px 0; text-align: left;">
                <h3 style="margin-top: 0; color: var(--text-dark);">
                    <i class="fas fa-shield-alt"></i> Izin yang Diperlukan:
                </h3>
                <ul style="list-style: none; padding: 0;">
                    <li style="padding: 8px 0;">
                        <i class="fab fa-google-drive" style="color: var(--primary-color);"></i>
                        <strong>Google Drive:</strong> Upload dan kelola dokumen
                    </li>
                    <li style="padding: 8px 0;">
                        <i class="fas fa-table" style="color: var(--success-color);"></i>
                        <strong>Google Sheets:</strong> Simpan data formulir
                    </li>
                </ul>
            </div>
            
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 4px; margin: 20px 0; text-align: left;">
                <strong><i class="fas fa-info-circle"></i> Catatan:</strong><br>
                • Dokumen akan disimpan di Google Drive Anda sendiri<br>
                • Token akan disimpan secara lokal untuk penggunaan selanjutnya<br>
                • Anda bisa mencabut akses kapan saja dari Google Account Settings
            </div>
            
            <a href="<?= htmlspecialchars($authUrl) ?>" class="btn btn-primary" style="font-size: 16px; padding: 15px 30px; margin-top: 20px;">
                <i class="fab fa-google"></i> Login dengan Google
            </a>
            
            <p style="margin-top: 30px; font-size: 12px; color: var(--text-light);">
                Dengan melanjutkan, Anda menyetujui aplikasi untuk mengakses Google Drive dan Sheets Anda
            </p>
        </div>
    </div>
</body>
</html>
