# 🚀 Panduan Setup Cepat - Form Document System

## ⚡ Setup dalam 5 Langkah

### 1️⃣ Install Dependencies (2 menit)

Buka **Command Prompt** di folder project, lalu jalankan:

```bash
composer install
```

Tunggu sampai selesai download library Google APIs.

---

### 2️⃣ Setup Google Cloud (10 menit)

#### A. Buat Project & Enable APIs

1. Buka https://console.cloud.google.com/
2. **Create Project** → Beri nama → **Create**
3. Buka **APIs & Services** → **Library**
4. Cari dan **Enable**:
   - ✅ Google Drive API
   - ✅ Google Sheets API

#### B. Buat Service Account

1. **APIs & Services** → **Credentials** → **Create Credentials** → **Service Account**
2. Isi form:
   - **Service account name:** `form-document-sa`
   - **Service account ID:** (auto-fill)
   - **Description:** (optional)
3. Klik **CREATE AND CONTINUE**
4. **Grant this service account access to project:**
   - **Role:** Pilih **Editor**
5. Klik **CONTINUE**
6. Klik **DONE**
7. Klik **nama service account** yang baru dibuat
8. Tab **KEYS** → **ADD KEY** → **Create new key**
9. Pilih **JSON** → **CREATE**
10. File akan download → **Rename menjadi `credentials.json`**
11. **Copy file ke folder project** (`E:\Xampp\htdocs\form-document\credentials.json`)

⚠️ **PENTING:** Simpan email service account dari file credentials.json (key: `client_email`)

---

### 3️⃣ Setup Google Drive (3 menit)

1. Buka https://drive.google.com/
2. Buat folder baru: **"Form Documents"**
3. **Share folder ke Service Account:**
   - Klik kanan folder → **Share**
   - Tambahkan **email service account** (dari credentials.json)
   - Akses: **Editor**
   - **Uncheck "Notify people"**
   - Klik **Share**
4. Buka folder → Copy **Folder ID** dari URL:
   ```
   https://drive.google.com/drive/folders/FOLDER_ID_NYA_DISINI
   ```

---

### 4️⃣ Setup Google Sheets (3 menit)

1. Buka https://sheets.google.com/
2. Buat spreadsheet baru: **"Data Dokumen"**
3. **Rename sheet pertama** (kiri bawah) menjadi: **"Data Dokumen"**
4. **Share spreadsheet ke Service Account:**
   - Klik **Share** (kanan atas)
   - Tambahkan **email service account** yang sama
   - Akses: **Editor**
   - **Uncheck "Notify people"**
   - Klik **Share**
5. Copy **Spreadsheet ID** dari URL:
   ```
   https://docs.google.com/spreadsheets/d/SPREADSHEET_ID_NYA_DISINI/edit
   ```

---

### 5️⃣ Konfigurasi .env (2 menit)

1. Copy file `.env.example` menjadi `.env`:
   ```bash
   copy .env.example .env
   ```

2. Edit file `.env` dan isi:

```env
GOOGLE_CREDENTIALS_PATH=credentials.json
GOOGLE_DRIVE_FOLDER_ID=paste_folder_id_dari_step_3
GOOGLE_SPREADSHEET_ID=paste_spreadsheet_id_dari_step_4

APP_NAME="Form Document System"
UPLOAD_MAX_SIZE=10485760
ALLOWED_EXTENSIONS=pdf,doc,docx,jpg,jpeg,png
```

**✅ SELESAI!**

---

## 🎯 Test Aplikasi

1. Jalankan **XAMPP** → Start **Apache**
2. Buka browser: `http://localhost/form-document/`
3. Form langsung muncul (tidak perlu login!)
4. Isi form dan upload dokumen
5. Cek hasilnya di:
   - **Google Drive** → Folder "Form Documents" → Ada folder baru sesuai nama
   - **Google Sheets** → Data masuk otomatis dengan checkbox validasi

---

## 🎨 Fitur yang Sudah Jalan

✅ Form modern dan responsive  
✅ Upload multiple files dengan drag & drop  
✅ Auto create folder di Drive (sesuai nama user)  
✅ Auto save data ke Sheets  
✅ Checkbox validasi di Sheets (kolom "Status Validasi")  
✅ Link langsung ke Drive & Sheets setelah upload  

---

## ❓ Troubleshooting Cepat

### ❌ Error: "Class 'Google\Client' not found"
**Solusi:** Jalankan `composer install`

### ❌ Error: "The caller does not have permission" atau "Storage quota exceeded"
**Solusi:** 
- Pastikan sudah **share** folder Drive & Sheets ke email service account
- Email service account ada di file `credentials.json` (key: `client_email`)
- Pastikan akses minimal **Editor**
- Format email: `xxx@xxx.iam.gserviceaccount.com`

### ❌ Data tidak masuk ke Sheets
**Solusi:**
- Pastikan nama sheet di Google Sheets adalah **"Data Dokumen"** (exact match)
- Cek Spreadsheet ID di `.env` sudah benar

### ❌ Dokumen tidak masuk ke Drive
**Solusi:**
- Cek Folder ID di `.env` sudah benar
- Pastikan folder sudah di-share ke service account dengan akses Editor

---

## 📞 Butuh Bantuan?

Baca file **README.md** untuk dokumentasi lengkap!

---

**Dibuat dengan ❤️ untuk Client Tuan Fadhli**
