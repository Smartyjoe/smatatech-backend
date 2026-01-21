# Smatatech API - cPanel Deployment Guide

## Complete Step-by-Step Guide for Shared Hosting (No SSH Required)

This guide walks you through deploying the Smatatech Laravel API backend on a cPanel shared hosting environment **without SSH/terminal access**.

---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Section A: Before Upload (Local Preparation)](#section-a-before-upload-local-preparation)
3. [Section B: Uploading to cPanel](#section-b-uploading-to-cpanel)
4. [Section C: Database Configuration](#section-c-database-configuration)
5. [Section D: Storage & Permissions](#section-d-storage--permissions)
6. [Section E: Running Installation](#section-e-running-installation)
7. [Section F: Final Verification](#section-f-final-verification)
8. [Troubleshooting](#troubleshooting)

---

## Prerequisites

Before starting, ensure you have:

- [ ] cPanel hosting account with PHP 8.1+ support
- [ ] MySQL database access
- [ ] FTP access OR cPanel File Manager access
- [ ] Your domain/subdomain configured
- [ ] Local development environment with PHP & Composer

### Recommended Hosting Requirements

| Requirement | Minimum | Recommended |
|-------------|---------|-------------|
| PHP Version | 8.1 | 8.2+ |
| MySQL Version | 5.7 | 8.0+ |
| Disk Space | 100MB | 500MB+ |
| Memory Limit | 128MB | 256MB+ |

---

## Section A: Before Upload (Local Preparation)

### Step 1: Install Dependencies Locally

On your local machine, run:

```bash
# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# This creates the vendor folder which is required
```

> ⚠️ **Important**: The `vendor` folder MUST be uploaded to the server since you cannot run Composer on shared hosting.

### Step 2: Generate Application Key (Optional - Can Do on Server)

```bash
php artisan key:generate
```

Copy the generated `APP_KEY` from your `.env` file. It looks like:
```
APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

### Step 3: Prepare Your .env File

1. Copy `.env.example` to `.env.production`
2. Edit with your production values:

```env
#==========================================================================
# PRODUCTION ENVIRONMENT CONFIGURATION
#==========================================================================

APP_NAME="Smatatech API"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

#--------------------------------------------------------------------------
# DATABASE (Get these from cPanel MySQL Databases)
#--------------------------------------------------------------------------
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpaneluser_dbname
DB_USERNAME=cpaneluser_dbuser
DB_PASSWORD=your_secure_password

#--------------------------------------------------------------------------
# DRIVERS (File-based for shared hosting)
#--------------------------------------------------------------------------
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

#--------------------------------------------------------------------------
# LOGGING
#--------------------------------------------------------------------------
LOG_CHANNEL=single
LOG_LEVEL=error

#--------------------------------------------------------------------------
# CORS - Your Frontend URLs
#--------------------------------------------------------------------------
FRONTEND_URL=https://yourdomain.com
ADMIN_FRONTEND_URL=https://admin.yourdomain.com
SANCTUM_STATEFUL_DOMAINS=yourdomain.com,admin.yourdomain.com

#--------------------------------------------------------------------------
# MAIL (Use your cPanel email or external service)
#--------------------------------------------------------------------------
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=465
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

#--------------------------------------------------------------------------
# INSTALLER SECURITY TOKEN (CHANGE THIS!)
#--------------------------------------------------------------------------
INSTALL_TOKEN=your_random_secret_token_here_2024
```

### Step 4: Prepare Files for Upload

Create a ZIP file containing the entire project:

**Files/Folders to Include:**
```
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── vendor/          ← IMPORTANT: Include this!
├── .env.production  ← Your production env file
├── artisan
├── composer.json
└── composer.lock
```

**Files to EXCLUDE:**
```
├── .git/
├── node_modules/
├── tests/
├── .env             ← Local env (use .env.production instead)
```

---

## Section B: Uploading to cPanel

### Option 1: Standard Subdomain Setup (Recommended)

This setup uses a subdomain like `api.yourdomain.com`.

#### Step 1: Create Subdomain in cPanel

1. Log into cPanel
2. Go to **Domains** → **Subdomains** (or **Domains** in newer cPanel)
3. Create subdomain: `api.yourdomain.com`
4. Set document root to: `/home/username/api.yourdomain.com/public`

#### Step 2: Upload Files

**Using File Manager:**

1. Go to **File Manager** in cPanel
2. Navigate to `/home/username/` (your home directory)
3. Create folder: `api.yourdomain.com`
4. Upload your ZIP file to this folder
5. Right-click the ZIP → **Extract**
6. Delete the ZIP file after extraction

**File Structure Should Be:**
```
/home/username/api.yourdomain.com/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/          ← Subdomain points HERE
│   ├── index.php
│   ├── install.php
│   └── .htaccess
├── resources/
├── routes/
├── storage/
├── vendor/
├── .env             ← Rename .env.production to .env
└── artisan
```

#### Step 3: Rename Environment File

1. In File Manager, navigate to `/home/username/api.yourdomain.com/`
2. Rename `.env.production` to `.env`
3. Or create new `.env` file and paste your configuration

---

### Option 2: Main Domain with /api Path

If you want the API at `yourdomain.com/api`:

#### Step 1: Upload to Home Directory

Upload Laravel files to a folder OUTSIDE `public_html`:

```
/home/username/
├── laravel-api/           ← Laravel files here
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── public/
│   └── ...
└── public_html/           ← Your main website
    └── api/               ← Symlink or copy of Laravel's public folder
```

#### Step 2: Configure public_html/api

**Option A: Create index.php redirect**

Create `/home/username/public_html/api/index.php`:

```php
<?php
/**
 * Laravel API Entry Point
 * This file redirects requests to the Laravel application
 */

// Change this path to match your Laravel installation
$laravelPath = '/home/username/laravel-api';

// Set up the paths
$publicPath = $laravelPath . '/public';

// Change to Laravel's public directory
chdir($publicPath);

// Load Laravel
require $publicPath . '/index.php';
```

**Option B: Copy public folder contents**

Copy contents of Laravel's `public/` folder to `public_html/api/` and modify `index.php`:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Adjust paths to point to Laravel installation
$basePath = '/home/username/laravel-api';

if (file_exists($maintenance = $basePath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $basePath.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $basePath.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
```

---

## Section C: Database Configuration

### Step 1: Create MySQL Database

1. In cPanel, go to **MySQL® Databases**
2. Create new database:
   - Database name: `yoursite_api` (will become `cpaneluser_yoursite_api`)
3. Note the full database name shown

### Step 2: Create Database User

1. Still in MySQL® Databases
2. Under "MySQL Users", create new user:
   - Username: `apiuser` (will become `cpaneluser_apiuser`)
   - Password: Use a strong password (save this!)
3. Note the full username shown

### Step 3: Add User to Database

1. Under "Add User To Database"
2. Select your user and database
3. Click **Add**
4. On privileges screen, check **ALL PRIVILEGES**
5. Click **Make Changes**

### Step 4: Update .env File

Update your `.env` with the database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpaneluser_yoursite_api
DB_USERNAME=cpaneluser_apiuser
DB_PASSWORD=your_secure_password_here
```

> 💡 **Tip**: In cPanel, database names and usernames are prefixed with your cPanel username automatically.

---

## Section D: Storage & Permissions

### Step 1: Set Folder Permissions

In cPanel File Manager, set these folders to permission **755** or **775**:

1. Navigate to your Laravel directory
2. Right-click each folder → **Change Permissions**

| Folder | Permission |
|--------|------------|
| `/storage` | 755 or 775 |
| `/storage/app` | 755 |
| `/storage/app/public` | 755 |
| `/storage/framework` | 755 |
| `/storage/framework/cache` | 755 |
| `/storage/framework/sessions` | 755 |
| `/storage/framework/views` | 755 |
| `/storage/logs` | 755 |
| `/bootstrap/cache` | 755 |

### Step 2: Create Required Directories

If any directories are missing, create them:

1. In File Manager, navigate to `/storage/framework/`
2. Create folders if missing:
   - `cache/data`
   - `sessions`
   - `views`

### Step 3: Create Storage Link

The storage link connects `public/storage` to `storage/app/public`.

**Method 1: Via install.php (Recommended)**
- Access the installer (see Section E) and click "Create Storage Link"

**Method 2: Manual (if symlinks don't work)**
1. In File Manager, navigate to `/public/`
2. Create folder named `storage`
3. Manually copy uploaded files from `/storage/app/public/` to `/public/storage/` when needed

---

## Section E: Running Installation

### Step 1: Access the Installer

1. Open your browser
2. Navigate to: `https://api.yourdomain.com/install.php?token=YOUR_INSTALL_TOKEN`

   Replace `YOUR_INSTALL_TOKEN` with the value you set in `.env`

### Step 2: Check System Status

The installer will show:
- ✅ Environment configuration
- ✅ Database connection status
- ✅ Storage permissions
- ✅ Required PHP extensions

### Step 3: Run Setup Commands

Click these buttons in order:

1. **🔑 Generate App Key** (if not already set)
2. **📁 Create Required Directories**
3. **🔗 Create Storage Link**
4. **🗄️ Run Migrations**
5. **🌱 Run Seeders** (creates default admin user)
6. **🧹 Clear All Caches**
7. **⚡ Optimize** (optional, for performance)

### Step 4: Note Default Credentials

After running seeders, the default admin account is:
- **Email**: `admin@smatatech.com`
- **Password**: `password`

> ⚠️ **IMPORTANT**: Change this password immediately after first login!

### Step 5: Delete install.php

**CRITICAL SECURITY STEP!**

1. In cPanel File Manager
2. Navigate to `/public/`
3. Delete `install.php`

---

## Section F: Final Verification

### Test 1: Health Check

Open in browser:
```
https://api.yourdomain.com/api/health
```

Expected response:
```json
{
  "status": "ok",
  "timestamp": "2024-01-20T12:00:00+00:00",
  "app": "Smatatech API",
  "environment": "production",
  "database": "connected",
  "storage_writable": true
}
```

### Test 2: Public API

```
https://api.yourdomain.com/api/settings
```

Should return site settings JSON.

### Test 3: Admin Login

Using Postman, cURL, or your frontend:

```bash
curl -X POST https://api.yourdomain.com/api/admin/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "admin@smatatech.com", "password": "password"}'
```

Expected response:
```json
{
  "success": true,
  "data": {
    "user": {
      "name": "Admin",
      "email": "admin@smatatech.com",
      "role": "super_admin"
    },
    "token": "1|xxxxxxxxxxxx",
    "expiresAt": "2024-01-21T12:00:00+00:00"
  }
}
```

### Test 4: Laravel Health Route

```
https://api.yourdomain.com/up
```

Should return 200 OK.

---

## Troubleshooting

### Problem: 500 Internal Server Error

**Solutions:**

1. **Check .env file exists and is readable**
   ```
   /home/username/api.yourdomain.com/.env
   ```

2. **Check APP_KEY is set**
   - Open `.env` and ensure APP_KEY has a value
   - Run "Generate App Key" from installer

3. **Check storage permissions**
   - Set `/storage` and all subfolders to 755

4. **Check error logs**
   - cPanel → Error Log
   - Or check `/storage/logs/laravel.log`

### Problem: Database Connection Error

**Solutions:**

1. **Verify credentials in .env**
   - DB_DATABASE, DB_USERNAME, DB_PASSWORD must match cPanel exactly
   - Remember: cPanel prefixes username to database names

2. **Test connection in cPanel**
   - Go to phpMyAdmin
   - Try logging in with your database credentials

3. **Check DB_HOST**
   - Usually `localhost` for shared hosting
   - Some hosts use `127.0.0.1`

### Problem: CORS Errors

**Solutions:**

1. **Update .env CORS settings**
   ```env
   FRONTEND_URL=https://yourdomain.com
   ADMIN_FRONTEND_URL=https://admin.yourdomain.com
   ```

2. **Clear config cache**
   - Access installer → Clear All Caches

3. **Check SANCTUM_STATEFUL_DOMAINS**
   - Should include all frontend domains without protocol

### Problem: Storage Link Not Working

**Solutions:**

1. **Symlinks disabled on host**
   - Many shared hosts disable symlinks
   - Use manual copy method instead

2. **Manual workaround**
   - Create `/public/storage/` folder manually
   - Copy uploaded files there when needed
   - Or use cloud storage (S3) for uploads

### Problem: File Upload Errors

**Solutions:**

1. **Check PHP upload limits**
   - In cPanel → MultiPHP INI Editor
   - Increase `upload_max_filesize` (e.g., 10M)
   - Increase `post_max_size` (e.g., 12M)
   - Increase `max_execution_time` (e.g., 120)

2. **Check folder permissions**
   - `/storage/app/public` should be 755

### Problem: Routes Not Working (404)

**Solutions:**

1. **Check .htaccess exists**
   - `/public/.htaccess` must exist

2. **Enable mod_rewrite**
   - Contact hosting support if needed

3. **Verify document root**
   - Must point to Laravel's `/public` folder

### Problem: Session/Login Issues

**Solutions:**

1. **Ensure SESSION_DRIVER=file**
   - Database sessions require the sessions table

2. **Check session folder**
   - `/storage/framework/sessions` must be writable

3. **Clear session files**
   - Delete files in `/storage/framework/sessions/`

---

## Post-Deployment Checklist

- [ ] install.php deleted
- [ ] APP_DEBUG=false in .env
- [ ] Default admin password changed
- [ ] CORS configured for your frontend domains
- [ ] SSL certificate active (HTTPS)
- [ ] Error logging configured
- [ ] Backup strategy in place
- [ ] API endpoints tested
- [ ] Frontend connected and working

---

## Maintenance Commands via install.php

If you need to run maintenance tasks later, you can temporarily upload `install.php` again.

**Always remember to:**
1. Use a strong, unique INSTALL_TOKEN
2. Delete install.php immediately after use
3. Never leave install.php on a production server

---

## Support

For issues specific to this deployment:
1. Check Laravel logs: `/storage/logs/laravel.log`
2. Check cPanel Error Log
3. Review this guide's troubleshooting section

---

*Guide Version: 1.0 | Last Updated: January 2024*
