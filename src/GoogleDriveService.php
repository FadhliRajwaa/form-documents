<?php

namespace App;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class GoogleDriveService {
    private $service;
    private $parentFolderId;
    
    public function __construct() {
        $client = $this->getClient();
        $this->service = new Drive($client);
        $this->parentFolderId = GOOGLE_DRIVE_FOLDER_ID;
    }
    
    /**
     * Inisialisasi Google Client dengan OAuth 2.0
     */
    private function getClient() {
        $client = new Client();
        $client->setApplicationName(APP_NAME);
        $client->setScopes([Drive::DRIVE_FILE]);
        $client->setAuthConfig(GOOGLE_CREDENTIALS_PATH);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        
        // Load token from database
        require_once BASE_PATH . '/token-storage.php';
        $tokenStorage = new \TokenStorage();
        $tokenKey = 'google_oauth_token';
        
        if ($tokenStorage->hasToken($tokenKey)) {
            $tokenData = $tokenStorage->loadToken($tokenKey);
            $accessToken = json_decode($tokenData, true);
            $client->setAccessToken($accessToken);
            
            // Refresh token if expired
            if ($client->isAccessTokenExpired()) {
                if ($client->getRefreshToken()) {
                    $newToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                    $tokenStorage->saveToken($tokenKey, json_encode($newToken));
                } else {
                    throw new \Exception('Token expired. Please re-authorize at: oauth.php');
                }
            }
        } else {
            throw new \Exception('No token found. Please authorize first at: oauth.php');
        }
        
        return $client;
    }
    
    /**
     * Cari atau buat folder berdasarkan nama
     * 
     * @param string $folderName Nama folder yang akan dicari/dibuat
     * @return string Folder ID
     */
    public function getOrCreateFolder($folderName) {
        // Sanitize folder name
        $folderName = $this->sanitizeFolderName($folderName);
        
        // Cari folder yang sudah ada
        $folderId = $this->searchFolder($folderName);
        
        if ($folderId) {
            return $folderId;
        }
        
        // Jika tidak ada, buat folder baru
        return $this->createFolder($folderName);
    }
    
    /**
     * Cari folder berdasarkan nama di dalam parent folder
     */
    private function searchFolder($folderName) {
        $query = "name='{$folderName}' and mimeType='application/vnd.google-apps.folder'";
        $query .= " and '{$this->parentFolderId}' in parents and trashed=false";
        
        $response = $this->service->files->listFiles([
            'q' => $query,
            'spaces' => 'drive',
            'fields' => 'files(id, name)',
            'supportsAllDrives' => true,
            'includeItemsFromAllDrives' => true
        ]);
        
        $files = $response->getFiles();
        
        if (count($files) > 0) {
            return $files[0]->getId();
        }
        
        return null;
    }
    
    /**
     * Buat folder baru
     */
    private function createFolder($folderName) {
        $fileMetadata = new DriveFile([
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => [$this->parentFolderId]
        ]);
        
        $folder = $this->service->files->create($fileMetadata, [
            'fields' => 'id',
            'supportsAllDrives' => true
        ]);
        
        return $folder->id;
    }
    
    /**
     * Upload file ke folder tertentu
     * 
     * @param string $filePath Path lokal file
     * @param string $fileName Nama file
     * @param string $folderId ID folder tujuan
     * @return array File info (id, name, webViewLink)
     */
    public function uploadFile($filePath, $fileName, $folderId) {
        $fileName = sanitizeFilename($fileName);
        
        // Detect MIME type
        $mimeType = mime_content_type($filePath);
        
        $fileMetadata = new DriveFile([
            'name' => $fileName,
            'parents' => [$folderId]
        ]);
        
        $content = file_get_contents($filePath);
        
        $file = $this->service->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id, name, webViewLink',
            'supportsAllDrives' => true
        ]);
        
        return [
            'id' => $file->id,
            'name' => $file->name,
            'url' => $file->webViewLink
        ];
    }
    
    /**
     * Upload multiple files
     * 
     * @param array $files Array of uploaded files from $_FILES
     * @param string $folderId Target folder ID
     * @return array Array of uploaded file info
     */
    public function uploadMultipleFiles($files, $folderId) {
        $uploadedFiles = [];
        
        foreach ($files['tmp_name'] as $key => $tmpName) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = $files['name'][$key];
                
                $fileInfo = $this->uploadFile($tmpName, $fileName, $folderId);
                $uploadedFiles[] = $fileInfo;
            }
        }
        
        return $uploadedFiles;
    }
    
    /**
     * Sanitize folder name
     */
    private function sanitizeFolderName($name) {
        // Remove special characters but keep spaces
        $name = preg_replace('/[^\p{L}\p{N}\s_\-]/u', '', $name);
        // Remove multiple spaces
        $name = preg_replace('/\s+/', ' ', $name);
        return trim($name);
    }
    
    /**
     * Get folder URL
     */
    public function getFolderUrl($folderId) {
        return "https://drive.google.com/drive/folders/{$folderId}";
    }
}
