<?php
require_once 'config.php';

use App\GoogleDriveService;
use App\GoogleSheetsService;

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed');
}

try {
    // Validate required fields
    $requiredFields = ['nama', 'email', 'telepon'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            jsonResponse(false, "Field {$field} wajib diisi");
        }
    }
    
    // Validate files
    if (empty($_FILES['dokumen']['name'][0])) {
        jsonResponse(false, 'Minimal harus upload 1 dokumen');
    }
    
    // Validate each file
    $errors = [];
    foreach ($_FILES['dokumen']['name'] as $key => $filename) {
        $file = [
            'name' => $_FILES['dokumen']['name'][$key],
            'type' => $_FILES['dokumen']['type'][$key],
            'tmp_name' => $_FILES['dokumen']['tmp_name'][$key],
            'error' => $_FILES['dokumen']['error'][$key],
            'size' => $_FILES['dokumen']['size'][$key]
        ];
        
        $fileErrors = validateFile($file);
        if (!empty($fileErrors)) {
            $errors = array_merge($errors, $fileErrors);
        }
    }
    
    if (!empty($errors)) {
        jsonResponse(false, implode(', ', $errors));
    }
    
    // Initialize services
    $driveService = new GoogleDriveService();
    $sheetsService = new GoogleSheetsService();
    
    // Get folder name from 'nama' field
    $folderName = $_POST['nama'];
    
    // Create or get folder in Google Drive
    $folderId = $driveService->getOrCreateFolder($folderName);
    $folderUrl = $driveService->getFolderUrl($folderId);
    
    // Upload files to Google Drive
    $uploadedFiles = $driveService->uploadMultipleFiles($_FILES['dokumen'], $folderId);
    
    // Prepare document links for spreadsheet
    $documentLinks = [];
    foreach ($uploadedFiles as $file) {
        $documentLinks[] = $file['name'] . ': ' . $file['url'];
    }
    $documentLinksString = implode("\n", $documentLinks);
    
    // Prepare data for Google Sheets
    $sheetData = [
        'nama' => $_POST['nama'],
        'email' => $_POST['email'],
        'telepon' => $_POST['telepon'],
        'keterangan' => $_POST['keterangan'] ?? '',
        'folder_url' => $folderUrl,
        'dokumen_links' => $documentLinksString
    ];
    
    // Append data to Google Sheets
    $sheetsResult = $sheetsService->appendData($sheetData);
    
    if (!$sheetsResult) {
        throw new Exception('Gagal menyimpan data ke Google Sheets');
    }
    
    // Clean up temporary files
    foreach ($_FILES['dokumen']['tmp_name'] as $tmpFile) {
        if (file_exists($tmpFile)) {
            @unlink($tmpFile);
        }
    }
    
    // Success response
    jsonResponse(true, 'Dokumen berhasil diupload', [
        'folder_url' => $folderUrl,
        'sheets_url' => $sheetsService->getSpreadsheetUrl(),
        'uploaded_files' => $uploadedFiles
    ]);
    
} catch (Exception $e) {
    error_log("Upload error: " . $e->getMessage());
    jsonResponse(false, 'Terjadi kesalahan: ' . $e->getMessage());
}
