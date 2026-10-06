@echo off
title Web PHP Warehouse System (Port 8080)
echo ========================================================
echo   Menjalankan Web PHP Warehouse System pada Port 8080...
echo   URL: http://127.0.0.1:8080
echo ========================================================
cd /d "%~dp0"
php -S 127.0.0.1:8080 -t public
pause
