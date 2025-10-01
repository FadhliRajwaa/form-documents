# 🔧 Fix: credentials.json di Render

## ⚠️ Masalah

Error: `file "/var/www/html/credentials.json" does not exist`

**Penyebab:** Secret Files di Render **TIDAK** otomatis ter-mount ke Docker container.

---

## ✅ Solusi

Ada 2 cara:

---

## **Cara 1: Via Environment Variable (RECOMMENDED)** ⭐

Lebih mudah dan lebih aman!

### **Step 1: Copy Isi credentials.json**

1. Buka file `credentials.json` di local
2. Copy **SELURUH ISI** (Ctrl+A, Ctrl+C)
3. Compress ke single line (hapus newline):
   - Atau pakai online tool: https://www.text-utils.com/json-formatter/
   - Pilih "Minify" atau "Compact"

### **Step 2: Add Environment Variable di Render**

1. Render Dashboard → **form-documents**
2. **Environment** (menu kiri)
3. **Add Environment Variable**
4. **Key:** `GOOGLE_CREDENTIALS_JSON`
5. **Value:** Paste isi credentials.json (compressed, single line)
6. **Save Changes**

Container akan auto-restart dan credentials.json akan ter-generate otomatis!

---

## **Cara 2: Build Credentials ke Image** 

⚠️ **TIDAK RECOMMENDED** untuk production (security issue), tapi bisa untuk testing.

### **Option A: Commit credentials.json (Temporary)**

```bash
# HANYA UNTUK TESTING!
# Remove from .gitignore temporarily
nano .gitignore
# Comment out: # credentials.json

# Commit
git add credentials.json
git commit -m "Temporary: add credentials for testing"
git push

# IMPORTANT: Setelah deploy berhasil, remove dari Git:
git rm credentials.json
echo "credentials.json" >> .gitignore
git commit -m "Remove credentials from Git"
git push
```

⚠️ **BAHAYA:** Credentials akan ada di Git history! Jangan pakai untuk production!

---

## 🎯 Recommended: Cara 1 (Environment Variable)

### **Complete Steps:**

1. **Copy credentials.json content:**
   ```json
   {"web":{"client_id":"...","project_id":"...","auth_uri":"...","token_uri":"...","auth_provider_x509_cert_url":"...","client_secret":"...","redirect_uris":["..."]}}
   ```

2. **Minify JSON** (remove whitespace & newlines):
   - Online: https://codebeautify.org/jsonminifier
   - Hasil: Single line JSON

3. **Render Dashboard:**
   - Environment → Add Environment Variable
   - Key: `GOOGLE_CREDENTIALS_JSON`
   - Value: [paste minified JSON]
   - Save

4. **Wait auto-restart** (~30 seconds)

5. **Test:** `https://form-documents.onrender.com/oauth.php`

---

## 📋 Environment Variables Lengkap

Pastikan semua ini sudah di-set di Render:

```
APP_NAME=Form Document System
UPLOAD_MAX_SIZE=10485760
ALLOWED_EXTENSIONS=pdf,doc,docx,jpg,jpeg,png
GOOGLE_CREDENTIALS_PATH=credentials.json
GOOGLE_DRIVE_FOLDER_ID=your_folder_id
GOOGLE_SPREADSHEET_ID=your_spreadsheet_id
GOOGLE_CREDENTIALS_JSON={"web":{...}} ← TAMBAHKAN INI!
```

---

## 🔍 Verify credentials.json Created

Via Render Shell:

```bash
# Open Shell di Render Dashboard
ls -la credentials.json
cat credentials.json
```

Harusnya muncul file dengan isi JSON yang benar!

---

## ✅ After Fix

1. credentials.json ter-generate otomatis saat container start
2. OAuth page bisa diakses: `https://form-documents.onrender.com/oauth.php`
3. Login Google → Generate token
4. Form ready to use!

---

## 🔒 Security Notes

### **Via Environment Variable:**
- ✅ Tidak ada di Git history
- ✅ Encrypted di Render
- ✅ Easy to rotate/update
- ✅ Best practice

### **Via Git Commit:**
- ❌ Ada di Git history (permanent!)
- ❌ Security risk
- ❌ Hard to rotate
- ❌ NOT recommended

---

**Always use Environment Variable for production!** 🔐
