# 🔐 Panduan Setup OAuth untuk Client

## 👋 Halo Client!

Sistem form document Anda sudah siap, tinggal **setup OAuth sekali** (5-10 menit) dan sistem akan berjalan selamanya!

---

## 🎯 Yang Perlu Anda Tahu

### **Apakah User yang Isi Form Perlu Login Google?**
**❌ TIDAK!** User bisa langsung isi form tanpa login.

### **Siapa yang Perlu Login?**
**✅ HANYA ANDA (Client/Admin)** - Sekali saja untuk setup!

### **Bagaimana Cara Kerjanya?**
```
Setup (Sekali):
Anda → Login OAuth → Token tersimpan

Penggunaan (Selamanya):
User → Isi form → Submit → File upload otomatis ke Drive Anda
```

---

## 📋 Langkah Setup OAuth (10 Menit)

### **Step 1: Buat OAuth 2.0 Credentials di Google Cloud**

#### **1a. Buka Google Cloud Console**
- Link: https://console.cloud.google.com/
- Login dengan akun Google Anda

#### **1b. Buat/Pilih Project**
- Klik **"Select a project"** (atas kiri)
- **NEW PROJECT** → Nama: `Form Document System`
- **CREATE**

#### **1c. Enable API**
1. **Menu ☰** → **APIs & Services** → **Library**
2. Cari **"Google Drive API"** → **ENABLE**
3. Cari **"Google Sheets API"** → **ENABLE**

#### **1d. Setup OAuth Consent Screen**
1. **APIs & Services** → **OAuth consent screen**
2. User Type: **External** → **CREATE**
3. Isi form:
   - **App name:** `Form Document System`
   - **User support email:** Pilih email Anda
   - **Developer contact:** Email Anda
4. **SAVE AND CONTINUE**
5. **Scopes:** Skip (klik **SAVE AND CONTINUE**)
6. **Test users:** 
   - Klik **ADD USERS**
   - Masukkan **email Google Anda sendiri**
   - **ADD**
7. **SAVE AND CONTINUE**
8. **BACK TO DASHBOARD**

#### **1e. Buat OAuth Client ID**
1. **APIs & Services** → **Credentials**
2. **+ CREATE CREDENTIALS** → **OAuth client ID**
3. **Application type:** Web application
4. **Name:** `Form Document Web`
5. **Authorized redirect URIs:**
   - Klik **ADD URI**
   - Ketik: `http://localhost/form-document/oauth.php`
   - ⚠️ **PENTING:** Sesuaikan dengan path project Anda!
6. **CREATE**
7. **Download JSON** (icon download di sebelah kanan)
8. **Rename file** menjadi: `credentials.json`
9. **Copy ke folder project:** `E:\Xampp\htdocs\form-document\credentials.json`

---

### **Step 2: Setup Folder & Spreadsheet**

#### **2a. Google Drive**
1. Buka https://drive.google.com/
2. Buat folder: **"Form Documents"**
3. Buka folder → Copy **Folder ID** dari URL:
   ```
   https://drive.google.com/drive/folders/1m2TH9NpJwMXYdbmUDk1-KJr44nQeVO6x
                                           ^^^ Copy ID ini ^^^
   ```

#### **2b. Google Sheets**
1. Buka https://sheets.google.com/
2. Buat spreadsheet baru: **"Data Dokumen"**
3. **Rename sheet tab** (bawah) menjadi: **"Data Dokumen"** (exact!)
4. Copy **Spreadsheet ID** dari URL:
   ```
   https://docs.google.com/spreadsheets/d/1I5wt481xCNgh2jEy2AfzCgjYsJqgjpWHQrVORwuYsQg/edit
                                          ^^^ Copy ID ini ^^^
   ```

#### **2c. Update `.env`**
Edit file `.env` di project:
```env
GOOGLE_DRIVE_FOLDER_ID=paste_folder_id_disini
GOOGLE_SPREADSHEET_ID=paste_spreadsheet_id_disini
```

---

### **Step 3: Login OAuth (Sekali Doang!)**

1. **Jalankan XAMPP** → Start Apache
2. **Buka browser:** `http://localhost/form-document/oauth.php`
3. Klik **"Login dengan Google"**
4. **Pilih akun Google Anda**
5. Akan muncul warning **"Google hasn't verified this app"**
   - Ini **NORMAL** untuk development
   - Klik **"Continue"** atau **"Advanced"** → **"Go to Form Document System (unsafe)"**
6. **Allow** semua permission:
   - ✅ See, edit, create, and delete your Google Drive files
   - ✅ See, edit, create, and delete your Google Sheets spreadsheets
7. Klik **"Continue"** atau **"Allow"**
8. Anda akan redirect kembali → Muncul **"Autentikasi Berhasil!"**
9. **SELESAI!** Token sudah tersimpan di `token.json`

---

### **Step 4: Test Form**

1. Buka: `http://localhost/form-document/`
2. Form langsung muncul (tidak redirect OAuth lagi)
3. Isi form & upload dokumen
4. Submit
5. Cek hasil:
   - **Google Drive:** Folder "Form Documents" → Ada subfolder baru (sesuai nama)
   - **Google Sheets:** Data masuk dengan checkbox validasi

---

## ✅ **Setelah Setup Berhasil**

### **Yang Terjadi:**
- ✅ File `token.json` sudah ter-generate
- ✅ Form bisa diakses siapa saja (publik)
- ✅ User **TIDAK PERLU** login Google
- ✅ Dokumen otomatis masuk ke Drive Anda
- ✅ Data otomatis masuk ke Sheets Anda
- ✅ Token **auto-refresh**, tidak perlu login ulang

### **Cara Validasi Dokumen:**
Di Google Sheets, kolom **"Status Validasi"**:
- ☐ Unchecked = Belum divalidasi
- ☑ Checked = Sudah valid

Tinggal **centang** untuk validasi dokumen!

---

## 🔄 **Token Management**

### **Apakah Token Kadaluarsa?**
**Tidak!** Token akan **auto-refresh** selamanya, kecuali:
- ❌ Anda manually revoke di Google Account Settings
- ❌ Password Google berubah
- ❌ 6 bulan tidak dipakai (untuk unverified app)

### **Cara Logout / Re-authorize:**
1. Delete file `token.json`
2. Buka `http://localhost/form-document/`
3. Akan redirect ke OAuth → Login ulang

### **Cara Revoke Access:**
1. Buka: https://myaccount.google.com/permissions
2. Cari **"Form Document System"**
3. Klik **"Remove Access"**

---

## 🚀 **Deploy ke Production**

Jika mau deploy ke hosting:

### **1. Upload Files**
Upload semua file termasuk `token.json` ke server

### **2. Update Redirect URI**
Di Google Cloud Console:
1. **Credentials** → Edit OAuth Client
2. **Authorized redirect URIs:**
   - Tambah: `https://yourdomain.com/form-document/oauth.php`
3. **SAVE**

### **3. Re-authorize di Server**
1. Buka: `https://yourdomain.com/form-document/oauth.php`
2. Login OAuth
3. Token baru tersimpan di server
4. **DONE!**

---

## ⚠️ **Warning yang Normal**

### **"Google hasn't verified this app"**
Ini **NORMAL** untuk app development/testing.

**Cara bypass:**
1. Klik **"Advanced"**
2. Klik **"Go to Form Document System (unsafe)"**
3. **Continue**

**Untuk production:** Anda bisa submit app untuk Google verification (opsional, proses ~1-2 minggu)

---

## ❓ **Troubleshooting**

### **Error: "redirect_uri_mismatch"**
**Solusi:**
- Cek Authorized redirect URIs di Google Cloud Console
- Harus exact match: `http://localhost/form-document/oauth.php`
- Tidak boleh ada trailing slash

### **Error: "Access blocked: This app's request is invalid"**
**Solusi:**
- Pastikan email Anda sudah ditambahkan di **Test users** (OAuth consent screen)

### **Form redirect terus ke OAuth**
**Solusi:**
- File `token.json` belum ter-generate atau terhapus
- Login ulang di `oauth.php`

### **Token expired error**
**Solusi:**
- Seharusnya auto-refresh, tapi jika error:
- Delete `token.json`
- Login ulang

---

## 🔒 **Keamanan & Privacy**

### **Apakah Data Aman?**
✅ **YA!**
- Dokumen tersimpan di **Google Drive Anda sendiri**
- Data tersimpan di **Google Sheets Anda sendiri**
- Token tersimpan di **server lokal** (tidak di cloud)
- User form **tidak bisa akses** Drive/Sheets Anda

### **Siapa yang Bisa Lihat Dokumen?**
- **Hanya Anda** (owner)
- User form **tidak bisa** lihat dokumen user lain
- Developer **tidak bisa** akses Drive/Sheets Anda (kecuali Anda share)

### **File `token.json` Aman?**
- ✅ Sudah ada di `.gitignore` (tidak ter-commit ke Git)
- ⚠️ **JANGAN share** file ini ke publik
- ⚠️ Simpan backup untuk re-deploy

---

## 📊 **Summary Setup**

### **Yang Anda Lakukan:**
1. ✅ Buat OAuth Client ID di Google Cloud (10 menit)
2. ✅ Setup folder Drive & spreadsheet Sheets (3 menit)
3. ✅ Login OAuth sekali → Token tersimpan (2 menit)
4. ✅ Test form → **BERHASIL!**

### **Yang User Lakukan:**
1. ✅ Buka form (tidak perlu login!)
2. ✅ Isi & submit
3. ✅ Selesai!

### **Yang Terjadi di Backend:**
```
User submit → Backend pakai token.json → Upload ke Drive Anda → Simpan ke Sheets Anda → Sukses!
```

---

## 🎉 **Selesai!**

Sistem sudah siap pakai!

**Next Steps:**
1. Test dengan beberapa user dummy
2. Validasi dokumen di Sheets (centang checkbox)
3. Deploy ke production (opsional)

---

## 📞 **Butuh Bantuan?**

Hubungi developer Anda jika:
- Error saat setup OAuth
- Form tidak bisa diakses
- Dokumen tidak masuk ke Drive/Sheets
- Ingin custom fitur

---

**Terima kasih sudah menggunakan Form Document System! 🙏**

Dibuat dengan ❤️ untuk memudahkan pengelolaan dokumen Anda.
