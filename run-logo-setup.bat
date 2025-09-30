@echo off
echo =========================================
echo   Logo Upload Feature - Setup
echo =========================================
echo.

echo Step 1: Running migrations...
php artisan migrate --force

echo.
echo Step 2: Clearing cache...
php artisan cache:clear

echo.
echo Step 3: Clearing config cache...
php artisan config:clear

echo.
echo =========================================
echo   Setup Complete!
echo =========================================
echo.
echo Next steps:
echo 1. Login as admin
echo 2. Go to: http://localhost:8000/admin/settings
echo 3. Upload your company logo
echo.
echo Press any key to exit...
pause > nul
