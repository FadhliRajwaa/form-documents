# 🔍 Debug credentials.json di Render

## Step 1: Buka Shell

1. Render Dashboard → **form-documents**
2. Klik tab **"Shell"** (atas kanan)
3. Tunggu shell terbuka

## Step 2: Check credentials.json

Run command:

```bash
cat /var/www/html/credentials.json
```

## Step 3: Verify Redirect URIs

Harusnya output seperti ini:

```json
{"web":{"client_id":"571808635-qlqbbtladojarm17v0pbiinrj5t6j04g.apps.googleusercontent.com","project_id":"form-473812","auth_uri":"https://accounts.google.com/o/oauth2/auth","token_uri":"https://oauth2.googleapis.com/token","auth_provider_x509_cert_url":"https://www.googleapis.com/oauth2/v1/certs","client_secret":"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","redirect_uris":["http://localhost/form-document/oauth.php","https://form-documents.onrender.com/oauth.php"],"javascript_origins":["http://localhost","https://form-documents.onrender.com"]}}
```

**Penting:** Cari `"redirect_uris"` → Harusnya ada `"https://form-documents.onrender.com/oauth.php"`

## Step 4: Jika TIDAK ADA atau SALAH

Berarti environment variable belum ter-apply dengan benar!

**Fix:**
1. Update `GOOGLE_CREDENTIALS_JSON` di Render Dashboard
2. Manual restart container
