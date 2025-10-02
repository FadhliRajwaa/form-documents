<?php
/**
 * Debug Environment Variables
 * HAPUS SETELAH DEBUG!
 */

header('Content-Type: text/plain');

echo "=== ENVIRONMENT VARIABLES DEBUG ===\n\n";

// Check DATABASE_URL
$databaseUrl = getenv('DATABASE_URL');
echo "DATABASE_URL exists: " . ($databaseUrl ? "YES ✅" : "NO ❌") . "\n";

if ($databaseUrl) {
    // Parse and show (hide password)
    $parts = parse_url($databaseUrl);
    echo "  Host: " . ($parts['host'] ?? 'N/A') . "\n";
    echo "  Port: " . ($parts['port'] ?? 'N/A') . "\n";
    echo "  Database: " . (isset($parts['path']) ? ltrim($parts['path'], '/') : 'N/A') . "\n";
    echo "  User: " . ($parts['user'] ?? 'N/A') . "\n";
    echo "  Password: " . (isset($parts['pass']) ? str_repeat('*', strlen($parts['pass'])) : 'N/A') . "\n";
} else {
    echo "  ❌ DATABASE_URL not set!\n";
    echo "  This will use file-based token storage (token.json)\n";
}

echo "\n=== OTHER ENV VARS ===\n\n";

$envVars = [
    'APP_NAME',
    'GOOGLE_CREDENTIALS_PATH',
    'GOOGLE_DRIVE_FOLDER_ID',
    'GOOGLE_SPREADSHEET_ID',
    'GOOGLE_CREDENTIALS_JSON'
];

foreach ($envVars as $var) {
    $value = getenv($var);
    if ($value) {
        if ($var === 'GOOGLE_CREDENTIALS_JSON') {
            echo "$var: " . substr($value, 0, 50) . "... (length: " . strlen($value) . ")\n";
        } else {
            echo "$var: $value\n";
        }
    } else {
        echo "$var: NOT SET ❌\n";
    }
}

echo "\n=== PHP PDO DRIVERS ===\n\n";
$drivers = PDO::getAvailableDrivers();
echo "Available drivers: " . implode(', ', $drivers) . "\n";
echo "PostgreSQL (pgsql) available: " . (in_array('pgsql', $drivers) ? "YES ✅" : "NO ❌") . "\n";

echo "\n=== END DEBUG ===";
