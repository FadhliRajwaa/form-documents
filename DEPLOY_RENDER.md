# 🚀 Deploy ke Render - Panduan Lengkap

## 📋 Overview

Panduan ini akan membantu Anda deploy aplikasi **Form Document System** ke **Render** sehingga bisa diakses dari internet, sambil tetap bisa development di local.

---

## ⚠️ Penting: Dual Environment Setup

Setelah deploy, Anda akan punya **2 environment**:

| Environment | URL | Token | Penggunaan |
|------------|-----|-------|------------|
| **Local** | `http://localhost/form-document/` | `token.json` local | Development & testing |
| **Production** | `https://your-app.onrender.com/` | `token.json` Render | User production |

**⚠️ OAuth harus di-setup 2 KALI** (sekali untuk local, sekali untuk production)!

---

## 🎯 Langkah Deploy

### **Step 1: Persiapan Google Cloud OAuth**

#### **1a. Tambah Redirect URI Production**

1. Buka https://console.cloud.google.com/
2. **APIs & Services** → **Credentials**
3. Klik **OAuth Client ID** Anda
4. **Authorized redirect URIs:**
   - ✅ `http://localhost/form-document/oauth.php` (sudah ada)
   - ➕ **ADD URI:** `https://your-app.onrender.com/oauth.php`
   - ⚠️ **TUNGGU! Anda akan dapat URL Render di Step 3!**
5. **SAVE**

**Catatan:** Anda bisa tambah URI production setelah dapat URL dari Render (Step 3).

---

### **Step 2: Push ke GitHub**

Render deploy dari GitHub, jadi perlu push project dulu.

#### **2a. Init Git (jika belum)**

```bash
cd E:\Xampp\htdocs\form-document

# Init git
git init

# Add all files
git add .

# Commit
git commit -m "Initial commit - Form Document System"
```

#### **2b. Buat Repository di GitHub**

1. Buka https://github.com/
2. Klik **"New repository"**
3. **Repository name:** `form-document` (atau nama bebas)
4. **Public** atau **Private** (pilih sesuai kebutuhan)
5. **Jangan centang** "Add README" (sudah ada)
6. **Create repository**

#### **2c. Push ke GitHub**

```bash
# Add remote
git remote add origin https://github.com/USERNAME/form-document.git

# Push
git branch -M main
git push -u origin main
```

**⚠️ PENTING:** File `credentials.json` dan `token.json` **TIDAK** akan ter-push (sudah di `.gitignore`). Ini **AMAN**!

---

### **Step 3: Deploy di Render**

#### **3a. Buat Account Render**

1. Buka https://render.com/
2. **Sign Up** (bisa pakai GitHub)
3. Login

#### **3b. Connect GitHub**

1. Di Render Dashboard, klik **"New +"** → **Web Service**
2. Klik **"Connect GitHub"** atau **"Configure GitHub"**
3. Pilih repository `form-document`
4. Klik **"Connect"**

#### **3c. Configure Web Service**

Fill the form:

**Basic Settings:**
- **Name:** `form-document` (atau nama bebas)
- **Region:** Pilih terdekat (e.g., Singapore)
- **Branch:** `main`
- **Runtime:** `PHP`
- **Build Command:** `composer install --no-dev --optimize-autoloader`
- **Start Command:** `php -S 0.0.0.0:$PORT -t .`

**Environment Variables:**
Klik **"Add Environment Variable"** dan tambahkan:

| Key | Value |
|-----|-------|
| `APP_NAME` | `Form Document System` |
| `UPLOAD_MAX_SIZE` | `10485760` |
| `ALLOWED_EXTENSIONS` | `pdf,doc,docx,jpg,jpeg,png` |
| `GOOGLE_CREDENTIALS_PATH` | `credentials.json` |
| `GOOGLE_DRIVE_FOLDER_ID` | `<folder_id_anda>` |
| `GOOGLE_SPREADSHEET_ID` | `<spreadsheet_id_anda>` |

**Advanced:**
- **Plan:** Free
- **Auto-Deploy:** Yes

#### **3d. Deploy!**

1. Klik **"Create Web Service"**
2. Render akan mulai build & deploy
3. Tunggu ~3-5 menit
4. Setelah selesai, Anda akan dapat URL: `https://form-document-xxxx.onrender.com`

---

### **Step 4: Upload `credentials.json` ke Render**

File `credentials.json` tidak ter-push ke Git (karena sensitif). Harus upload manual ke Render.

#### **4a. Via Render Shell**

1. Di Render Dashboard, buka Web Service Anda
2. Klik tab **"Shell"** (kanan atas)
3. Tunggu shell terbuka
4. Copy isi file `credentials.json` local Anda
5. Di shell, jalankan:

```bash
cat > credentials.json << 'EOF'
# Paste isi credentials.json disini
# Tekan Enter
EOF
```

**Atau cara lebih mudah:**

#### **4b. Via Secret Files (Recommended)**

1. Di Render Dashboard, buka Web Service Anda
2. **Environment** → **Secret Files**
3. Klik **"Add Secret File"**
4. **Filename:** `credentials.json`
5. **Contents:** Paste isi file `credentials.json` Anda
6. **Save**
7. Render akan auto-restart

---

### **Step 5: Update OAuth Redirect URI**

Sekarang Anda sudah punya URL production!

1. Copy URL Render: `https://form-document-xxxx.onrender.com`
2. Buka https://console.cloud.google.com/
3. **APIs & Services** → **Credentials**
4. Edit **OAuth Client ID**
5. **Authorized redirect URIs:**
   - ✅ `http://localhost/form-document/oauth.php`
   - ➕ `https://form-document-xxxx.onrender.com/oauth.php`
6. **SAVE**

---

### **Step 6: Login OAuth di Production**

Sekarang setup token untuk production!

#### **6a. Buka OAuth Page Production**

```
https://form-document-xxxx.onrender.com/oauth.php
```

#### **6b. Login Google**

1. Klik **"Login dengan Google"**
2. Pilih akun Google Anda (yang sama dengan local)
3. **Allow** permissions
4. **Success!** Token production tersimpan di Render

---

### **Step 7: Test Production**

```
https://form-document-xxxx.onrender.com/
```

1. Form muncul (tidak redirect OAuth lagi)
2. Isi form & upload dokumen
3. Submit
4. Cek Drive & Sheets → **File masuk!** ✅

---

## 🔄 **Workflow Development**

### **Local Development:**

```bash
# Akses local
http://localhost/form-document/

# Test perubahan
# Edit code → Refresh browser → Test

# Commit & push
git add .
git commit -m "Update feature"
git push
```

### **Auto-Deploy:**

Setelah push, Render akan **otomatis deploy** (~2-3 menit).

### **Manual Deploy:**

Di Render Dashboard → **Manual Deploy** → **Deploy latest commit**

---

## 🔐 **Token Management**

### **Local Token:**
- **Lokasi:** `E:\Xampp\htdocs\form-document\token.json`
- **Untuk:** Local development
- **Setup:** Login di `http://localhost/form-document/oauth.php`

### **Production Token:**
- **Lokasi:** `/opt/render/project/src/token.json` (di Render server)
- **Untuk:** Production
- **Setup:** Login di `https://your-app.onrender.com/oauth.php`

**⚠️ PENTING:** Token local ≠ Token production! Harus setup 2 kali.

---

## 🗂️ **File Structure di Render**

```
/opt/render/project/src/
├── assets/
├── src/
├── vendor/
├── .env (auto-generated dari environment variables)
├── credentials.json (from Secret Files)
├── token.json (generated after OAuth login)
├── composer.json
├── config.php
├── index.php
├── oauth.php
└── process.php
```

---

## 📝 **Environment Variables**

File `.env` local **TIDAK** ter-upload. Render pakai **Environment Variables** dari dashboard.

**Set di Render Dashboard:**
```
Environment → Environment Variables → Add
```

**Variables yang wajib:**
- `GOOGLE_CREDENTIALS_PATH=credentials.json`
- `GOOGLE_DRIVE_FOLDER_ID=<your_folder_id>`
- `GOOGLE_SPREADSHEET_ID=<your_spreadsheet_id>`

---

## 🔄 **Update Credentials**

Jika perlu update `credentials.json` di production:

1. Render Dashboard → **Environment** → **Secret Files**
2. Edit `credentials.json`
3. Save
4. Auto-restart

---

## ⚠️ **Troubleshooting**

### **Error: "redirect_uri_mismatch"**

**Penyebab:** Redirect URI belum ditambahkan di Google Cloud Console

**Solusi:**
1. Cek URL yang error di message
2. Tambahkan URL tersebut ke **Authorized redirect URIs**
3. Format: `https://your-app.onrender.com/oauth.php`

---

### **Error: "File credentials.json not found"**

**Penyebab:** credentials.json belum di-upload ke Render

**Solusi:**
- Upload via **Secret Files** (Step 4b)

---

### **Form redirect terus ke OAuth**

**Penyebab:** Token belum ter-generate di production

**Solusi:**
- Login di `https://your-app.onrender.com/oauth.php`

---

### **Deploy gagal / Build error**

**Solusi:**
1. Cek **Logs** di Render Dashboard
2. Pastikan `composer.json` valid
3. Pastikan semua dependency ter-install

---

### **File upload timeout**

**Penyebab:** Free tier Render spin down setelah 15 menit idle

**Solusi:**
- Upgrade ke paid plan ($7/month untuk always-on)
- Atau user tunggu ~30 detik untuk cold start

---

## 💰 **Render Pricing**

| Plan | Price | Features |
|------|-------|----------|
| **Free** | $0 | 750 hours/month, spin down after idle, 512MB RAM |
| **Starter** | $7/month | Always-on, 512MB RAM |
| **Standard** | $25/month | 2GB RAM, better performance |

**Untuk production dengan traffic rendah-medium, Free plan cukup!**

---

## 🌐 **Custom Domain (Optional)**

Jika ingin pakai domain sendiri (e.g., `forms.yourdomain.com`):

1. Beli domain (Namecheap, GoDaddy, dll)
2. Di Render Dashboard → **Settings** → **Custom Domains**
3. Add domain
4. Update DNS records (CNAME/A record)
5. **PENTING:** Update OAuth redirect URI di Google Cloud:
   - `https://forms.yourdomain.com/oauth.php`

---

## 🔒 **Security Notes**

### **Yang AMAN:**
- ✅ `credentials.json` di Secret Files (encrypted)
- ✅ `token.json` di server (tidak ter-commit)
- ✅ Environment variables (encrypted)
- ✅ HTTPS default di Render

### **Yang HARUS DIHINDARI:**
- ❌ Commit `credentials.json` ke Git
- ❌ Commit `token.json` ke Git
- ❌ Share Secret Files ke publik
- ❌ Hardcode sensitive data di code

---

## ✅ **Checklist Deployment**

Sebelum deploy, pastikan:

- [ ] `credentials.json` siap (OAuth Client ID)
- [ ] Folder Drive & Spreadsheet sudah dibuat
- [ ] Git repository sudah dibuat
- [ ] Code sudah di-push ke GitHub
- [ ] Environment variables sudah di-set di Render
- [ ] `credentials.json` sudah di-upload ke Render (Secret Files)
- [ ] OAuth redirect URI production sudah ditambahkan di Google Cloud
- [ ] Login OAuth di production
- [ ] Test form di production

---

## 🎉 **Selesai!**

Aplikasi Anda sudah live di internet!

**URLs:**
- **Local:** `http://localhost/form-document/`
- **Production:** `https://your-app.onrender.com/`

**Next Steps:**
1. Share URL production ke client
2. Monitor logs di Render Dashboard
3. Setup custom domain (optional)
4. Upgrade plan jika perlu always-on

---

## 📞 **Support**

**Render Documentation:**
- https://render.com/docs

**Render Community:**
- https://community.render.com/

**OAuth Issues:**
- Check Google Cloud Console logs
- Verify redirect URIs

---

**Happy Deploying! 🚀**

Dibuat dengan ❤️ untuk mempermudah deployment Anda.
