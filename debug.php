<?php
/**
 * Debug Endpoint - Check credentials.json
 * HAPUS FILE INI SETELAH SELESAI DEBUG!
 */

header('Content-Type: text/plain');

echo "=== DEBUG CREDENTIALS ===\n\n";

// Check if credentials.json exists
$credPath = __DIR__ . '/credentials.json';
echo "1. File exists: " . (file_exists($credPath) ? "YES ✅" : "NO ❌") . "\n\n";

if (file_exists($credPath)) {
    echo "2. File size: " . filesize($credPath) . " bytes\n\n";
    
    $content = file_get_contents($credPath);
    echo "3. File content:\n";
    echo $content . "\n\n";
    
    $json = json_decode($content, true);
    if ($json) {
        echo "4. JSON valid: YES ✅\n\n";
        
        if (isset($json['web']['redirect_uris'])) {
            echo "5. Redirect URIs:\n";
            foreach ($json['web']['redirect_uris'] as $uri) {
                echo "   - " . $uri . "\n";
            }
            echo "\n";
            
            // Check if production URI exists
            $productionUri = 'https://form-documents.onrender.com/oauth.php';
            $hasProductionUri = in_array($productionUri, $json['web']['redirect_uris']);
            echo "6. Production URI exists: " . ($hasProductionUri ? "YES ✅" : "NO ❌") . "\n\n";
        } else {
            echo "5. Redirect URIs: NOT FOUND ❌\n\n";
        }
        
        if (isset($json['web']['client_id'])) {
            echo "7. Client ID: " . substr($json['web']['client_id'], 0, 20) . "...\n\n";
        }
    } else {
        echo "4. JSON valid: NO ❌\n";
        echo "   Error: " . json_last_error_msg() . "\n\n";
    }
} else {
    echo "❌ credentials.json NOT FOUND!\n\n";
}

// Check environment variable
echo "=== ENVIRONMENT VARIABLE ===\n\n";
$envVar = getenv('GOOGLE_CREDENTIALS_JSON');
echo "GOOGLE_CREDENTIALS_JSON exists: " . ($envVar ? "YES ✅" : "NO ❌") . "\n";
if ($envVar) {
    echo "Length: " . strlen($envVar) . " characters\n";
    echo "Preview: " . substr($envVar, 0, 100) . "...\n\n";
}

echo "\n=== END DEBUG ===";
