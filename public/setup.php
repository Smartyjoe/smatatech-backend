<?php
/**
 * ============================================================================
 * SMATATECH API - Database Setup Script
 * ============================================================================
 * 
 * This script can be run from browser to set up the database on cPanel.
 * Access it at: https://your-domain.com/setup.php?token=setup123
 * 
 * IMPORTANT: Delete this file after successful setup!
 * ============================================================================
 */

// Prevent Laravel from handling this request
if (defined('LARAVEL_START')) {
    die('This script must be accessed directly, not through Laravel.');
}

// Security: Check for setup token - use a simple token
$setupToken = 'setup123';

// Debug: Show what token was received (remove in production)
if (!isset($_GET['token'])) {
    http_response_code(403);
    header('Content-Type: application/json');
    die(json_encode([
        'success' => false,
        'message' => 'Access denied. No token provided.',
        'usage' => 'Access this URL: ' . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/setup.php?token=setup123'
    ], JSON_PRETTY_PRINT));
}

if ($_GET['token'] !== $setupToken) {
    http_response_code(403);
    header('Content-Type: application/json');
    die(json_encode([
        'success' => false,
        'message' => 'Access denied. Invalid token.',
        'received_token' => $_GET['token'],
        'hint' => 'The correct token is: setup123'
    ], JSON_PRETTY_PRINT));
}

// Set content type
header('Content-Type: application/json');

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$results = [];
$errors = [];

try {
    // Step 1: Test database connection
    $results[] = '✓ Database connection successful';
    DB::connection()->getPdo();

    // Step 2: Check if migrations table exists
    $migrationsExist = Schema::hasTable('migrations');
    
    if (!$migrationsExist) {
        // Run fresh migrations
        $results[] = '→ Running migrations...';
        Artisan::call('migrate', ['--force' => true]);
        $results[] = '✓ Migrations completed';
        $results[] = Artisan::output();
    } else {
        // Run pending migrations only
        $results[] = '→ Running pending migrations...';
        Artisan::call('migrate', ['--force' => true]);
        $results[] = '✓ Migrations updated';
    }

    // Step 3: Run seeders
    $results[] = '→ Running database seeders...';
    
    // Check if roles exist
    if (!Schema::hasTable('roles') || DB::table('roles')->count() === 0) {
        Artisan::call('db:seed', ['--class' => 'RolesAndPermissionsSeeder', '--force' => true]);
        $results[] = '✓ Roles and permissions seeded';
    } else {
        $results[] = '→ Roles already exist, skipping...';
    }

    // Check if site settings exist
    if (!Schema::hasTable('site_settings') || DB::table('site_settings')->count() === 0) {
        Artisan::call('db:seed', ['--class' => 'SiteSettingsSeeder', '--force' => true]);
        $results[] = '✓ Site settings seeded';
    } else {
        $results[] = '→ Site settings already exist, skipping...';
    }

    // Check if admin exists
    if (DB::table('admins')->count() === 0) {
        Artisan::call('db:seed', ['--class' => 'AdminUserSeeder', '--force' => true]);
        $results[] = '✓ Admin user created';
    } else {
        $results[] = '→ Admin user already exists, skipping...';
    }

    // Check if sample data exists
    $hasServices = Schema::hasTable('services') && DB::table('services')->count() > 0;
    if (!$hasServices) {
        Artisan::call('db:seed', ['--class' => 'SampleDataSeeder', '--force' => true]);
        $results[] = '✓ Sample data seeded';
    } else {
        $results[] = '→ Sample data already exists, skipping...';
    }

    // Step 4: Clear and cache config
    $results[] = '→ Optimizing application...';
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    $results[] = '✓ Cache cleared';

    // Try to cache (may fail on some shared hosts)
    try {
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        $results[] = '✓ Configuration cached';
    } catch (Exception $e) {
        $results[] = '⚠ Could not cache config (this is OK on some hosts)';
    }

    // Step 5: Create storage link if not exists
    if (!file_exists(public_path('storage'))) {
        try {
            Artisan::call('storage:link');
            $results[] = '✓ Storage link created';
        } catch (Exception $e) {
            $results[] = '⚠ Could not create storage link - create manually if needed';
        }
    }

    // Final summary
    $adminCount = DB::table('admins')->count();
    $servicesCount = DB::table('services')->count();
    $categoriesCount = DB::table('categories')->count();
    $postsCount = Schema::hasTable('posts') ? DB::table('posts')->count() : 0;

    $summary = [
        'admins' => $adminCount,
        'services' => $servicesCount,
        'categories' => $categoriesCount,
        'posts' => $postsCount,
    ];

    echo json_encode([
        'success' => true,
        'message' => 'Setup completed successfully!',
        'results' => $results,
        'database_summary' => $summary,
        'next_steps' => [
            '1. DELETE this setup.php file immediately!',
            '2. Test the API at /api',
            '3. Test documentation at /api/docs',
            '4. Login at /api/admin/login with:',
            '   - Email: admin@smatatech.com OR admin@smatatech.com.ng',
            '   - Password: password OR Admin@123456',
            '5. Change the admin password after first login!',
        ],
        'api_endpoints' => [
            'index' => url('/api'),
            'docs' => url('/api/docs'),
            'health' => url('/api/health'),
            'services' => url('/api/services'),
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Setup failed',
        'error' => $e->getMessage(),
        'results' => $results,
        'troubleshooting' => [
            '1. Check your .env file has correct database credentials',
            '2. Ensure the database exists in cPanel MySQL Databases',
            '3. Ensure the database user has all privileges',
            '4. Check storage/ and bootstrap/cache/ are writable (755)',
        ],
    ], JSON_PRETTY_PRINT);
}
