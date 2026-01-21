# Database SQL Export

## For Manual Database Setup (Alternative to Migrations)

If you cannot run migrations via the web installer, you can manually import the database schema.

### Option 1: Use the Web Installer (Recommended)

1. Access `https://api.yourdomain.com/install.php?token=YOUR_TOKEN`
2. Click "Run Migrations"
3. Click "Run Seeders"

### Option 2: Generate SQL and Import Manually

On your local machine:

```bash
# Run migrations locally first
php artisan migrate:fresh

# Export the database to SQL
mysqldump -u root -p your_local_db > database/sql/schema.sql

# For data as well (including seeders):
php artisan db:seed
mysqldump -u root -p your_local_db > database/sql/full_dump.sql
```

Then in cPanel:
1. Go to phpMyAdmin
2. Select your database
3. Click "Import"
4. Upload the SQL file

### Option 3: Run Migrations via PHP

Create a temporary PHP file in public folder:

```php
<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

Artisan::call('migrate', ['--force' => true]);
echo Artisan::output();

Artisan::call('db:seed', ['--force' => true]);
echo Artisan::output();
```

**Delete this file immediately after use!**

---

## Required Tables

The application requires these tables:

### Core Tables
- `users` - Public user accounts
- `admins` - Admin user accounts
- `personal_access_tokens` - API authentication tokens
- `password_reset_tokens` - Password reset functionality

### Content Tables
- `posts` - Blog posts
- `categories` - Blog categories
- `comments` - Blog comments
- `services` - Services listing
- `case_studies` - Portfolio/case studies
- `testimonials` - Client testimonials
- `brands` - Brand/client logos
- `contacts` - Contact form submissions

### Configuration Tables
- `site_settings` - Site configuration
- `chatbot_configs` - Chatbot settings
- `email_settings` - Email configuration
- `email_templates` - Email templates
- `brevo_configs` - Brevo email service config

### Credits & AI Tables
- `credits` - User credit balances
- `credit_transactions` - Credit transaction history
- `ai_tools` - AI tools configuration
- `ai_usage_logs` - AI usage tracking

### System Tables
- `activity_logs` - Activity logging
- `migrations` - Laravel migrations tracking
- `cache` - Cache storage (if using database cache)
- `sessions` - Session storage (if using database sessions)
- `jobs` - Queue jobs (if using database queue)
- `failed_jobs` - Failed queue jobs

### Permission Tables (Spatie)
- `permissions` - Permission definitions
- `roles` - Role definitions
- `model_has_permissions` - Model-permission relations
- `model_has_roles` - Model-role relations
- `role_has_permissions` - Role-permission relations

---

## Available SQL Files

### `seed_data.sql`
Contains sample data for all content tables to ensure API endpoints return data:
- Categories (5 categories)
- Services (6 services)  
- Testimonials (5 testimonials)
- Brands (6 brands)
- Case Studies (3 case studies)
- Site Settings
- Chatbot Configuration

**Usage:** Import via phpMyAdmin AFTER running migrations.

---

## Quick Setup for cPanel

### Recommended: Use the Web Setup Script
1. Upload all files to your cPanel public_html directory
2. Configure `.env` with your database credentials
3. Access `https://your-domain.com/setup.php?token=smatatech-setup-2026`
4. The script will automatically run migrations and seeders
5. **DELETE setup.php immediately after setup!**

### Verify Setup Success
Test these endpoints:
- `GET /api` - API index with all endpoints
- `GET /api/docs` - Full API documentation  
- `GET /api/services` - Should return 6 services
- `GET /api/categories` - Should return 5 categories
- `GET /api/testimonials` - Should return 5 testimonials
- `GET /api/brands` - Should return 6 brands
- `GET /api/case-studies` - Should return 3 case studies
- `POST /api/contact` - Should accept contact form submissions
