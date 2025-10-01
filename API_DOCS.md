# 📚 API Documentation

## GoogleDriveService

Class untuk handle semua operasi Google Drive.

### Methods

#### `getOrCreateFolder(string $folderName): string`

Mencari folder berdasarkan nama, jika tidak ada akan dibuat otomatis.

**Parameters:**
- `$folderName` - Nama folder yang akan dicari/dibuat

**Returns:**
- `string` - Folder ID

**Example:**
```php
$driveService = new GoogleDriveService();
$folderId = $driveService->getOrCreateFolder("John Doe");
```

---

#### `uploadFile(string $filePath, string $fileName, string $folderId): array`

Upload single file ke folder tertentu.

**Parameters:**
- `$filePath` - Path lokal file
- `$fileName` - Nama file
- `$folderId` - ID folder tujuan

**Returns:**
- `array` - File info dengan keys: `id`, `name`, `url`

**Example:**
```php
$fileInfo = $driveService->uploadFile(
    '/tmp/document.pdf',
    'document.pdf',
    '1a2b3c4d5e6f'
);
// Returns: ['id' => 'xxx', 'name' => 'document.pdf', 'url' => 'https://...']
```

---

#### `uploadMultipleFiles(array $files, string $folderId): array`

Upload multiple files dari $_FILES.

**Parameters:**
- `$files` - Array dari $_FILES (format multiple upload)
- `$folderId` - ID folder tujuan

**Returns:**
- `array` - Array of file info

**Example:**
```php
$uploadedFiles = $driveService->uploadMultipleFiles(
    $_FILES['dokumen'],
    $folderId
);
```

---

#### `getFolderUrl(string $folderId): string`

Dapatkan URL folder.

**Parameters:**
- `$folderId` - Folder ID

**Returns:**
- `string` - URL folder

---

## GoogleSheetsService

Class untuk handle semua operasi Google Sheets.

### Methods

#### `appendData(array $data): bool`

Tambah data baru ke spreadsheet.

**Parameters:**
- `$data` - Array associative dengan keys:
  - `nama` (string)
  - `email` (string)
  - `telepon` (string)
  - `keterangan` (string)
  - `folder_url` (string)
  - `dokumen_links` (string)

**Returns:**
- `bool` - Success status

**Example:**
```php
$sheetsService = new GoogleSheetsService();
$result = $sheetsService->appendData([
    'nama' => 'John Doe',
    'email' => 'john@example.com',
    'telepon' => '08123456789',
    'keterangan' => 'Test upload',
    'folder_url' => 'https://drive.google.com/...',
    'dokumen_links' => 'file1.pdf: https://...\nfile2.pdf: https://...'
]);
```

---

#### `getSpreadsheetUrl(): string`

Dapatkan URL spreadsheet.

**Returns:**
- `string` - URL spreadsheet

---

## Helper Functions (config.php)

### `jsonResponse(bool $success, string $message, array $data = [])`

Kirim JSON response dan exit.

**Parameters:**
- `$success` - Status sukses/gagal
- `$message` - Pesan response
- `$data` - Data tambahan (optional)

**Example:**
```php
jsonResponse(true, 'Upload berhasil', ['file_id' => '123']);
```

---

### `validateFile(array $file): array`

Validasi file upload.

**Parameters:**
- `$file` - File dari $_FILES

**Returns:**
- `array` - Array of error messages (empty jika valid)

**Example:**
```php
$errors = validateFile($_FILES['document']);
if (!empty($errors)) {
    // Handle errors
}
```

---

### `sanitizeFilename(string $filename): string`

Sanitize nama file, remove special characters.

**Parameters:**
- `$filename` - Original filename

**Returns:**
- `string` - Sanitized filename

**Example:**
```php
$safe = sanitizeFilename("My Document (1).pdf");
// Returns: "My_Document_1.pdf"
```

---

## API Endpoint

### POST `/process.php`

Upload dokumen dan simpan data ke Drive & Sheets.

**Request:**
- Method: `POST`
- Content-Type: `multipart/form-data`

**Parameters:**
- `nama` (string, required) - Nama lengkap
- `email` (string, required) - Email address
- `telepon` (string, required) - Nomor telepon
- `keterangan` (string, optional) - Keterangan tambahan
- `dokumen[]` (file[], required) - File yang akan diupload (bisa multiple)

**Response Success:**
```json
{
    "success": true,
    "message": "Dokumen berhasil diupload",
    "data": {
        "folder_url": "https://drive.google.com/drive/folders/xxx",
        "sheets_url": "https://docs.google.com/spreadsheets/d/xxx",
        "uploaded_files": [
            {
                "id": "xxx",
                "name": "file1.pdf",
                "url": "https://drive.google.com/file/d/xxx"
            }
        ]
    }
}
```

**Response Error:**
```json
{
    "success": false,
    "message": "Error message here",
    "data": []
}
```

---

## Constants

Defined in `config.php`:

```php
APP_NAME                  // Application name
BASE_PATH                 // Base directory path
UPLOAD_PATH              // Upload directory path
UPLOAD_MAX_SIZE          // Max file size in bytes
ALLOWED_EXTENSIONS       // Array of allowed extensions
GOOGLE_CREDENTIALS_PATH  // Path to credentials.json
GOOGLE_DRIVE_FOLDER_ID   // Parent folder ID in Drive
GOOGLE_SPREADSHEET_ID    // Spreadsheet ID
```

---

## Error Handling

All methods throw `Exception` on error. Always wrap in try-catch:

```php
try {
    $driveService = new GoogleDriveService();
    $folderId = $driveService->getOrCreateFolder("Test");
} catch (Exception $e) {
    error_log($e->getMessage());
    jsonResponse(false, 'Error: ' . $e->getMessage());
}
```

---

## Custom Extensions

### Tambah Field Baru di Form

1. **index.php** - Tambah input:
```html
<div class="form-group">
    <label for="alamat">Alamat</label>
    <textarea id="alamat" name="alamat"></textarea>
</div>
```

2. **process.php** - Ambil value:
```php
'alamat' => $_POST['alamat'] ?? ''
```

3. **GoogleSheetsService.php** - Update header dan data:
```php
// Di method createHeader()
$header = [['No', 'Timestamp', 'Nama', 'Email', 'Telepon', 'Alamat', ...]];

// Di method appendData()
$row = [
    $rowNumber - 1,
    date('Y-m-d H:i:s'),
    $data['nama'] ?? '',
    $data['email'] ?? '',
    $data['telepon'] ?? '',
    $data['alamat'] ?? '',  // Tambah field baru
    // ...
];
```

---

## Webhook Integration (Future)

Untuk integrate dengan webhook/notification:

```php
// Di process.php, setelah upload sukses:

// Send webhook notification
$webhookUrl = 'https://your-webhook.com/notify';
$webhookData = [
    'event' => 'document_uploaded',
    'nama' => $_POST['nama'],
    'folder_url' => $folderUrl,
    'timestamp' => date('c')
];

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
```
