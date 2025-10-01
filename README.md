# 📝 Form Document System

Sistem pendataan dan penyimpanan dokumen otomatis ke Google Drive & Google Sheets dengan fitur validasi.

## ✨ Fitur

- ✅ Form upload dokumen modern dan responsive
- ✅ Multiple file upload dengan drag & drop
- ✅ Otomatis upload ke Google Drive (folder sesuai nama)
- ✅ Otomatis simpan data ke Google Sheets
- ✅ Fitur validasi checkbox di Google Sheets
- ✅ Real-time validation
- ✅ Loading indicator
- ✅ Success modal dengan link ke Drive & Sheets

## 🛠️ Tech Stack

- **Backend:** PHP 7.4+
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **API:** Google Drive API, Google Sheets API
- **Dependencies:** Composer (`google/apiclient`)

## 📋 Requirements

- PHP 7.4 atau lebih tinggi
- Composer
- XAMPP/Apache Server
- Google Cloud Project dengan Drive & Sheets API enabled
- Google Service Account atau OAuth 2.0 Credentials

## 🚀 Instalasi

### 1. Clone atau Copy Project

```bash
cd E:\Xampp\htdocs\form-document
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Setup Google Cloud Project

#### a. Buat Project di Google Cloud Console

1. Buka [Google Cloud Console](https://console.cloud.google.com/)
2. Klik **Create Project**
3. Beri nama project (misal: "Form Document System")
4. Klik **Create**

#### b. Enable APIs

1. Di Google Cloud Console, buka **APIs & Services** > **Library**
2. Cari dan enable:
   - **Google Drive API**
   - **Google Sheets API**

#### c. Buat Service Account

1. Buka **APIs & Services** > **Credentials**
2. Klik **Create Credentials** > **Service Account**
3. Isi nama service account (misal: "form-document-sa")
4. Klik **Create and Continue**
5. Pilih role **Editor** (atau minimal **Drive File** dan **Sheets Editor**)
6. Klik **Done**

#### d. Download Credentials JSON

1. Di halaman **Credentials**, klik service account yang baru dibuat
2. Pergi ke tab **Keys**
3. Klik **Add Key** > **Create new key**
4. Pilih **JSON**
5. File akan ter-download otomatis
6. Rename file menjadi `credentials.json`
7. **Copy file `credentials.json` ke root folder project** (`E:\Xampp\htdocs\form-document\`)

### 4. Setup Google Drive

#### a. Buat Parent Folder

1. Buka [Google Drive](https://drive.google.com/)
2. Buat folder baru (misal: "Form Documents")
3. Copy **Folder ID** dari URL:
   ```
   https://drive.google.com/drive/folders/1a2b3c4d5e6f7g8h9i0j
                                          ^^^^^^^^^^^^^^^^^^^
                                          Ini adalah Folder ID
   ```

#### b. Share Folder ke Service Account

1. Klik kanan pada folder "Form Documents"
2. Pilih **Share**
3. Tambahkan email service account (ada di file `credentials.json` → `client_email`)
   - Formatnya: `form-document-sa@project-id.iam.gserviceaccount.com`
4. Beri akses **Editor**
5. Klik **Send**

### 5. Setup Google Sheets

#### a. Buat Spreadsheet Baru

1. Buka [Google Sheets](https://sheets.google.com/)
2. Buat spreadsheet baru (misal: "Data Dokumen")
3. Rename Sheet pertama menjadi **"Data Dokumen"** (sesuai dengan `$sheetName` di `GoogleSheetsService.php`)
4. Copy **Spreadsheet ID** dari URL:
   ```
   https://docs.google.com/spreadsheets/d/1a2b3c4d5e6f7g8h9i0j/edit
                                          ^^^^^^^^^^^^^^^^^^^
                                          Ini adalah Spreadsheet ID
   ```

#### b. Share Spreadsheet ke Service Account

1. Klik **Share** di Google Sheets
2. Tambahkan email service account (sama seperti di Drive)
3. Beri akses **Editor**
4. Klik **Send**

### 6. Konfigurasi Environment

1. Copy file `.env.example` menjadi `.env`:

```bash
copy .env.example .env
```

2. Edit file `.env` dan isi dengan data Anda:

```env
# Google API Configuration
GOOGLE_CREDENTIALS_PATH=credentials.json
GOOGLE_DRIVE_FOLDER_ID=1a2b3c4d5e6f7g8h9i0j
GOOGLE_SPREADSHEET_ID=1a2b3c4d5e6f7g8h9i0j

# Application Settings
APP_NAME="Form Document System"
UPLOAD_MAX_SIZE=10485760
ALLOWED_EXTENSIONS=pdf,doc,docx,jpg,jpeg,png
```

**Penting:** Ganti `GOOGLE_DRIVE_FOLDER_ID` dan `GOOGLE_SPREADSHEET_ID` dengan ID yang sudah Anda copy!

### 7. Setup Folder Uploads

Pastikan folder `uploads/` ada dan writable:

```bash
mkdir uploads
```

Di Windows, pastikan Apache memiliki write permission ke folder `uploads/`.

### 8. Jalankan XAMPP

1. Buka **XAMPP Control Panel**
2. Start **Apache**
3. Buka browser dan akses:
   ```
   http://localhost/form-document/
   ```

## 📁 Struktur Folder

```
form-document/
├── assets/
│   ├── css/
│   │   └── style.css          # Styling modern
│   └── js/
│       └── script.js          # Form validation & UX
├── src/
│   ├── GoogleDriveService.php # Service untuk Drive API
│   └── GoogleSheetsService.php # Service untuk Sheets API
├── uploads/                    # Temporary upload folder
├── vendor/                     # Composer dependencies
├── .env                        # Environment config (JANGAN commit!)
├── .env.example                # Template environment
├── .gitignore                  # Git ignore rules
├── composer.json               # PHP dependencies
├── config.php                  # Configuration loader
├── credentials.json            # Google credentials (JANGAN commit!)
├── index.php                   # Form page
├── process.php                 # Upload handler
└── README.md                   # Dokumentasi
```

## 🔄 Workflow

1. **User mengisi form** dengan data (Nama, Email, Telepon, Keterangan)
2. **User upload dokumen** (bisa multiple files)
3. **Klik Submit** → Data diproses
4. **Sistem membuat/mencari folder** di Google Drive sesuai nama user
5. **Dokumen diupload** ke folder tersebut
6. **Data disimpan** ke Google Sheets dengan:
   - Nomor urut
   - Timestamp
   - Data form
   - Link folder Drive
   - Link dokumen
   - Checkbox validasi (default: Belum Divalidasi)
7. **User menerima notifikasi** sukses dengan link ke Drive & Sheets

## 📊 Format Google Sheets

| No | Timestamp | Nama | Email | Telepon | Keterangan | Folder Drive | Link Dokumen | Status Validasi | Catatan |
|----|-----------|------|-------|---------|------------|--------------|--------------|-----------------|---------|
| 1  | 2025-10-01 19:00:00 | John Doe | john@email.com | 08123456789 | Test | [Link] | [Links] | ☑️ | - |

**Status Validasi** adalah checkbox yang bisa dicentang untuk menandai dokumen sudah divalidasi.

## 🎨 Fitur UI/UX

- ✅ Modern gradient background
- ✅ Responsive design (mobile-friendly)
- ✅ Drag & drop file upload
- ✅ Preview file yang dipilih
- ✅ Loading indicator saat upload
- ✅ Success modal dengan link langsung
- ✅ Alert messages untuk error/success
- ✅ Form validation real-time

## 🔒 Security

- ✅ File type validation
- ✅ File size validation
- ✅ XSS prevention (HTML escaping)
- ✅ CSRF protection ready (bisa tambahkan token)
- ✅ Credentials tidak ter-commit ke Git

## 🐛 Troubleshooting

### Error: "File .env tidak ditemukan"
**Solusi:** Copy `.env.example` menjadi `.env` dan isi konfigurasi

### Error: "Class 'Google\Client' not found"
**Solusi:** Jalankan `composer install`

### Error: "Permission denied" saat upload
**Solusi:** 
- Pastikan folder `uploads/` ada
- Pastikan Apache punya write permission

### Error: "The caller does not have permission"
**Solusi:**
- Pastikan sudah share folder Drive & Sheets ke email service account
- Pastikan role minimal **Editor**

### Data tidak masuk ke Sheets
**Solusi:**
- Cek nama Sheet di Sheets harus **"Data Dokumen"** (sesuai `$sheetName`)
- Cek Spreadsheet ID sudah benar di `.env`

### Dokumen tidak masuk ke Drive
**Solusi:**
- Cek Folder ID sudah benar di `.env`
- Pastikan folder sudah di-share ke service account

## 📝 Customization

### Ubah Maximum File Size

Edit di `.env`:
```env
UPLOAD_MAX_SIZE=20971520  # 20MB dalam bytes
```

### Ubah Allowed Extensions

Edit di `.env`:
```env
ALLOWED_EXTENSIONS=pdf,doc,docx,jpg,jpeg,png,zip,rar
```

### Ubah Nama Sheet

Edit di `src/GoogleSheetsService.php`:
```php
private $sheetName = 'Nama Sheet Anda';
```

### Tambah Field Form

1. Tambah input di `index.php`
2. Ambil value di `process.php`
3. Tambah kolom di `GoogleSheetsService.php` → method `createHeader()` dan `appendData()`

## 📞 Support

Jika ada masalah atau pertanyaan:
1. Cek bagian **Troubleshooting** di atas
2. Review kembali langkah **Setup Google Cloud Project**
3. Pastikan semua credentials sudah benar

## 📄 License

Dibuat untuk client dengan ❤️ by Jarvis
