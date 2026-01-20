@echo off
echo ==========================================
echo  Smatatech API - Deployment Preparation
echo ==========================================
echo.

REM Clear Laravel caches
echo [1/6] Clearing Laravel caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo Done!
echo.

REM Install production dependencies
echo [2/6] Installing production dependencies...
call composer install --optimize-autoloader --no-dev
echo Done!
echo.

REM Create deployment folder
echo [3/6] Creating deployment folder...
if exist "deployment" rmdir /s /q "deployment"
mkdir deployment
echo Done!
echo.

REM Copy files (excluding unnecessary ones)
echo [4/6] Copying files to deployment folder...
xcopy /E /I /Y "app" "deployment\app"
xcopy /E /I /Y "bootstrap" "deployment\bootstrap"
xcopy /E /I /Y "config" "deployment\config"
xcopy /E /I /Y "database" "deployment\database"
xcopy /E /I /Y "public" "deployment\public"
xcopy /E /I /Y "resources" "deployment\resources"
xcopy /E /I /Y "routes" "deployment\routes"
xcopy /E /I /Y "storage" "deployment\storage"
xcopy /E /I /Y "vendor" "deployment\vendor"
copy /Y "artisan" "deployment\artisan"
copy /Y "composer.json" "deployment\composer.json"
copy /Y "composer.lock" "deployment\composer.lock"
copy /Y ".htaccess" "deployment\.htaccess"
copy /Y ".env.production" "deployment\.env.production"
copy /Y "DEPLOYMENT_GUIDE.md" "deployment\DEPLOYMENT_GUIDE.md"
echo Done!
echo.

REM Create empty storage directories
echo [5/6] Ensuring storage structure...
mkdir "deployment\storage\app\public" 2>nul
mkdir "deployment\storage\framework\cache\data" 2>nul
mkdir "deployment\storage\framework\sessions" 2>nul
mkdir "deployment\storage\framework\views" 2>nul
mkdir "deployment\storage\logs" 2>nul
echo. > "deployment\storage\app\.gitignore"
echo. > "deployment\storage\framework\.gitignore"
echo. > "deployment\storage\logs\.gitignore"
echo Done!
echo.

echo [6/6] Creating zip file...
echo Please manually zip the 'deployment' folder
echo or use a tool like 7-Zip to create deployment.zip
echo.

echo ==========================================
echo  Preparation Complete!
echo ==========================================
echo.
echo Next steps:
echo 1. Zip the 'deployment' folder
echo 2. Upload to cPanel
echo 3. Follow DEPLOYMENT_GUIDE.md
echo.
pause
