@echo off
echo ========================================
echo   Form Document System - Quick Setup
echo ========================================
echo.

echo [1/3] Checking Composer...
where composer >nul 2>nul
if %errorlevel% neq 0 (
    echo ERROR: Composer tidak ditemukan!
    echo Silakan install Composer dari https://getcomposer.org/
    pause
    exit /b
)
echo OK: Composer found!
echo.

echo [2/3] Installing dependencies...
call composer install
if %errorlevel% neq 0 (
    echo ERROR: Gagal install dependencies!
    pause
    exit /b
)
echo OK: Dependencies installed!
echo.

echo [3/3] Creating .env file...
if exist .env (
    echo .env sudah ada, skip...
) else (
    copy .env.example .env
    echo OK: .env file created!
)
echo.

echo ========================================
echo   Setup Completed!
echo ========================================
echo.
echo Langkah selanjutnya:
echo 1. Setup Google Cloud Project (lihat SETUP_GUIDE.md)
echo 2. Download credentials.json dan taruh di folder ini
echo 3. Edit file .env dengan Drive Folder ID dan Spreadsheet ID
echo 4. Jalankan XAMPP Apache
echo 5. Buka http://localhost/form-document/
echo.
echo Dokumentasi lengkap: README.md
echo Panduan setup: SETUP_GUIDE.md
echo.
pause
