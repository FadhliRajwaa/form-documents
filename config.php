<?php
/**
 * Configuration File
 * Load environment variables dan setup konstanta
 */

// Load .env file (for local development)
// In production (Render, Railway, etc), environment variables are set via platform
function loadEnv($path) {
    // Check if .env file exists (local development)
    if (file_exists($path)) {
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            if (!array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
    // If .env doesn't exist, assume environment variables are set by platform (production)
    // No error thrown - this is normal for production deployments
}

loadEnv(__DIR__ . '/.env');

// Application Constants
define('APP_NAME', getenv('APP_NAME') ?: 'Form Document System');
define('BASE_PATH', __DIR__);
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('UPLOAD_MAX_SIZE', getenv('UPLOAD_MAX_SIZE') ?: 10485760); // 10MB default
define('ALLOWED_EXTENSIONS', explode(',', getenv('ALLOWED_EXTENSIONS') ?: 'pdf,doc,docx,jpg,jpeg,png'));

// Google API Constants
define('GOOGLE_CREDENTIALS_PATH', BASE_PATH . '/' . getenv('GOOGLE_CREDENTIALS_PATH'));
define('GOOGLE_DRIVE_FOLDER_ID', getenv('GOOGLE_DRIVE_FOLDER_ID'));
define('GOOGLE_SPREADSHEET_ID', getenv('GOOGLE_SPREADSHEET_ID'));

// Autoload Composer
require_once BASE_PATH . '/vendor/autoload.php';

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Helper function untuk response JSON
 */
function jsonResponse($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Helper function untuk validasi file upload
 */
function validateFile($file) {
    $errors = [];
    
    // Check if file was uploaded
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Error uploading file: ' . $file['name'];
        return $errors;
    }
    
    // Check file size
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        $errors[] = 'File terlalu besar: ' . $file['name'] . ' (Max: ' . (UPLOAD_MAX_SIZE / 1048576) . 'MB)';
    }
    
    // Check file extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        $errors[] = 'Ekstensi file tidak diizinkan: ' . $file['name'] . ' (Allowed: ' . implode(', ', ALLOWED_EXTENSIONS) . ')';
    }
    
    return $errors;
}

/**
 * Helper function untuk sanitize filename
 */
function sanitizeFilename($filename) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    $name = pathinfo($filename, PATHINFO_FILENAME);
    
    // Remove special characters
    $name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);
    
    return $name . '.' . $ext;
}
