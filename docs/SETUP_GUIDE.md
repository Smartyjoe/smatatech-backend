# Smatatech Backend Setup Guide

## Requirements

- PHP >= 8.2
- Composer
- MySQL >= 5.7
- Node.js & npm (for frontend)

## Installation Steps

### 1. Clone and Navigate
```bash
cd backend
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Configure Environment
Copy `.env.example` to `.env` and configure:

```env
APP_NAME="Smatatech Backend"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smatatech_transposition
DB_USERNAME=smatatech_transposition
DB_PASSWORD=123fourfive6

# Mail Configuration (Brevo)
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
MAIL_USERNAME=your-brevo-username
MAIL_PASSWORD=your-brevo-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@smatatech.com"
MAIL_FROM_NAME="${APP_NAME}"

# Brevo API
BREVO_API_KEY=your-brevo-api-key
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Run Migrations
```bash
php artisan migrate
```

### 6. Seed Database
```bash
php artisan db:seed
```

This will create:
- 2 admin users
- Default site settings
- Default chatbot configuration
- Email templates

### 7. Create Storage Link
```bash
php artisan storage:link
```

### 8. Start Development Server
```bash
php artisan serve
```

The API will be available at: `http://localhost:8000/api/v1`

## Default Admin Credentials

**Super Admin:**
- Email: admin@smatatech.com
- Password: password123

**Regular Admin:**
- Email: admin2@smatatech.com
- Password: password123

**⚠️ IMPORTANT:** Change these passwords in production!

## Frontend Integration

### Update Frontend .env

In your React frontend project, update `.env`:

```env
# Disable mock API
VITE_USE_MOCK_API=false

# Point to Laravel backend
VITE_ADMIN_API_BASE_URL=http://localhost:8000/api/v1
VITE_PUBLIC_API_BASE_URL=http://localhost:8000/api/v1
```

### Start Frontend
```bash
npm run dev
```

## Testing the Backend

### 1. Test Health Check
```bash
curl http://localhost:8000/up
```

### 2. Test Admin Login
```bash
curl -X POST http://localhost:8000/api/v1/admin/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@smatatech.com","password":"password123"}'
```

### 3. Test Public API
```bash
curl http://localhost:8000/api/v1/brands
curl http://localhost:8000/api/v1/services
curl http://localhost:8000/api/v1/settings
```

## Troubleshooting

### Database Connection Error
- Verify MySQL is running
- Check database credentials in `.env`
- Ensure database `smatatech_transposition` exists

### CORS Issues
- Check `config/cors.php` includes your frontend URL
- Verify frontend is running on allowed origin (localhost:5173 or localhost:3000)

### Storage/Upload Issues
- Run `php artisan storage:link` again
- Check permissions on `storage` and `bootstrap/cache` directories
- On Windows: Run as administrator if needed

### Migration Errors
- Run `php artisan migrate:fresh` to reset database
- Then run `php artisan db:seed` again

## Production Deployment

### 1. Environment Configuration
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

### 2. Optimize Application
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

### 3. Set Permissions
```bash
chmod -R 755 storage bootstrap/cache
```

### 4. Configure Web Server
Point your web server document root to `public` directory.

**Apache .htaccess** (already included)

**Nginx Configuration:**
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 5. Enable HTTPS
- Install SSL certificate
- Update APP_URL in .env
- Update CORS origins in config/cors.php

### 6. Setup Queue Workers (Optional)
For better email performance:
```bash
php artisan queue:work --daemon
```

## Maintenance

### Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Backup Database
```bash
mysqldump -u smatatech_transposition -p smatatech_transposition > backup.sql
```

### View Logs
```bash
tail -f storage/logs/laravel.log
```

## Additional Features

### File Upload Limits
Edit `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Rate Limiting
Configure in `app/Http/Kernel.php` (already set up with Sanctum)

## Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Review API documentation: `docs/API_DOCUMENTATION.md`
- Verify all environment variables are set correctly

---

**Backend Version:** 1.0.0  
**Laravel Version:** 11.x  
**Last Updated:** January 2024
