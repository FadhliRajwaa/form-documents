# 🐳 Deploy ke Render dengan Docker - Panduan Lengkap

## 📋 Overview

Panduan ini untuk deploy aplikasi **Form Document System** ke **Render** menggunakan **Docker**.

---

## 🎯 Langkah Deploy

### **Step 1: Persiapan Files**

File yang sudah saya buatkan:
- ✅ `Dockerfile` - Docker configuration untuk PHP
- ✅ `.dockerignore` - File yang tidak perlu di-copy ke container

**Cek files ini sudah ada di project Anda!**

---

### **Step 2: Push ke GitHub**

```bash
cd E:\Xampp\htdocs\form-document

# Add new files
git add Dockerfile .dockerignore
git commit -m "Add Docker support"
git push

# Atau jika belum init:
git init
git add .
git commit -m "Initial commit with Docker"
git remote add origin https://github.com/USERNAME/form-document.git
git branch -M main
git push -u origin main
```

---

### **Step 3: Setup OAuth Redirect URI**

Sebelum deploy, siapkan OAuth:

1. Buka https://console.cloud.google.com/
2. **APIs & Services** → **Credentials**
3. Klik **OAuth Client ID** Anda
4. **Authorized redirect URIs** → Tambahkan:
   - ✅ `http://localhost/form-document/oauth.php` (sudah ada)
   - ➕ `https://your-app-name.onrender.com/oauth.php` (akan dapat URL di Step 5)
5. **SAVE**

**Note:** Anda bisa tambah URI production setelah dapat URL dari Render.

---

### **Step 4: Deploy di Render**

#### **4a. Buat Web Service**

1. Buka https://render.com/ → Login
2. Klik **"New +"** → **"Web Service"**
3. Connect GitHub repository `form-document`

#### **4b. Configure Service**

Fill the form:

**Basic Settings:**
- **Name:** `form-document` (atau nama bebas)
- **Region:** Singapore (atau terdekat)
- **Branch:** `main`
- **Root Directory:** (kosongkan)

**Language:** Pilih **Docker** 🐳

**Build & Deploy:**
- **Dockerfile Path:** `Dockerfile` (default, biarkan)
- Render akan otomatis detect Dockerfile Anda

**Instance Type:**
- **Plan:** Free

---

### **Step 5: Add Environment Variables**

Di bagian **Environment Variables**, klik **"Add Environment Variable"**:

| Key | Value | Notes |
|-----|-------|-------|
| `APP_NAME` | `Form Document System` | Nama aplikasi |
| `UPLOAD_MAX_SIZE` | `10485760` | 10MB dalam bytes |
| `ALLOWED_EXTENSIONS` | `pdf,doc,docx,jpg,jpeg,png` | Ekstensi file yang diizinkan |
| `GOOGLE_CREDENTIALS_PATH` | `credentials.json` | Path ke credentials |
| `GOOGLE_DRIVE_FOLDER_ID` | `<your_folder_id>` | ID folder Drive Anda |
| `GOOGLE_SPREADSHEET_ID` | `<your_spreadsheet_id>` | ID spreadsheet Anda |

**Cara dapat ID:**
- **Folder ID:** Dari URL Drive → `https://drive.google.com/drive/folders/FOLDER_ID_INI`
- **Spreadsheet ID:** Dari URL Sheets → `https://docs.google.com/spreadsheets/d/SPREADSHEET_ID_INI/edit`

---

### **Step 6: Create Web Service**

1. Klik **"Create Web Service"**
2. Render akan:
   - Clone repository
   - Build Docker image
   - Deploy container
3. Tunggu ~3-5 menit
4. Setelah selesai, Anda dapat URL: `https://form-document-xxxx.onrender.com`

---

### **Step 7: Upload credentials.json**

File `credentials.json` tidak ter-push ke GitHub (karena di `.gitignore`).

**Upload via Secret Files:**

1. Di Render Dashboard → Pilih Web Service Anda
2. Klik **"Environment"** (menu kiri)
3. Scroll ke bawah → Section **"Secret Files"**
4. Klik **"Add Secret File"**
5. **Form:**
   - **Filename:** `credentials.json`
   - **Contents:** Paste isi file `credentials.json` Anda
6. Klik **"Save"**
7. Render akan auto-restart container

---

### **Step 8: Update OAuth Redirect URI (Jika Belum)**

Sekarang Anda punya URL production!

1. Copy URL: `https://form-document-xxxx.onrender.com`
2. Buka Google Cloud Console → **Credentials**
3. Edit **OAuth Client ID**
4. **Authorized redirect URIs** → Tambahkan:
   - `https://form-document-xxxx.onrender.com/oauth.php`
5. **SAVE**

---

### **Step 9: Login OAuth Production**

Setup token untuk production:

1. Buka: `https://form-document-xxxx.onrender.com/oauth.php`
2. Klik **"Login dengan Google"**
3. Pilih akun Google Anda
4. Klik **"Advanced"** → **"Go to Form Document System (unsafe)"** (normal untuk unverified app)
5. **Allow** semua permissions
6. Success! Token tersimpan di container

---

### **Step 10: Test Production**

1. Buka: `https://form-document-xxxx.onrender.com/`
2. Form muncul (tidak redirect OAuth)
3. Isi form & upload dokumen
4. Submit
5. Cek Drive & Sheets → **File masuk!** ✅

---

## 🔍 **Troubleshooting**

### **Error: Build failed**

**Check Logs:**
- Render Dashboard → **Logs** tab
- Cari error message

**Common issues:**
- `composer.json` syntax error → Fix & push
- PHP version mismatch → Update Dockerfile jika perlu

---

### **Error: "credentials.json not found"**

**Solusi:**
- Upload via Secret Files (Step 7)
- Pastikan filename exact: `credentials.json`

---

### **Error: "redirect_uri_mismatch"**

**Solusi:**
- Check OAuth redirect URI di Google Cloud
- Harus exact match: `https://your-app.onrender.com/oauth.php`
- Tidak boleh ada trailing slash atau typo

---

### **Container terus restart**

**Check Logs:**
- Lihat error di Logs
- Biasanya karena PHP error atau missing credentials

**Debug:**
1. Render Dashboard → **Shell**
2. Check file exists: `ls -la credentials.json`
3. Check PHP errors: `cat /var/log/apache2/error.log`

---

### **File upload tidak work**

**Solusi:**
- Check folder permissions di Dockerfile (sudah di-set)
- Check `UPLOAD_MAX_SIZE` environment variable
- Check Render free tier limits (512MB RAM)

---

## 🔄 **Update & Redeploy**

### **Update Code:**

```bash
# Edit code di local
git add .
git commit -m "Update feature"
git push
```

Render akan **auto-deploy** (~3-5 menit).

### **Update Environment Variables:**

Render Dashboard → **Environment** → Edit variable → **Save** → Auto-restart

### **Update credentials.json:**

Render Dashboard → **Environment** → **Secret Files** → Edit → **Save** → Auto-restart

---

## 🐳 **Docker Commands (Local Testing)**

Test Docker image di local sebelum deploy:

```bash
# Build image
docker build -t form-document .

# Run container
docker run -p 8080:80 \
  -e GOOGLE_CREDENTIALS_PATH=credentials.json \
  -e GOOGLE_DRIVE_FOLDER_ID=your_id \
  -e GOOGLE_SPREADSHEET_ID=your_id \
  form-document

# Test
http://localhost:8080/
```

---

## 📊 **Dockerfile Explained**

```dockerfile
# Base image: PHP 8.2 with Apache
FROM php:8.2-apache

# Install dependencies (zip untuk Google API)
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev \
    && docker-php-ext-install zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Install PHP dependencies
COPY composer.json composer.lock* ./
RUN composer install --no-dev --optimize-autoloader

# Copy application
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port & start Apache
EXPOSE 80
CMD ["apache2-foreground"]
```

---

## ⚡ **Performance Tips**

### **Free Tier Limitations:**
- **RAM:** 512MB
- **Spin down:** 15 menit idle
- **Cold start:** ~30-60 detik

### **Optimization:**
- ✅ Dockerfile sudah optimized
- ✅ Composer autoload optimized
- ✅ No dev dependencies

### **Upgrade:**
- **$7/month** → Always-on, no spin down
- **$25/month** → 2GB RAM, better performance

---

## 🔒 **Security Checklist**

- [x] `credentials.json` tidak di Git (`.gitignore`)
- [x] `credentials.json` via Secret Files (encrypted)
- [x] `token.json` tidak di Git (`.gitignore`)
- [x] Environment variables (tidak hardcode di code)
- [x] HTTPS default di Render
- [x] OAuth scopes minimal (Drive & Sheets only)

---

## ✅ **Deployment Checklist**

- [ ] Dockerfile & .dockerignore sudah dibuat
- [ ] Code di-push ke GitHub
- [ ] OAuth redirect URI ditambahkan di Google Cloud
- [ ] Web Service dibuat di Render (Docker)
- [ ] Environment variables ditambahkan
- [ ] credentials.json di-upload via Secret Files
- [ ] OAuth login di production
- [ ] Test upload dokumen
- [ ] ✅ **SELESAI!**

---

## 🎉 **Done!**

Aplikasi Anda sekarang live di:
- **Production:** `https://your-app.onrender.com/`
- **Local:** `http://localhost/form-document/`

**Both environments work independently!**

---

## 📞 **Support**

**Render Docker Docs:**
- https://render.com/docs/docker

**PHP Official Image:**
- https://hub.docker.com/_/php

**Troubleshooting:**
- Check Logs di Render Dashboard
- Check Shell untuk debug

---

**Happy Deploying dengan Docker! 🐳🚀**
