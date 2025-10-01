<?php
/**
 * Debug OAuth URL Detection
 * HAPUS SETELAH SELESAI DEBUG!
 */

header('Content-Type: text/plain');

echo "=== DEBUG OAUTH URL DETECTION ===\n\n";

// Detect current URL (same logic as oauth.php)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$currentUrl = $protocol . '://' . $host . '/oauth.php';

echo "1. Protocol: " . $protocol . "\n";
echo "2. Host: " . $host . "\n";
echo "3. Detected redirect URI: " . $currentUrl . "\n\n";

echo "4. Expected redirect URI: https://form-documents.onrender.com/oauth.php\n";
echo "5. Match: " . ($currentUrl === 'https://form-documents.onrender.com/oauth.php' ? "YES ✅" : "NO ❌") . "\n\n";

// Server variables
echo "=== SERVER VARIABLES ===\n\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'NOT SET') . "\n";
echo "SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'NOT SET') . "\n";
echo "SERVER_PORT: " . ($_SERVER['SERVER_PORT'] ?? 'NOT SET') . "\n";
echo "HTTPS: " . ($_SERVER['HTTPS'] ?? 'NOT SET') . "\n";
echo "REQUEST_SCHEME: " . ($_SERVER['REQUEST_SCHEME'] ?? 'NOT SET') . "\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NOT SET') . "\n";
echo "HTTP_X_FORWARDED_PROTO: " . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'NOT SET') . "\n";
echo "HTTP_X_FORWARDED_HOST: " . ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? 'NOT SET') . "\n\n";

// Load credentials and check
require_once 'config.php';

use Google\Client;

echo "=== GOOGLE CLIENT CONFIG ===\n\n";

try {
    
    $client = new Client();
    $client->setAuthConfig(__DIR__ . '/credentials.json');
    
    $authConfig = $client->getAuthConfig();
    
    if (isset($authConfig['redirect_uris'])) {
        echo "Redirect URIs in credentials.json:\n";
        foreach ($authConfig['redirect_uris'] as $uri) {
            echo "  - " . $uri . "\n";
        }
        echo "\n";
    }
    
    // What redirect URI will be used?
    echo "Redirect URI that will be sent to Google:\n";
    echo "  " . $currentUrl . "\n\n";
    
    // Check if it's in allowed list
    $isAllowed = isset($authConfig['redirect_uris']) && in_array($currentUrl, $authConfig['redirect_uris']);
    echo "Is this URI in allowed list: " . ($isAllowed ? "YES ✅" : "NO ❌") . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== END DEBUG ===";
