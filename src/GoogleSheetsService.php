<?php

namespace App;

use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use Google\Service\Sheets\BatchUpdateSpreadsheetRequest;
use Google\Service\Sheets\Request as SheetsRequest;
use Google\Service\Sheets\CellData;
use Google\Service\Sheets\ExtendedValue;
use Google\Service\Sheets\CellFormat;
use Google\Service\Sheets\Color;

class GoogleSheetsService {
    private $service;
    private $spreadsheetId;
    private $sheetName = 'Data Dokumen';
    
    public function __construct() {
        $client = $this->getClient();
        $this->service = new Sheets($client);
        $this->spreadsheetId = GOOGLE_SPREADSHEET_ID;
        
        // Ensure header exists
        $this->ensureHeader();
    }
    
    /**
     * Inisialisasi Google Client dengan OAuth 2.0
     */
    private function getClient() {
        $client = new Client();
        $client->setApplicationName(APP_NAME);
        $client->setScopes([Sheets::SPREADSHEETS]);
        $client->setAuthConfig(GOOGLE_CREDENTIALS_PATH);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        
        // Load token if exists
        $tokenPath = BASE_PATH . '/token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }
        
        // Refresh token if expired
        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            } else {
                throw new \Exception('Token expired. Please re-authorize at: oauth.php');
            }
        }
        
        return $client;
    }
    
    /**
     * Pastikan header kolom ada
     */
    private function ensureHeader() {
        try {
            // Check if sheet has data
            $range = "{$this->sheetName}!A1:J1";
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
            $values = $response->getValues();
            
            // If no header, create one
            if (empty($values)) {
                $this->createHeader();
            }
        } catch (\Exception $e) {
            // Sheet might not exist or wrong name
            $errorMsg = $e->getMessage();
            error_log("Error checking header: " . $errorMsg);
            
            // Throw more helpful error
            if (strpos($errorMsg, 'not found') !== false) {
                throw new \Exception("Sheet '{$this->sheetName}' tidak ditemukan di Spreadsheet. Pastikan nama sheet adalah 'Data Dokumen' (exact match)");
            }
            throw new \Exception("Error accessing Google Sheets: " . $errorMsg);
        }
    }
    
    /**
     * Buat header kolom
     */
    private function createHeader() {
        $header = [
            [
                'No',
                'Timestamp',
                'Nama',
                'Email',
                'Telepon',
                'Keterangan',
                'Folder Drive',
                'Link Dokumen',
                'Status Validasi',
                'Catatan'
            ]
        ];
        
        $range = "{$this->sheetName}!A1:J1";
        $body = new ValueRange([
            'values' => $header
        ]);
        
        $params = [
            'valueInputOption' => 'RAW'
        ];
        
        $this->service->spreadsheets_values->update(
            $this->spreadsheetId,
            $range,
            $body,
            $params
        );
        
        // Format header (bold)
        $this->formatHeader();
    }
    
    /**
     * Format header (bold dan background warna)
     */
    private function formatHeader() {
        $requests = [
            new SheetsRequest([
                'repeatCell' => [
                    'range' => [
                        'sheetId' => $this->getSheetId(),
                        'startRowIndex' => 0,
                        'endRowIndex' => 1,
                        'startColumnIndex' => 0,
                        'endColumnIndex' => 10
                    ],
                    'cell' => [
                        'userEnteredFormat' => [
                            'backgroundColor' => [
                                'red' => 0.26,
                                'green' => 0.52,
                                'blue' => 0.96
                            ],
                            'textFormat' => [
                                'foregroundColor' => [
                                    'red' => 1.0,
                                    'green' => 1.0,
                                    'blue' => 1.0
                                ],
                                'fontSize' => 11,
                                'bold' => true
                            ]
                        ]
                    ],
                    'fields' => 'userEnteredFormat(backgroundColor,textFormat)'
                ]
            ])
        ];
        
        $batchUpdateRequest = new BatchUpdateSpreadsheetRequest([
            'requests' => $requests
        ]);
        
        try {
            $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $batchUpdateRequest);
        } catch (\Exception $e) {
            error_log("Error formatting header: " . $e->getMessage());
        }
    }
    
    /**
     * Get Sheet ID by name
     */
    private function getSheetId() {
        try {
            $spreadsheet = $this->service->spreadsheets->get($this->spreadsheetId);
            $sheets = $spreadsheet->getSheets();
            
            foreach ($sheets as $sheet) {
                if ($sheet->getProperties()->getTitle() === $this->sheetName) {
                    return $sheet->getProperties()->getSheetId();
                }
            }
        } catch (\Exception $e) {
            error_log("Error getting sheet ID: " . $e->getMessage());
        }
        
        return 0; // Default to first sheet
    }
    
    /**
     * Tambah data baru ke spreadsheet
     * 
     * @param array $data Data form yang akan ditambahkan
     * @return bool Success status
     */
    public function appendData($data) {
        // Get next row number
        $rowNumber = $this->getNextRowNumber();
        
        // Prepare data row
        $row = [
            $rowNumber - 1, // No (minus header)
            date('Y-m-d H:i:s'), // Timestamp
            $data['nama'] ?? '',
            $data['email'] ?? '',
            $data['telepon'] ?? '',
            $data['keterangan'] ?? '',
            $data['folder_url'] ?? '',
            $data['dokumen_links'] ?? '',
            'FALSE', // Default status (unchecked checkbox)
            '' // Catatan kosong
        ];
        
        $range = "{$this->sheetName}!A{$rowNumber}:J{$rowNumber}";
        $body = new ValueRange([
            'values' => [$row]
        ]);
        
        $params = [
            'valueInputOption' => 'USER_ENTERED'
        ];
        
        try {
            $result = $this->service->spreadsheets_values->update(
                $this->spreadsheetId,
                $range,
                $body,
                $params
            );
            
            // Add checkbox for validation
            $this->addCheckbox($rowNumber);
            
            return true;
        } catch (\Exception $e) {
            error_log("Error appending data: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get next available row number
     */
    private function getNextRowNumber() {
        try {
            $range = "{$this->sheetName}!A:A";
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
            $values = $response->getValues();
            
            return count($values) + 1;
        } catch (\Exception $e) {
            return 2; // Start from row 2 if error (row 1 is header)
        }
    }
    
    /**
     * Tambah checkbox untuk validasi
     */
    private function addCheckbox($rowNumber) {
        $requests = [
            new SheetsRequest([
                'setDataValidation' => [
                    'range' => [
                        'sheetId' => $this->getSheetId(),
                        'startRowIndex' => $rowNumber - 1,
                        'endRowIndex' => $rowNumber,
                        'startColumnIndex' => 8, // Column I (Status Validasi)
                        'endColumnIndex' => 9
                    ],
                    'rule' => [
                        'condition' => [
                            'type' => 'BOOLEAN'
                        ],
                        'strict' => true,
                        'showCustomUi' => true
                    ]
                ]
            ])
        ];
        
        $batchUpdateRequest = new BatchUpdateSpreadsheetRequest([
            'requests' => $requests
        ]);
        
        try {
            $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $batchUpdateRequest);
        } catch (\Exception $e) {
            error_log("Error adding checkbox: " . $e->getMessage());
        }
    }
    
    /**
     * Get spreadsheet URL
     */
    public function getSpreadsheetUrl() {
        return "https://docs.google.com/spreadsheets/d/{$this->spreadsheetId}";
    }
}
