# 🗄️ Setup PostgreSQL untuk Token Storage

## 📋 Overview

Token OAuth sekarang disimpan di **PostgreSQL database** untuk persistence yang lebih baik.

### **Keuntungan:**
- ✅ Token **tidak hilang** saat container restart
- ✅ Auto-refresh token jika expired
- ✅ **Tidak perlu login ulang** setelah idle
- ✅ Production-ready

---

## 🚀 Setup di Render

### **Step 1: Create PostgreSQL Database**

1. **Render Dashboard** → **New** → **PostgreSQL**
2. Fill form:
   - **Name:** `form-documents-db` (atau nama bebas)
   - **Database:** (auto-generated, atau custom)
   - **User:** (auto-generated, atau custom)
   - **Region:** ✅ **Singapore** (untuk latency rendah ke Indonesia)
   - **PostgreSQL Version:** **17** (latest)
   - **Plan:** ✅ **Free** (1GB storage)
3. **Create Database**
4. Tunggu ~1-2 menit (status: Available)

---

### **Step 2: Link Database ke Web Service**

1. **Render Dashboard** → **form-documents** (web service Anda)
2. **Environment** (menu kiri)
3. Scroll ke **Environment Groups**
4. Database akan auto-tersedia dengan variable:
   - `DATABASE_URL` - Full connection string ✅ (ini yang kita pakai!)
   - `DATABASE_HOST`
   - `DATABASE_PORT`
   - `DATABASE_USER`
   - `DATABASE_PASSWORD`
   - `DATABASE_NAME`

**Tidak perlu add manual!** Render auto-link database ke web service di region yang sama.

---

### **Step 3: Deploy Updated Code**

Code sudah di-update untuk pakai database. Push ke Git:

```bash
git add .
git commit -m "Add PostgreSQL token storage for persistent OAuth"
git push
```

Render akan auto-deploy (~3-5 menit).

---

### **Step 4: Re-authorize OAuth**

Setelah deploy selesai:

1. Buka: `https://form-documents.onrender.com/oauth.php`
2. Login dengan Google
3. Token akan tersimpan di **database PostgreSQL** ✅
4. Done!

---

## 🔍 Verify Setup

### **Check Database Connection**

Buka: `https://form-documents.onrender.com/check-config.php`

Harusnya muncul:
- ✅ Database: Connected
- ✅ Token: Available

### **Check Token di Database** (Optional)

Via Render Database Shell:

```sql
SELECT * FROM oauth_tokens;
```

Harusnya ada 1 row dengan `token_key = 'google_oauth_token'`.

---

## 📊 Database Schema

```sql
CREATE TABLE oauth_tokens (
    id SERIAL PRIMARY KEY,
    token_key VARCHAR(255) UNIQUE NOT NULL,
    token_data TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Table ini **auto-created** oleh `TokenStorage` class saat pertama kali diakses.

---

## 🔧 How It Works

### **OAuth Flow:**

```
User → oauth.php → Google OAuth → Callback
    ↓
TokenStorage::saveToken()
    ↓
PostgreSQL Database (PERSISTENT!)
```

### **Upload Flow:**

```
User submit form → upload.php
    ↓
GoogleDriveService → TokenStorage::loadToken()
    ↓
PostgreSQL Database
    ↓
If token expired → Auto-refresh
    ↓
Upload to Drive ✅
```

### **Key Features:**

1. **Auto Token Refresh:**
   - Jika token expired, otomatis refresh
   - Save token baru ke database
   - User tidak perlu login ulang

2. **Persistent Storage:**
   - Token tersimpan di database (tidak hilang saat restart)
   - Container restart → Token tetap ada ✅

3. **Production Ready:**
   - Handle connection errors gracefully
   - Auto-reconnect jika database down
   - Transaction-safe

---

## ⚠️ Troubleshooting

### **Error: DATABASE_URL not set**

**Penyebab:** Database belum linked ke web service.

**Fix:**
1. Render Dashboard → **form-documents**
2. **Environment** → Check if `DATABASE_URL` exists
3. Jika tidak ada:
   - Ensure database dan web service di **region yang sama** (Singapore)
   - Manual add: Copy internal database URL dari database dashboard

---

### **Error: Connection failed**

**Penyebab:** Database belum ready atau connection string salah.

**Fix:**
1. Check database status: **Available**
2. Verify `DATABASE_URL` format:
   ```
   postgresql://user:password@host:port/dbname
   ```
3. Ensure web service bisa access database (same region)

---

### **Error: No token found**

**Penyebab:** Belum login OAuth setelah setup database.

**Fix:**
1. Buka: `https://form-documents.onrender.com/oauth.php`
2. Login dengan Google
3. Token akan tersimpan di database

---

## 💰 Cost Comparison

| Tier | Price | Token Persistence | Spin Down | Total Monthly |
|------|-------|-------------------|-----------|---------------|
| **Free Web + Free DB** | $0 | ✅ YES | ⚠️ 15 min idle | $0 |
| **Starter Web + Free DB** | $7 | ✅ YES | ❌ Always-on | $7/mo |
| **Free Web only** | $0 | ❌ NO | ⚠️ 15 min idle | $0 |

**Recommended:** Free Web + Free DB = **$0/month** dengan persistent tokens! 🎉

---

## ✅ Benefits Recap

| Feature | Before (File-based) | After (PostgreSQL) |
|---------|--------------------|--------------------|
| **Token persistence** | ❌ Lost on restart | ✅ Persistent |
| **Re-login after idle** | ⚠️ Required | ❌ Not needed |
| **Auto token refresh** | ⚠️ Limited | ✅ Full support |
| **Production ready** | ⚠️ Testing only | ✅ Yes |
| **Cost** | Free | Free |

---

## 🎯 Summary

Setup PostgreSQL = **5 menit**, benefit = **HUGE!**

No more re-login setiap restart! 🎉

Token storage sekarang **production-ready** dan fully **persistent**! 💯
