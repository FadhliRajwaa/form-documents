# 🏗️ Arsitektur Sistem

## 📊 Diagram Alur

```
┌──────────────┐
│   Browser    │
│   (User)     │
└──────┬───────┘
       │
       │ 1. Isi form & upload
       ▼
┌──────────────────────┐
│    index.php         │
│  (Form Interface)    │
│  + Validation (JS)   │
└──────┬───────────────┘
       │
       │ 2. POST data
       ▼
┌──────────────────────┐
│   process.php        │
│  (Upload Handler)    │
│  - Validate input    │
│  - Process files     │
└──────┬───────────────┘
       │
       ├─────────────────┬─────────────────┐
       │                 │                 │
       │ 3. Create       │ 5. Upload       │ 7. Save
       │    folder       │    files        │    data
       ▼                 ▼                 ▼
┌─────────────┐   ┌─────────────┐   ┌─────────────┐
│   Google    │   │   Google    │   │   Google    │
│   Drive     │   │   Drive     │   │   Sheets    │
│   Service   │   │   Service   │   │   Service   │
└──────┬──────┘   └──────┬──────┘   └──────┬──────┘
       │                 │                 │
       │ 4. Return       │ 6. Return       │ 8. Return
       │    folder ID    │    file info    │    success
       ▼                 ▼                 ▼
┌────────────────────────────────────────────┐
│             Google Cloud API               │
│  ┌──────────────┐      ┌──────────────┐   │
│  │ Google Drive │      │ Google Sheets│   │
│  └──────────────┘      └──────────────┘   │
└────────────────────────────────────────────┘
       │                 │
       │ 9. Store        │ 10. Store
       ▼                 ▼
┌─────────────┐   ┌─────────────┐
│   Folder:   │   │ Spreadsheet │
│  John Doe   │   │ Row with    │
│  - file1.pdf│   │ checkbox    │
│  - file2.doc│   │ validation  │
└─────────────┘   └─────────────┘
```

---

## 🗂️ Struktur File

```
form-document/
│
├── 📄 index.php              # Main form page (UI)
├── 📄 process.php            # Upload processor (Backend logic)
├── 📄 config.php             # Configuration & helpers
│
├── 📁 src/                   # Service classes
│   ├── GoogleDriveService.php    # Drive API operations
│   └── GoogleSheetsService.php   # Sheets API operations
│
├── 📁 assets/                # Frontend assets
│   ├── css/
│   │   └── style.css         # Modern styling
│   └── js/
│       └── script.js         # Form validation & UX
│
├── 📁 uploads/               # Temporary upload storage
│
├── 📁 vendor/                # Composer dependencies
│
├── 📄 .env                   # Environment config (SECRET!)
├── 📄 .env.example           # Template config
├── 📄 credentials.json       # Google credentials (SECRET!)
│
└── 📚 Dokumentasi
    ├── README.md             # Full documentation
    ├── SETUP_GUIDE.md        # Quick setup guide
    ├── API_DOCS.md           # Developer API docs
    └── ARCHITECTURE.md       # This file
```

---

## 🔄 Workflow Detail

### 1. User Interaction (Frontend)

```
User fills form → JavaScript validates → Shows file preview
                                              │
                    ┌─────────────────────────┘
                    │
                    ▼
              User clicks Submit
                    │
                    ▼
            Loading overlay shown
                    │
                    ▼
              POST to process.php
```

### 2. Backend Processing

```php
// process.php

1. Validate input (nama, email, telepon, files)
2. Initialize GoogleDriveService & GoogleSheetsService
3. Create/get folder: $driveService->getOrCreateFolder($nama)
4. Upload files: $driveService->uploadMultipleFiles($files, $folderId)
5. Prepare data array
6. Save to Sheets: $sheetsService->appendData($data)
7. Return JSON response
```

### 3. Google Drive Operations

```php
// GoogleDriveService

getOrCreateFolder($folderName)
    └─> searchFolder($folderName)
            │
            ├─> Found? → Return existing folder ID
            │
            └─> Not found? → createFolder($folderName)
                                └─> Return new folder ID

uploadMultipleFiles($files, $folderId)
    └─> Loop each file
            └─> uploadFile($filePath, $fileName, $folderId)
                    └─> Return file info (id, name, url)
```

### 4. Google Sheets Operations

```php
// GoogleSheetsService

appendData($data)
    └─> getNextRowNumber()
            └─> Prepare row data
                    └─> Update spreadsheet
                            └─> addCheckbox($rowNumber)
```

---

## 🔐 Security Flow

```
Input → Validation → Sanitization → Process → Response
  │         │            │             │         │
  │         │            │             │         └─> JSON encode
  │         │            │             │
  │         │            │             └─> Error handling
  │         │            │
  │         │            └─> sanitizeFilename()
  │         │                escapeHtml()
  │         │
  │         └─> validateFile()
  │             - File size check
  │             - Extension check
  │             - MIME type check
  │
  └─> Required field check
      Email format check
      Phone format check
```

---

## 🌐 API Integration

```
┌──────────────────────────────────────────────┐
│           Google Cloud Platform              │
│                                              │
│  ┌────────────────────────────────────┐     │
│  │      Service Account               │     │
│  │  (credentials.json)                │     │
│  │  - client_email                    │     │
│  │  - private_key                     │     │
│  └────────┬───────────────────────────┘     │
│           │                                  │
│           │ Authenticate                     │
│           ▼                                  │
│  ┌────────────────────────────────────┐     │
│  │      Google APIs                   │     │
│  │  - Drive API (upload, list)        │     │
│  │  - Sheets API (read, write)        │     │
│  └────────────────────────────────────┘     │
│                                              │
└──────────────────────────────────────────────┘
```

---

## 📦 Dependencies

```
Composer (google/apiclient v2.15+)
    │
    ├─> google/auth
    ├─> google/apiclient-services
    │   ├─> Drive API
    │   └─> Sheets API
    ├─> guzzlehttp/guzzle
    └─> firebase/php-jwt
```

---

## 🎯 Data Flow

```
Form Data → POST → Validation → Processing
                                    │
                    ┌───────────────┴───────────────┐
                    │                               │
                    ▼                               ▼
          ┌──────────────────┐          ┌──────────────────┐
          │  Google Drive    │          │  Google Sheets   │
          │                  │          │                  │
          │  Folder: "Name"  │          │  Row:            │
          │  ├─ file1.pdf    │          │  - No            │
          │  ├─ file2.doc    │◀────────▶│  - Timestamp     │
          │  └─ file3.jpg    │  Links   │  - Data          │
          │                  │          │  - Folder URL    │
          │                  │          │  - Files URL     │
          │                  │          │  - ☐ Validation  │
          └──────────────────┘          └──────────────────┘
                    │                               │
                    └───────────────┬───────────────┘
                                    ▼
                            JSON Response
                                    │
                                    ▼
                            Success Modal
                           (with links)
```

---

## 🔧 Configuration Flow

```
.env.example
    │
    │ copy
    ▼
.env ──────────> config.php ──────────> Services
    │               │                        │
    │               │                        ├─> GoogleDriveService
    │               │                        └─> GoogleSheetsService
    │               │
    │               └─> Constants:
    │                   - GOOGLE_DRIVE_FOLDER_ID
    │                   - GOOGLE_SPREADSHEET_ID
    │                   - UPLOAD_MAX_SIZE
    │                   - ALLOWED_EXTENSIONS
    │
    └─> credentials.json ──> Google Client
```

---

## 📱 Responsive Design

```
Desktop (> 768px)      Tablet (768px)       Mobile (< 768px)
┌──────────────┐       ┌─────────────┐       ┌──────────┐
│   [Form]     │       │   [Form]    │       │ [Form]   │
│   ┌──────┐   │       │  ┌──────┐   │       │ ┌──────┐ │
│   │Field1│   │       │  │Field1│   │       │ │Field1│ │
│   │Field2│   │       │  │Field2│   │       │ │Field2│ │
│   └──────┘   │       │  └──────┘   │       │ └──────┘ │
│              │       │             │       │          │
│  [Reset][OK] │       │ [Reset][OK] │       │ [Reset]  │
│              │       │             │       │ [Submit] │
└──────────────┘       └─────────────┘       └──────────┘
```

---

## ⚡ Performance Optimization

```
Frontend:
- CSS/JS compression (gzip)
- Browser caching (1 week for static files)
- Lazy loading images
- Debounced validation

Backend:
- Efficient file handling (stream upload)
- Batch operations where possible
- Error logging (not echoing)
- Connection pooling

API:
- Minimal API calls
- Batch requests when possible
- Proper scopes (only what's needed)
```

---

## 🔄 Future Enhancements

1. **Authentication**: Add user login system
2. **Dashboard**: Admin panel untuk manage submissions
3. **Email Notification**: Auto-email saat upload sukses
4. **File Preview**: Preview dokumen sebelum upload
5. **Bulk Actions**: Validasi multiple rows sekaligus
6. **Export**: Export data ke PDF/Excel
7. **Search/Filter**: Search data di Sheets
8. **Webhook**: Integrate dengan sistem lain

---

**Sistem ini dirancang dengan prinsip:**
- ✅ **Simple** - Easy to understand & maintain
- ✅ **Secure** - Validation & sanitization
- ✅ **Scalable** - Can handle growth
- ✅ **Modern** - Best practices & clean code
