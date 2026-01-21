<?php
/**
 * Fix Script - Update statuses and clear cache
 * Access: https://api.smatatech.com.ng/fix.php?token=setup123
 * DELETE THIS FILE AFTER USE!
 */

$setupToken = 'setup123';
if (!isset($_GET['token']) || $_GET['token'] !== $setupToken) {
    http_response_code(403);
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Invalid token. Use ?token=setup123']));
}

header('Content-Type: application/json');

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

$results = [];

try {
    // 1. Update all services to published
    $servicesUpdated = DB::table('services')
        ->where('status', '!=', 'published')
        ->update(['status' => 'published']);
    $results[] = "✓ Updated {$servicesUpdated} services to 'published'";

    // 2. Update all posts to published (and set published_at if null)
    $postsUpdated = DB::table('posts')
        ->where('status', '!=', 'published')
        ->update([
            'status' => 'published',
            'published_at' => DB::raw('COALESCE(published_at, NOW())')
        ]);
    $results[] = "✓ Updated {$postsUpdated} posts to 'published'";

    // 3. Update all categories to active
    $categoriesUpdated = DB::table('categories')
        ->where('status', '!=', 'active')
        ->update(['status' => 'active']);
    $results[] = "✓ Updated {$categoriesUpdated} categories to 'active'";

    // 4. Update all testimonials to published
    $testimonialsUpdated = DB::table('testimonials')
        ->where('status', '!=', 'published')
        ->update(['status' => 'published']);
    $results[] = "✓ Updated {$testimonialsUpdated} testimonials to 'published'";

    // 5. Update all brands to active
    $brandsUpdated = DB::table('brands')
        ->where('status', '!=', 'active')
        ->update(['status' => 'active']);
    $results[] = "✓ Updated {$brandsUpdated} brands to 'active'";

    // 6. Update all case studies to published
    $caseStudiesUpdated = DB::table('case_studies')
        ->where('status', '!=', 'published')
        ->update([
            'status' => 'published',
            'publish_date' => DB::raw('COALESCE(publish_date, NOW())')
        ]);
    $results[] = "✓ Updated {$caseStudiesUpdated} case studies to 'published'";

    // 7. Clear all cache
    Cache::flush();
    $results[] = "✓ Cache flushed";

    // 8. Clear Laravel caches
    try {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        $results[] = "✓ Laravel caches cleared";
    } catch (Exception $e) {
        $results[] = "⚠ Could not clear some caches: " . $e->getMessage();
    }

    // 9. Try to re-cache config
    try {
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        $results[] = "✓ Configuration re-cached";
    } catch (Exception $e) {
        $results[] = "⚠ Could not re-cache config (this is OK)";
    }

    // 10. Get final counts
    $counts = [
        'services' => DB::table('services')->where('status', 'published')->count(),
        'posts' => DB::table('posts')->where('status', 'published')->count(),
        'categories' => DB::table('categories')->where('status', 'active')->count(),
        'testimonials' => DB::table('testimonials')->where('status', 'published')->count(),
        'brands' => DB::table('brands')->where('status', 'active')->count(),
        'case_studies' => DB::table('case_studies')->where('status', 'published')->count(),
    ];

    echo json_encode([
        'success' => true,
        'message' => 'All fixes applied successfully!',
        'results' => $results,
        'published_counts' => $counts,
        'test_endpoints' => [
            'services' => url('/api/services'),
            'posts' => url('/api/posts'),
            'categories' => url('/api/categories'),
            'testimonials' => url('/api/testimonials'),
            'brands' => url('/api/brands'),
            'case_studies' => url('/api/case-studies'),
        ],
        'next_steps' => [
            '1. Test the endpoints above',
            '2. DELETE this fix.php file',
            '3. DELETE setup.php and debug.php if still present',
        ]
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'results' => $results
    ], JSON_PRETTY_PRINT);
}
