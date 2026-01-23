@echo off
cd /d "c:\Users\Admin\Herd\quiz-system"
del /f database\database.sqlite 2>nul
php artisan migrate --seed
pause
