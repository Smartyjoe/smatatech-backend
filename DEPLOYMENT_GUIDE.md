# Smatatech Backend - cPanel Deployment Guide

## Prerequisites

Before you begin, ensure you have:
- cPanel hosting with PHP 8.2+ support
- MySQL database access
- SSH access (recommended) or File Manager access
- Your frontend URLs ready (for CORS configuration)

---

## Step 1: Prepare the Deployment Package

### Option A: Using the Provided Script (Recommended)

Run this command in your local project directory:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
composer install --optimize-autoloader --no-dev
```

### Option B: Manual Preparation

1. Delete these folders/files if they exist:
   - `node_modules/`
   - `.git/`
   - `tests/`
   - `.env` (keep `.env.production` as reference)

---

## Step 2: Create MySQL Database in cPanel

1. Login to your **cPanel**
2. Go to **MySQL® Databases**
3. Create a new database:
   - Database name: `smatatech_api` (will become `cpanelusername_smatatech_api`)
4. Create a new user:
   - Username: `smatatech_user` (will become `cpanelusername_smatatech_user`)
   - Password: Use a strong password (save this!)
5. Add user to database:
   - Select **ALL PRIVILEGES**
   - Click **Make Changes**

**Note your credentials:**
```
DB_DATABASE: cpanelusername_smatatech_api
DB_USERNAME: cpanelusername_smatatech_user
DB_PASSWORD: your_password_here
```

---

## Step 3: Upload Files to cPanel

### Method A: File Manager (Easiest)

1. Zip your entire project folder
2. Login to **cPanel** > **File Manager**
3. Navigate to your desired location:
   - For subdomain: `/home/username/api.yourdomain.com/`
   - For subdirectory: `/home/username/public_html/api/`
4. Click **Upload** and upload the zip file
5. Right-click the zip file > **Extract**
6. Delete the zip file after extraction

### Method B: FTP/SFTP (Recommended for large files)

1. Use FileZilla or similar FTP client
2. Connect using your cPanel FTP credentials
3. Upload all files to the appropriate directory

---

## Step 4: Configure Environment File

1. In **File Manager**, navigate to your project root
2. Copy `.env.production` to `.env`:
   - Select `.env.production`
   - Click **Copy**
   - Name it `.env`
3. Edit the `.env` file and update:

```env
APP_URL=https://api.yourdomain.com

# Your frontend URLs
FRONTEND_URL=https://yourdomain.com
ADMIN_FRONTEND_URL=https://admin.yourdomain.com

# Database (from Step 2)
DB_DATABASE=cpanelusername_smatatech_api
DB_USERNAME=cpanelusername_smatatech_user
DB_PASSWORD=your_actual_password

# Sanctum domains (your frontend domains)
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,admin.yourdomain.com,api.yourdomain.com
```

---

## Step 5: Generate Application Key

### Option A: Via SSH (Recommended)

```bash
cd /home/username/api.yourdomain.com
php artisan key:generate
```

### Option B: Manual Generation

1. Go to https://generate-random.org/laravel-key-generator
2. Copy the generated key
3. Paste in `.env` file:
```env
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

---

## Step 6: Set Directory Permissions

### Via SSH:

```bash
cd /home/username/api.yourdomain.com

# Storage and cache must be writable
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Make sure directories exist
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/logs
```

### Via File Manager:

1. Right-click `storage` folder > **Change Permissions**
2. Set to `775` (check all boxes except "Write" for "World")
3. Check **"Recurse into subdirectories"**
4. Repeat for `bootstrap/cache` folder

---

## Step 7: Run Database Migrations

### Via SSH (Recommended):

```bash
cd /home/username/api.yourdomain.com
php artisan migrate --force
php artisan db:seed --force
```

### Via cPanel Cron Job (Alternative):

1. Go to **cPanel** > **Cron Jobs**
2. Add a one-time cron job:
```
php /home/username/api.yourdomain.com/artisan migrate --force && php /home/username/api.yourdomain.com/artisan db:seed --force
```
3. Run it once, then delete the cron job

---

## Step 8: Create Storage Symlink

### Via SSH:

```bash
cd /home/username/api.yourdomain.com
php artisan storage:link
```

### Manual Alternative:

1. In **File Manager**, navigate to `public/` folder
2. Delete `storage` if it exists
3. Create a symbolic link:
   - Some cPanels allow this via File Manager
   - Or use SSH: `ln -s ../storage/app/public public/storage`

---

## Step 9: Configure Subdomain (if using subdomain)

1. Go to **cPanel** > **Domains** or **Subdomains**
2. Create subdomain: `api.yourdomain.com`
3. Set document root to: `/home/username/api.yourdomain.com/public`
   - **Important:** Point to the `public` folder!

---

## Step 10: Enable SSL (Required for Production)

1. Go to **cPanel** > **SSL/TLS Status** or **Let's Encrypt**
2. Enable SSL for `api.yourdomain.com`
3. Enable "Force HTTPS" redirect

---

## Step 11: Update CORS Configuration

Edit `config/cors.php` if needed, or set environment variables:

```env
FRONTEND_URL=https://yourdomain.com
ADMIN_FRONTEND_URL=https://admin.yourdomain.com
```

---

## Step 12: Test the API

### Test Health Check:
```
GET https://api.yourdomain.com/up
```

### Test Admin Login:
```
POST https://api.yourdomain.com/api/admin/login
Content-Type: application/json

{
    "email": "admin@smatatech.com",
    "password": "password"
}
```

Expected response:
```json
{
    "success": true,
    "data": {
        "user": { ... },
        "token": "1|xxxxxxxx",
        "expiresAt": "2026-01-21T..."
    }
}
```

---

## Step 13: Optimize for Production

### Via SSH:

```bash
cd /home/username/api.yourdomain.com

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear old caches
php artisan cache:clear
```

---

## Troubleshooting

### Error 500 - Internal Server Error

1. Check `storage/logs/laravel.log` for details
2. Verify permissions on `storage/` and `bootstrap/cache/`
3. Ensure `.env` file exists and is configured

### CORS Errors

1. Verify `FRONTEND_URL` and `ADMIN_FRONTEND_URL` in `.env`
2. Check `SANCTUM_STATEFUL_DOMAINS` includes your frontend domains
3. Ensure SSL is enabled on all domains

### Database Connection Error

1. Verify database credentials in `.env`
2. Check database exists in cPanel
3. Ensure user has privileges on the database

### 404 on API Routes

1. Verify `.htaccess` files exist (root and public/)
2. Check mod_rewrite is enabled
3. Ensure document root points to `public/` folder

### Token Issues

1. Clear config cache: `php artisan config:clear`
2. Regenerate key: `php artisan key:generate`
3. Clear sanctum tokens: Check `personal_access_tokens` table

---

## Default Admin Credentials

After running seeders:
- **Email:** admin@smatatech.com
- **Password:** password

⚠️ **IMPORTANT:** Change this password immediately after first login!

---

## Security Checklist

- [ ] Changed default admin password
- [ ] APP_DEBUG set to `false`
- [ ] APP_ENV set to `production`
- [ ] SSL enabled and forced
- [ ] Database credentials secure
- [ ] `.env` file not accessible via web
- [ ] `storage/` not accessible via web

---

## Support

If you encounter issues:
1. Check `storage/logs/laravel.log`
2. Enable debug temporarily: `APP_DEBUG=true`
3. Check cPanel error logs
4. Verify PHP version is 8.2+

