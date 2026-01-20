#!/bin/bash

echo "=========================================="
echo " Smatatech API - Deployment Preparation"
echo "=========================================="
echo ""

# Clear Laravel caches
echo "[1/7] Clearing Laravel caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo "Done!"
echo ""

# Install production dependencies
echo "[2/7] Installing production dependencies..."
composer install --optimize-autoloader --no-dev
echo "Done!"
echo ""

# Remove old deployment folder if exists
echo "[3/7] Cleaning up old deployment folder..."
rm -rf deployment
rm -f deployment.zip
mkdir -p deployment
echo "Done!"
echo ""

# Copy files
echo "[4/7] Copying files to deployment folder..."
cp -r app deployment/
cp -r bootstrap deployment/
cp -r config deployment/
cp -r database deployment/
cp -r public deployment/
cp -r resources deployment/
cp -r routes deployment/
cp -r vendor deployment/
cp artisan deployment/
cp composer.json deployment/
cp composer.lock deployment/
cp .htaccess deployment/
cp .env.production deployment/
cp DEPLOYMENT_GUIDE.md deployment/
echo "Done!"
echo ""

# Create storage structure
echo "[5/7] Creating storage structure..."
mkdir -p deployment/storage/app/public
mkdir -p deployment/storage/framework/cache/data
mkdir -p deployment/storage/framework/sessions
mkdir -p deployment/storage/framework/views
mkdir -p deployment/storage/logs
touch deployment/storage/app/.gitignore
touch deployment/storage/framework/.gitignore
touch deployment/storage/logs/.gitignore
echo "Done!"
echo ""

# Remove unnecessary files from deployment
echo "[6/7] Cleaning deployment folder..."
rm -rf deployment/.git
rm -rf deployment/node_modules
rm -rf deployment/tests
rm -f deployment/.env
rm -f deployment/.env.example
rm -f deployment/phpunit.xml
rm -f deployment/package.json
rm -f deployment/package-lock.json
rm -f deployment/vite.config.js
rm -f deployment/tailwind.config.js
rm -f deployment/postcss.config.js
echo "Done!"
echo ""

# Create zip file
echo "[7/7] Creating deployment.zip..."
cd deployment
zip -r ../deployment.zip . -x "*.git*"
cd ..
echo "Done!"
echo ""

echo "=========================================="
echo " Preparation Complete!"
echo "=========================================="
echo ""
echo "Created: deployment.zip"
echo ""
echo "Next steps:"
echo "1. Upload deployment.zip to cPanel"
echo "2. Extract in your target directory"
echo "3. Follow DEPLOYMENT_GUIDE.md"
echo ""
