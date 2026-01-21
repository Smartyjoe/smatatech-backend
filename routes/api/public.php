<?php

use App\Http\Controllers\Api\ApiDocumentationController;
use App\Http\Controllers\Api\Public\PublicController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Public Website API Routes
|--------------------------------------------------------------------------
|
| Publicly accessible routes for the frontend website.
| These routes do not require authentication.
|
*/

// Root API Endpoint - Comprehensive API Index
Route::get('/', [ApiDocumentationController::class, 'index']);

// Full API Documentation
Route::get('/docs', [ApiDocumentationController::class, 'docs']);

// Health Check Endpoint (no rate limiting for monitoring)
Route::get('/health', function () {
    $status = [
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'app' => config('app.name'),
        'environment' => app()->environment(),
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
    ];

    // Check database connection
    try {
        DB::connection()->getPdo();
        $status['database'] = 'connected';
    } catch (\Exception $e) {
        $status['database'] = 'disconnected';
        $status['status'] = 'degraded';
    }

    // Check storage writable
    $status['storage_writable'] = is_writable(storage_path('logs'));

    $httpStatus = $status['status'] === 'ok' ? 200 : 503;

    return response()->json($status, $httpStatus);
});

Route::middleware('throttle:api')->group(function () {
    // Services
    Route::get('/services', [PublicController::class, 'services']);
    Route::get('/services/{slug}', [PublicController::class, 'service']);

    // Case Studies
    Route::get('/case-studies', [PublicController::class, 'caseStudies']);
    Route::get('/case-studies/{slug}', [PublicController::class, 'caseStudy']);

    // Testimonials
    Route::get('/testimonials', [PublicController::class, 'testimonials']);

    // Blog Posts
    Route::get('/posts', [PublicController::class, 'posts']);
    Route::get('/posts/{slug}', [PublicController::class, 'post']);

    // Categories
    Route::get('/categories', [PublicController::class, 'categories']);

    // Brands
    Route::get('/brands', [PublicController::class, 'brands']);

    // Site Settings
    Route::get('/settings', [PublicController::class, 'settings']);

    // Contact Form (rate limited more strictly)
    Route::middleware('throttle:contact')->group(function () {
        Route::post('/contact', [PublicController::class, 'submitContact']);
    });
});
