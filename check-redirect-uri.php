<?php
/**
 * Check Redirect URI Detection
 * Temporary debug file - HAPUS setelah selesai
 */

header('Content-Type: text/plain');

echo "=== REDIRECT URI DETECTION ===\n\n";

// Detect current URL (same logic as oauth.php)
$protocol = 'http';
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $protocol = 'https';
} elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $protocol = 'https';
} elseif (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] === 'https') {
    $protocol = 'https';
}

$host = $_SERVER['HTTP_HOST'];

// Get dynamic path (remove current script and add oauth.php)
$scriptDir = dirname($_SERVER['SCRIPT_NAME']); // e.g., /form-document or /

// Handle root directory case (avoid double slash)
if ($scriptDir === '/' || $scriptDir === '\\') {
    $oauthPath = '/oauth.php';
} else {
    $oauthPath = $scriptDir . '/oauth.php';
}

$currentUrl = $protocol . '://' . $host . $oauthPath;

echo "Detected Redirect URI:\n";
echo "  " . $currentUrl . "\n\n";

echo "Server Variables:\n";
echo "  HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "\n";
echo "  SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'NOT SET') . "\n";
echo "  SERVER_PORT: " . ($_SERVER['SERVER_PORT'] ?? 'NOT SET') . "\n";
echo "  REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "\n";
echo "  REQUEST_SCHEME: " . ($_SERVER['REQUEST_SCHEME'] ?? 'NOT SET') . "\n";
echo "  HTTPS: " . ($_SERVER['HTTPS'] ?? 'NOT SET') . "\n\n";

// Check credentials.json
require_once __DIR__ . '/config.php';

if (file_exists(GOOGLE_CREDENTIALS_PATH)) {
    $creds = json_decode(file_get_contents(GOOGLE_CREDENTIALS_PATH), true);
    
    if (isset($creds['web']['redirect_uris'])) {
        echo "Registered Redirect URIs in credentials.json:\n";
        foreach ($creds['web']['redirect_uris'] as $uri) {
            echo "  - " . $uri . "\n";
        }
        echo "\n";
        
        // Check if current URL is registered
        if (in_array($currentUrl, $creds['web']['redirect_uris'])) {
            echo "✅ Current URL IS registered!\n";
        } else {
            echo "❌ Current URL NOT registered!\n\n";
            echo "SOLUTION:\n";
            echo "Add this to Google Cloud Console → Credentials → Authorized redirect URIs:\n";
            echo "  " . $currentUrl . "\n";
        }
    }
}
