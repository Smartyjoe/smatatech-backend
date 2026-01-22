<?php

use App\Http\Controllers\Api\Public\ChatController;
use App\Http\Controllers\Api\Public\InquiryController;
use App\Http\Controllers\Api\Public\NewsletterController;
use App\Http\Controllers\Api\Public\PublicController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Versioned Public API Routes
|--------------------------------------------------------------------------
|
| These routes are included within versioned route groups (v1, v2, etc.)
| They provide the core public API functionality.
|
*/

// Health Check Endpoint
Route::get('/health', function () {
    $status = [
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'app' => config('app.name'),
        'environment' => app()->environment(),
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
    ];

    try {
        DB::connection()->getPdo();
        $status['database'] = 'connected';
    } catch (\Exception $e) {
        $status['database'] = 'disconnected';
        $status['status'] = 'degraded';
    }

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
    Route::get('/case-studies/{slug}/related', [PublicController::class, 'relatedCaseStudies']);

    // Testimonials
    Route::get('/testimonials', [PublicController::class, 'testimonials']);

    // Blog Posts
    Route::get('/posts', [PublicController::class, 'posts']);
    Route::get('/posts/{slug}', [PublicController::class, 'post']);
    Route::get('/posts/{slug}/related', [PublicController::class, 'relatedPosts']);

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

    // Chatbot
    Route::get('/chatbot/config', [ChatController::class, 'config']);
    Route::post('/chat', [ChatController::class, 'chat']);

    // Newsletter
    Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);
    Route::post('/newsletter/unsubscribe', [NewsletterController::class, 'unsubscribe']);

    // Service Inquiries
    Route::post('/inquiries', [InquiryController::class, 'store']);
});
