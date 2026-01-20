<?php

use App\Http\Controllers\Api\Public\PublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Website API Routes
|--------------------------------------------------------------------------
|
| Publicly accessible routes for the frontend website.
| These routes do not require authentication.
|
*/

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
    Route::middleware('throttle:api')->group(function () {
        Route::post('/contact', [PublicController::class, 'submitContact']);
    });
});
