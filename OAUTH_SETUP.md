# 🔐 OAuth 2.0 Setup Guide

## ⚠️ Penting: Sistem Sudah Diupdate ke OAuth 2.0

Sistem sekarang menggunakan **OAuth 2.0** (bukan Service Account) agar dokumen bisa tersimpan di **Google Drive Anda sendiri** yang punya storage quota.

---

## 📋 Langkah Setup Credentials

### 1. Buka Google Cloud Console
https://console.cloud.google.com/

### 2. Pilih/Buat Project
Pilih project yang sudah ada atau buat baru

### 3. Setup OAuth Consent Screen

**Navigasi:** APIs & Services → OAuth consent screen

1. User Type: **External**
2. Klik **CREATE**
3. Isi form:
   - App name: `Form Document System`
   - User support email: pilih email Anda
   - App logo: (optional, bisa skip)
   - Application home page: `http://localhost/form-document/`
   - Authorized domains: (skip untuk localhost)
   - Developer contact: email Anda
4. **SAVE AND CONTINUE**
5. Scopes: **Skip** (kita set manual di code)
6. **SAVE AND CONTINUE**
7. Test users: **ADD USERS** → Tambahkan email Google Anda
8. **SAVE AND CONTINUE**
9. **BACK TO DASHBOARD**

### 4. Buat OAuth Client ID

**Navigasi:** APIs & Services → Credentials

1. Klik **+ CREATE CREDENTIALS**
2. Pilih **OAuth client ID**
3. Application type: **Web application**
4. Name: `Form Document Web`
5. Authorized JavaScript origins:
   - `http://localhost`
6. Authorized redirect URIs:
   - `http://localhost/form-document/oauth.php`
7. Klik **CREATE**
8. **Download JSON** (icon download di sebelah kanan)
9. Rename file menjadi **`credentials.json`**
10. Copy ke folder: `E:\Xampp\htdocs\form-document\credentials.json`

---

## 🔧 Format credentials.json

File `credentials.json` yang benar untuk OAuth 2.0 formatnya seperti ini:

```json
{
  "web": {
    "client_id": "xxx.apps.googleusercontent.com",
    "project_id": "your-project-id",
    "auth_uri": "https://accounts.google.com/o/oauth2/auth",
    "token_uri": "https://oauth2.googleapis.com/token",
    "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
    "client_secret": "GOCSPX-xxxxx",
    "redirect_uris": ["http://localhost/form-document/oauth.php"]
  }
}
```

**⚠️ Jangan share file ini ke siapapun!**

---

## 🚀 Cara Pakai

### Pertama Kali (Authorization)

1. Buka: `http://localhost/form-document/`
2. Akan auto-redirect ke `oauth.php`
3. Klik **"Login dengan Google"**
4. Pilih akun Google Anda
5. Klik **"Continue"** saat muncul warning "Google hasn't verified this app"
6. **Allow** semua permission:
   - ✅ See, edit, create, and delete all your Google Drive files
   - ✅ See, edit, create, and delete all your Google Sheets spreadsheets
7. Setelah sukses, akan redirect ke form

### File yang Ter-generate

Setelah authorization sukses, akan ada file `token.json` yang berisi:
```json
{
  "access_token": "ya29.xxx",
  "refresh_token": "1//xxx",
  "scope": "...",
  "token_type": "Bearer",
  "expires_in": 3599,
  "created": 1234567890
}
```

File ini akan **auto-refresh** jika token expired.

---

## 🔄 Logout / Re-authorize

Jika ingin logout atau re-authorize dengan akun lain:

1. Delete file `token.json`
2. Buka `http://localhost/form-document/`
3. Akan redirect ke OAuth lagi

---

## ⚠️ Warning yang Normal

Saat login, Anda akan melihat warning:

> **Google hasn't verified this app**
> This app hasn't been verified by Google yet. Only proceed if you know and trust the developer.

Ini **NORMAL** karena app masih dalam mode development/testing. Klik **Continue** untuk melanjutkan.

Untuk production, Anda bisa submit app untuk verification di Google Cloud Console.

---

## 🔐 Security Notes

1. **`credentials.json`** → Jangan commit ke Git (sudah ada di `.gitignore`)
2. **`token.json`** → Jangan commit ke Git (sudah ada di `.gitignore`)
3. **Test users** → Hanya email yang ditambahkan di OAuth consent screen yang bisa login
4. **Scopes** → App hanya minta akses Drive & Sheets (minimal permission)

---

## 🆚 Perbedaan Service Account vs OAuth 2.0

| Feature | Service Account | OAuth 2.0 |
|---------|----------------|-----------|
| Storage Quota | ❌ Tidak punya | ✅ Pakai quota user |
| Login Required | ❌ Tidak | ✅ Ya (sekali) |
| File Ownership | Service Account | User sendiri |
| Sharing Required | ✅ Ya | ❌ Tidak |
| Best For | Server-to-server | User applications |

**Untuk aplikasi form document ini, OAuth 2.0 lebih cocok!**

---

## ❓ Troubleshooting

### Error: "redirect_uri_mismatch"
**Solusi:** 
- Cek Authorized redirect URIs di Google Cloud Console
- Harus exact match: `http://localhost/form-document/oauth.php`
- Tidak boleh ada trailing slash

### Error: "invalid_client"
**Solusi:**
- File `credentials.json` corrupt atau format salah
- Download ulang dari Google Cloud Console

### Warning: "This app isn't verified"
**Solusi:**
- Ini normal untuk development
- Klik **"Continue"** untuk melanjutkan
- Tambahkan email ke Test Users di OAuth consent screen

### Token expired
**Solusi:**
- Token akan auto-refresh
- Jika gagal refresh, delete `token.json` dan login ulang

---

## 📞 Support

Jika masih error, cek:
1. ✅ APIs (Drive & Sheets) sudah enabled
2. ✅ OAuth consent screen sudah setup
3. ✅ Email sudah ditambahkan ke Test Users
4. ✅ Redirect URI sudah benar
5. ✅ File `credentials.json` format OAuth (bukan Service Account)

---

**Good luck! 🚀**
