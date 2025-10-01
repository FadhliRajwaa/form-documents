# 📘 Panduan Setup untuk Client

## 👋 Halo!

Sistem form document ini memerlukan beberapa setup dari pihak Anda. Jangan khawatir, saya akan pandu step-by-step dengan bahasa yang mudah dipahami.

---

## 🎯 Yang Anda Perlukan

1. **Akun Google** (gratis)
2. **Google Drive** untuk simpan dokumen
3. **Google Sheets** untuk simpan data
4. **10-15 menit** untuk setup

---

## 📋 Langkah Setup

### **Step 1: Buat Project di Google Cloud** (5 menit)

1. Buka browser, pergi ke: https://console.cloud.google.com/
2. Login dengan akun Google Anda
3. Klik **"Select a project"** (atas kiri) → **"NEW PROJECT"**
4. Isi:
   - **Project name:** `Form Document System` (atau nama bebas)
   - **Organization:** Biarkan default
5. Klik **CREATE**
6. Tunggu beberapa detik sampai project selesai dibuat

---

### **Step 2: Enable API yang Diperlukan** (3 menit)

Sistem memerlukan 2 API untuk bisa bekerja:

#### **2a. Enable Google Drive API**

1. Di Google Cloud Console, klik menu ☰ (kiri atas)
2. Pilih **"APIs & Services"** → **"Library"**
3. Di kotak pencarian, ketik: **"Google Drive API"**
4. Klik hasil pertama
5. Klik tombol biru **"ENABLE"**
6. Tunggu sampai selesai

#### **2b. Enable Google Sheets API**

1. Klik **"Library"** lagi (di menu kiri)
2. Di kotak pencarian, ketik: **"Google Sheets API"**
3. Klik hasil pertama
4. Klik tombol biru **"ENABLE"**
5. Tunggu sampai selesai

---

### **Step 3: Buat Service Account** (5 menit)

Service Account adalah "robot" yang akan menjalankan sistem untuk Anda.

1. Di menu kiri, klik **"Credentials"**
2. Klik **"+ CREATE CREDENTIALS"** (atas)
3. Pilih **"Service Account"**
4. Isi form:
   - **Service account name:** `form-document-robot`
   - **Service account ID:** (akan otomatis terisi)
   - **Description:** `Robot untuk upload dokumen` (opsional)
5. Klik **"CREATE AND CONTINUE"**
6. Di bagian **"Grant this service account access to project"**:
   - **Role:** Pilih **"Editor"**
7. Klik **"CONTINUE"**
8. Klik **"DONE"**

---

### **Step 4: Download Credentials (Kunci Akses)** (2 menit)

1. Anda akan melihat Service Account yang baru dibuat di daftar
2. Klik **nama service account** tersebut (link biru)
3. Pergi ke tab **"KEYS"** (di menu atas)
4. Klik **"ADD KEY"** → **"Create new key"**
5. Pilih **"JSON"**
6. Klik **"CREATE"**
7. File JSON akan otomatis ter-download ke komputer Anda
8. **PENTING:** 
   - Rename file menjadi: **`credentials.json`**
   - **KIRIM FILE INI KE DEVELOPER ANDA**
   - **JANGAN SHARE FILE INI KE PUBLIK!**

---

### **Step 5: Setup Google Drive** (3 menit)

#### **5a. Buat Folder untuk Dokumen**

1. Buka https://drive.google.com/
2. Klik **"+ New"** → **"New folder"**
3. Nama folder: **"Form Documents"** (atau nama bebas)
4. Klik **"CREATE"**

#### **5b. Dapatkan Folder ID**

1. Buka folder yang baru dibuat
2. Lihat URL di browser, contoh:
   ```
   https://drive.google.com/drive/folders/1m2TH9NpJwMXYdbmUDk1-KJr44nQeVO6x
   ```
3. Copy bagian setelah `folders/`:
   ```
   1m2TH9NpJwMXYdbmUDk1-KJr44nQeVO6x
   ```
   ☝️ **INI ADALAH FOLDER ID ANDA**
4. **SIMPAN ID INI, KASIH KE DEVELOPER**

#### **5c. Share Folder ke Service Account**

1. Masih di folder tersebut, klik tombol **"Share"** (kanan atas)
2. Di kotak **"Add people, groups, and calendar events"**, paste **EMAIL SERVICE ACCOUNT**
   - Email ada di file `credentials.json` yang tadi didownload
   - Cari baris: `"client_email"`
   - Format: `form-document-robot@project-id.iam.gserviceaccount.com`
3. Pastikan akses: **"Editor"**
4. **UNCHECK** ✅ → ☐ kotak **"Notify people"** (jangan kirim notif)
5. Klik **"Share"** atau **"Send"**

**✅ Selesai! Folder sudah bisa diakses sistem**

---

### **Step 6: Setup Google Sheets** (3 menit)

#### **6a. Buat Spreadsheet**

1. Buka https://sheets.google.com/
2. Klik **"+ Blank"** (buat spreadsheet kosong)
3. Klik judul spreadsheet (atas kiri, default: "Untitled spreadsheet")
4. Rename menjadi: **"Data Dokumen"**

#### **6b. Rename Sheet**

1. Di bawah (tab sheet), ada tulisan "Sheet1"
2. Klik kanan pada tab "Sheet1"
3. Pilih **"Rename"**
4. Ketik: **`Data Dokumen`** (HARUS PERSIS, huruf besar/kecil sama!)
5. Tekan **Enter**

#### **6c. Dapatkan Spreadsheet ID**

1. Lihat URL di browser, contoh:
   ```
   https://docs.google.com/spreadsheets/d/1I5wt481xCNgh2jEy2AfzCgjYsJqgjpWHQrVORwuYsQg/edit
   ```
2. Copy bagian setelah `/d/` sampai sebelum `/edit`:
   ```
   1I5wt481xCNgh2jEy2AfzCgjYsJqgjpWHQrVORwuYsQg
   ```
   ☝️ **INI ADALAH SPREADSHEET ID ANDA**
3. **SIMPAN ID INI, KASIH KE DEVELOPER**

#### **6d. Share Spreadsheet ke Service Account**

1. Klik tombol **"Share"** (kanan atas)
2. Di kotak "Add people...", paste **EMAIL SERVICE ACCOUNT** yang sama
3. Pastikan akses: **"Editor"**
4. **UNCHECK** ✅ → ☐ kotak **"Notify people"**
5. Klik **"Share"** atau **"Send"**

**✅ Selesai! Spreadsheet sudah bisa diakses sistem**

---

## 📝 Checklist - Kirim Ini ke Developer

Setelah selesai semua step di atas, kirim informasi ini ke developer Anda:

```
✅ File credentials.json (yang sudah di-rename)
✅ Google Drive Folder ID: 1m2TH9NpJwMXYdbmUDk1-KJr44nQeVO6x
✅ Google Spreadsheet ID: 1I5wt481xCNgh2jEy2AfzCgjYsJqgjpWHQrVORwuYsQg
```

Ganti ID di atas dengan ID Anda yang sebenarnya!

---

## 🔒 Keamanan & Privacy

### **Apakah data saya aman?**

✅ **YA!** Karena:
1. Dokumen tersimpan di **Google Drive Anda sendiri**
2. Data tersimpan di **Google Sheets Anda sendiri**
3. Service Account hanya punya akses ke **folder & spreadsheet yang Anda share**
4. Anda bisa **cabut akses** kapan saja

### **Siapa yang bisa lihat dokumen?**

- **Hanya Anda** dan **Service Account** (robot sistem)
- Service Account **tidak bisa login**, hanya bisa upload otomatis
- Orang lain **TIDAK BISA** akses kecuali Anda share manual

### **Bagaimana cara cabut akses?**

Jika suatu saat tidak pakai sistem lagi:

1. Buka folder "Form Documents" di Google Drive
2. Klik **"Share"** → Lihat **"People with access"**
3. Klik **X** di samping email service account → **"Remove"**
4. Lakukan hal yang sama di Google Sheets

---

## ❓ FAQ (Pertanyaan Sering Ditanya)

### **Q: Apakah saya harus bayar untuk Google Cloud?**
**A:** Tidak! Untuk penggunaan normal, semuanya gratis. Google Cloud memberi free quota yang lebih dari cukup.

### **Q: Apakah developer bisa akses Drive/Sheets saya?**
**A:** Tidak! Developer hanya punya file `credentials.json` untuk sistem upload otomatis. Developer tidak bisa login ke akun Google Anda.

### **Q: File credentials.json aman untuk dikirim?**
**A:** Aman dikirim ke developer terpercaya. File ini hanya memberi akses ke **folder & spreadsheet yang Anda share**, bukan ke seluruh Drive Anda.

### **Q: Bagaimana jika lupa Folder ID atau Spreadsheet ID?**
**A:** Buka kembali folder/spreadsheet di browser, copy dari URL.

### **Q: Kenapa sheet harus nama "Data Dokumen" (exact)?**
**A:** Sistem mencari sheet dengan nama persis tersebut. Jika beda huruf besar/kecil, sistem tidak bisa menemukan.

---

## 🎉 Selesai!

Setelah setup di atas selesai dan kirim data ke developer, sistem siap dipakai!

**Cara pakai:**
1. Buka form di browser (developer akan kasih link)
2. Isi data & upload dokumen
3. Dokumen otomatis masuk ke folder Drive Anda (dengan subfolder sesuai nama)
4. Data otomatis masuk ke Sheets Anda
5. Anda bisa centang checkbox "Status Validasi" di Sheets untuk validasi dokumen

---

## 📞 Butuh Bantuan?

Jika ada yang tidak jelas atau error saat setup, hubungi developer Anda dengan informasi:
- Step berapa yang error
- Screenshot error (jika ada)
- Browser yang dipakai

---

**Terima kasih sudah setup! 🙏**

Sistem ini dibuat untuk memudahkan pengelolaan dokumen Anda.
