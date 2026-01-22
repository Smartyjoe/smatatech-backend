<?php
/**
 * ============================================================================
 * SMATATECH API - Browser-based Migration & Setup Script
 * ============================================================================
 * 
 * Access: https://your-domain.com/migrate.php?token=setup123
 * 
 * This script will:
 * 1. Run all pending database migrations
 * 2. Seed missing data
 * 3. Fix content statuses (publish services, posts, etc.)
 * 4. Clear and rebuild caches
 * 
 * IMPORTANT: DELETE THIS FILE IMMEDIATELY AFTER USE!
 * ============================================================================
 */

// Security token - change this before uploading!
$setupToken = 'setup123';

// Verify token
if (!isset($_GET['token']) || $_GET['token'] !== $setupToken) {
    http_response_code(403);
    header('Content-Type: application/json');
    die(json_encode([
        'success' => false,
        'message' => 'Access denied. Invalid or missing token.',
        'usage' => 'Add ?token=setup123 to the URL',
    ], JSON_PRETTY_PRINT));
}

// Set headers
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);
set_time_limit(300); // 5 minutes max

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

$results = [];
$errors = [];

try {
    // =========================================================================
    // STEP 1: Test Database Connection
    // =========================================================================
    DB::connection()->getPdo();
    $results[] = '✓ Database connection successful';

    // =========================================================================
    // STEP 2: Run Migrations
    // =========================================================================
    $results[] = '→ Running migrations...';
    
    try {
        Artisan::call('migrate', ['--force' => true]);
        $migrationOutput = Artisan::output();
        $results[] = '✓ Migrations completed';
        
        // Show what migrations ran
        $migrationLines = array_filter(explode("\n", trim($migrationOutput)));
        foreach ($migrationLines as $line) {
            if (!empty(trim($line))) {
                $results[] = '  ' . trim($line);
            }
        }
    } catch (Exception $e) {
        $errors[] = 'Migration error: ' . $e->getMessage();
    }

    // =========================================================================
    // STEP 3: Run Seeders (if tables are empty)
    // =========================================================================
    $results[] = '→ Checking seeders...';

    // Roles and Permissions
    if (!Schema::hasTable('roles') || DB::table('roles')->count() === 0) {
        try {
            Artisan::call('db:seed', ['--class' => 'RolesAndPermissionsSeeder', '--force' => true]);
            $results[] = '✓ Roles and permissions seeded';
        } catch (Exception $e) {
            $errors[] = 'RolesAndPermissionsSeeder error: ' . $e->getMessage();
        }
    } else {
        $results[] = '  → Roles already exist, skipping';
    }

    // Site Settings
    if (!Schema::hasTable('site_settings') || DB::table('site_settings')->count() === 0) {
        try {
            Artisan::call('db:seed', ['--class' => 'SiteSettingsSeeder', '--force' => true]);
            $results[] = '✓ Site settings seeded';
        } catch (Exception $e) {
            $errors[] = 'SiteSettingsSeeder error: ' . $e->getMessage();
        }
    } else {
        $results[] = '  → Site settings already exist, skipping';
    }

    // Admin User
    if (Schema::hasTable('admins') && DB::table('admins')->count() === 0) {
        try {
            Artisan::call('db:seed', ['--class' => 'AdminUserSeeder', '--force' => true]);
            $results[] = '✓ Admin user seeded';
        } catch (Exception $e) {
            $errors[] = 'AdminUserSeeder error: ' . $e->getMessage();
        }
    }

    // Sample Data
    $hasServices = Schema::hasTable('services') && DB::table('services')->count() > 0;
    if (!$hasServices) {
        try {
            Artisan::call('db:seed', ['--class' => 'SampleDataSeeder', '--force' => true]);
            $results[] = '✓ Sample data seeded';
        } catch (Exception $e) {
            $errors[] = 'SampleDataSeeder error: ' . $e->getMessage();
        }
    }

    // Email Settings
    if (class_exists('Database\Seeders\EmailSettingsSeeder')) {
        try {
            Artisan::call('db:seed', ['--class' => 'EmailSettingsSeeder', '--force' => true]);
            $results[] = '✓ Email settings seeded';
        } catch (Exception $e) {
            // Silently ignore if seeder doesn't exist
        }
    }

    // =========================================================================
    // STEP 4: Fix Content Statuses
    // =========================================================================
    $results[] = '→ Fixing content statuses...';

    // Publish all services
    if (Schema::hasTable('services')) {
        $updated = DB::table('services')
            ->where('status', '!=', 'published')
            ->update(['status' => 'published']);
        if ($updated > 0) {
            $results[] = "✓ Published {$updated} services";
        }
    }

    // Publish all posts and set published_at
    if (Schema::hasTable('posts')) {
        $updated = DB::table('posts')
            ->where('status', '!=', 'published')
            ->update([
                'status' => 'published',
                'published_at' => DB::raw('COALESCE(published_at, NOW())'),
            ]);
        if ($updated > 0) {
            $results[] = "✓ Published {$updated} posts";
        }
        
        // Fix posts without published_at
        DB::table('posts')
            ->where('status', 'published')
            ->whereNull('published_at')
            ->update(['published_at' => now()]);
    }

    // Activate all categories
    if (Schema::hasTable('categories')) {
        $updated = DB::table('categories')
            ->where('status', '!=', 'active')
            ->update(['status' => 'active']);
        if ($updated > 0) {
            $results[] = "✓ Activated {$updated} categories";
        }
    }

    // Publish all testimonials
    if (Schema::hasTable('testimonials')) {
        $updated = DB::table('testimonials')
            ->where('status', '!=', 'published')
            ->update(['status' => 'published']);
        if ($updated > 0) {
            $results[] = "✓ Published {$updated} testimonials";
        }
    }

    // Activate all brands
    if (Schema::hasTable('brands')) {
        $updated = DB::table('brands')
            ->where('status', '!=', 'active')
            ->update(['status' => 'active']);
        if ($updated > 0) {
            $results[] = "✓ Activated {$updated} brands";
        }
    }

    // Publish all case studies
    if (Schema::hasTable('case_studies')) {
        $updated = DB::table('case_studies')
            ->where('status', '!=', 'published')
            ->update([
                'status' => 'published',
                'publish_date' => DB::raw('COALESCE(publish_date, CURDATE())'),
            ]);
        if ($updated > 0) {
            $results[] = "✓ Published {$updated} case studies";
        }
    }

    // =========================================================================
    // STEP 5: Fix Email Settings (null values)
    // =========================================================================
    if (Schema::hasTable('email_settings') && DB::table('email_settings')->count() > 0) {
        DB::table('email_settings')->whereNull('from_name')->update(['from_name' => 'Smatatech']);
        DB::table('email_settings')->whereNull('from_email')->update(['from_email' => 'noreply@smatatech.com.ng']);
        DB::table('email_settings')->whereNull('smtp_port')->update(['smtp_port' => 587]);
        DB::table('email_settings')->whereNull('smtp_encryption')->update(['smtp_encryption' => 'tls']);
        $results[] = '✓ Email settings defaults applied';
    }

    // Fix Brevo Config
    if (Schema::hasTable('brevo_config') && DB::table('brevo_config')->count() > 0) {
        DB::table('brevo_config')->whereNull('sender_name')->update(['sender_name' => 'Smatatech']);
        DB::table('brevo_config')->whereNull('sender_email')->update(['sender_email' => 'noreply@smatatech.com.ng']);
        DB::table('brevo_config')->whereNull('is_enabled')->update(['is_enabled' => false]);
        $results[] = '✓ Brevo config defaults applied';
    }

    // =========================================================================
    // STEP 6: Create Email Templates if empty
    // =========================================================================
    if (Schema::hasTable('email_templates') && DB::table('email_templates')->count() === 0) {
        $templates = [
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Welcome Email',
                'slug' => 'welcome',
                'subject' => 'Welcome to Smatatech!',
                'body' => '<h1>Welcome!</h1><p>Thank you for registering.</p>',
                'variables' => json_encode(['user_name', 'site_name']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Password Reset',
                'slug' => 'password-reset',
                'subject' => 'Reset Your Password',
                'body' => '<h1>Password Reset</h1><p>Click the link to reset: {{reset_url}}</p>',
                'variables' => json_encode(['user_name', 'reset_url']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => Str::uuid()->toString(),
                'name' => 'Contact Notification',
                'slug' => 'contact-notification',
                'subject' => 'New Contact Message',
                'body' => '<h1>New Contact</h1><p>From: {{sender_name}}</p><p>{{message}}</p>',
                'variables' => json_encode(['sender_name', 'sender_email', 'message']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($templates as $template) {
            DB::table('email_templates')->insert($template);
        }
        $results[] = '✓ Email templates created';
    }

    // =========================================================================
    // STEP 7: Clear Caches
    // =========================================================================
    $results[] = '→ Clearing caches...';
    
    Cache::flush();
    
    try {
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('cache:clear');
        $results[] = '✓ All caches cleared';
    } catch (Exception $e) {
        $results[] = '⚠ Some caches could not be cleared: ' . $e->getMessage();
    }

    // Try to re-cache
    try {
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        $results[] = '✓ Configuration re-cached';
    } catch (Exception $e) {
        $results[] = '⚠ Could not re-cache (this is OK on some hosts)';
    }

    // =========================================================================
    // STEP 8: Storage Link
    // =========================================================================
    if (!file_exists(public_path('storage'))) {
        try {
            Artisan::call('storage:link');
            $results[] = '✓ Storage link created';
        } catch (Exception $e) {
            $results[] = '⚠ Could not create storage link';
        }
    }

    // =========================================================================
    // FINAL: Summary
    // =========================================================================
    $counts = [
        'admins' => Schema::hasTable('admins') ? DB::table('admins')->count() : 0,
        'services' => Schema::hasTable('services') ? DB::table('services')->where('status', 'published')->count() : 0,
        'posts' => Schema::hasTable('posts') ? DB::table('posts')->where('status', 'published')->count() : 0,
        'categories' => Schema::hasTable('categories') ? DB::table('categories')->where('status', 'active')->count() : 0,
        'testimonials' => Schema::hasTable('testimonials') ? DB::table('testimonials')->where('status', 'published')->count() : 0,
        'brands' => Schema::hasTable('brands') ? DB::table('brands')->where('status', 'active')->count() : 0,
        'case_studies' => Schema::hasTable('case_studies') ? DB::table('case_studies')->where('status', 'published')->count() : 0,
        'newsletter_subscribers' => Schema::hasTable('newsletter_subscribers') ? DB::table('newsletter_subscribers')->count() : 0,
        'service_inquiries' => Schema::hasTable('service_inquiries') ? DB::table('service_inquiries')->count() : 0,
        'chatbot_conversations' => Schema::hasTable('chatbot_conversations') ? DB::table('chatbot_conversations')->count() : 0,
    ];

    // Check for new tables
    $newTables = [
        'newsletter_subscribers' => Schema::hasTable('newsletter_subscribers'),
        'service_inquiries' => Schema::hasTable('service_inquiries'),
        'chatbot_conversations' => Schema::hasTable('chatbot_conversations'),
    ];

    echo json_encode([
        'success' => count($errors) === 0,
        'message' => count($errors) === 0 ? 'Migration and setup completed successfully!' : 'Completed with some errors',
        'results' => $results,
        'errors' => $errors,
        'database_counts' => $counts,
        'new_tables_created' => $newTables,
        'test_endpoints' => [
            'api_index' => url('/api'),
            'api_docs' => url('/api/docs'),
            'services' => url('/api/services'),
            'posts' => url('/api/posts'),
            'case_studies' => url('/api/case-studies'),
            'testimonials' => url('/api/testimonials'),
            'brands' => url('/api/brands'),
            'settings' => url('/api/settings'),
            'chatbot_config' => url('/api/chatbot/config'),
        ],
        'admin_login' => [
            'url' => url('/api/admin/login'),
            'credentials' => 'admin@smatatech.com.ng / Admin@123456 OR admin@smatatech.com / password',
        ],
        'next_steps' => [
            '1. Test the endpoints above',
            '2. DELETE this migrate.php file immediately!',
            '3. Also delete: setup.php, debug.php, fix.php, fix-posts.php',
            '4. Change admin password after first login',
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Setup failed',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'results' => $results,
        'troubleshooting' => [
            '1. Check .env file has correct database credentials',
            '2. Ensure database exists in cPanel MySQL Databases',
            '3. Ensure database user has all privileges',
            '4. Check storage/ and bootstrap/cache/ are writable (755)',
        ],
    ], JSON_PRETTY_PRINT);
}
