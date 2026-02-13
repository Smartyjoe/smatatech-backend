<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider within a group
| which is assigned the "api" middleware group.
|
*/

// API v1 Routes
Route::prefix('v1')->group(function () {
    
    // Public routes (no authentication required)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [App\Http\Controllers\Auth\AuthController::class, 'register']);
        Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login']);
        Route::post('/forgot-password', [App\Http\Controllers\Auth\AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [App\Http\Controllers\Auth\AuthController::class, 'resetPassword']);
    });
    
    // Admin authentication routes
    Route::prefix('admin')->group(function () {
        Route::post('/login', [App\Http\Controllers\Auth\AdminAuthController::class, 'login']);
    });
    
    // Public API endpoints
    Route::get('/brands', [App\Http\Controllers\Public\BrandController::class, 'index']);
    Route::get('/services', [App\Http\Controllers\Public\ServiceController::class, 'index']);
    Route::get('/services/{slug}', [App\Http\Controllers\Public\ServiceController::class, 'show']);
    Route::get('/case-studies', [App\Http\Controllers\Public\CaseStudyController::class, 'index']);
    Route::get('/case-studies/{slug}', [App\Http\Controllers\Public\CaseStudyController::class, 'show']);
    Route::get('/testimonials', [App\Http\Controllers\Public\TestimonialController::class, 'index']);
    Route::get('/posts', [App\Http\Controllers\Public\BlogController::class, 'index']);
    Route::get('/posts/{slug}', [App\Http\Controllers\Public\BlogController::class, 'show']);
    Route::post('/posts/{slug}/comments', [App\Http\Controllers\Public\BlogController::class, 'addComment']);
    Route::get('/categories', [App\Http\Controllers\Public\BlogController::class, 'categories']);
    Route::get('/settings', [App\Http\Controllers\Public\SettingsController::class, 'index']);
    Route::get('/chatbot/config', [App\Http\Controllers\Public\ChatbotController::class, 'config']);
    Route::post('/chatbot/message', [App\Http\Controllers\Public\ChatbotController::class, 'message']);
    Route::post('/contact', [App\Http\Controllers\Public\ContactController::class, 'store']);
    
    // Protected user routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout']);
        Route::get('/auth/me', [App\Http\Controllers\Auth\AuthController::class, 'me']);
        Route::post('/auth/refresh', [App\Http\Controllers\Auth\AuthController::class, 'refresh']);
    });
    
    // Protected admin routes
    Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
        Route::post('/logout', [App\Http\Controllers\Auth\AdminAuthController::class, 'logout']);
        Route::get('/me', [App\Http\Controllers\Auth\AdminAuthController::class, 'me']);
        Route::post('/refresh', [App\Http\Controllers\Auth\AdminAuthController::class, 'refresh']);
        Route::put('/profile', [App\Http\Controllers\Auth\AdminAuthController::class, 'updateProfile']);
        Route::put('/profile/password', [App\Http\Controllers\Auth\AdminAuthController::class, 'updatePassword']);
        
        // Dashboard
        Route::get('/dashboard/stats', [App\Http\Controllers\Admin\DashboardController::class, 'stats']);
        Route::get('/dashboard/activity', [App\Http\Controllers\Admin\DashboardController::class, 'activity']);
        
        // Brands
        Route::apiResource('brands', App\Http\Controllers\Admin\BrandController::class);
        
        // Services
        Route::apiResource('services', App\Http\Controllers\Admin\ServiceController::class);
        
        // Case Studies
        Route::apiResource('case-studies', App\Http\Controllers\Admin\CaseStudyController::class);
        
        // Testimonials
        Route::apiResource('testimonials', App\Http\Controllers\Admin\TestimonialController::class);
        
        // Blog
        Route::apiResource('posts', App\Http\Controllers\Admin\BlogPostController::class);
        Route::apiResource('categories', App\Http\Controllers\Admin\BlogCategoryController::class);
        Route::apiResource('comments', App\Http\Controllers\Admin\BlogCommentController::class)->only(['index', 'update', 'destroy']);
        
        // Users
        Route::apiResource('users', App\Http\Controllers\Admin\UserController::class);
        
        // Contacts
        Route::apiResource('contacts', App\Http\Controllers\Admin\ContactController::class)->only(['index', 'update', 'destroy']);
        
        // Email
        Route::prefix('email')->group(function () {
            Route::get('/settings', [App\Http\Controllers\Admin\EmailController::class, 'getSettings']);
            Route::put('/settings', [App\Http\Controllers\Admin\EmailController::class, 'updateSettings']);
            Route::get('/templates', [App\Http\Controllers\Admin\EmailController::class, 'getTemplates']);
            Route::post('/templates', [App\Http\Controllers\Admin\EmailController::class, 'createTemplate']);
            Route::put('/templates/{id}', [App\Http\Controllers\Admin\EmailController::class, 'updateTemplate']);
            Route::delete('/templates/{id}', [App\Http\Controllers\Admin\EmailController::class, 'deleteTemplate']);
            Route::post('/test', [App\Http\Controllers\Admin\EmailController::class, 'testEmail']);
        });
        
        // Settings
        Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index']);
        Route::put('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update']);
        
        // Chatbot
        Route::prefix('chatbot')->group(function () {
            Route::get('/config', [App\Http\Controllers\Admin\ChatbotController::class, 'getConfig']);
            Route::put('/config', [App\Http\Controllers\Admin\ChatbotController::class, 'updateConfig']);
            Route::post('/toggle', [App\Http\Controllers\Admin\ChatbotController::class, 'toggle']);
            Route::get('/training', [App\Http\Controllers\Admin\ChatbotController::class, 'getTraining']);
            Route::post('/training', [App\Http\Controllers\Admin\ChatbotController::class, 'addTraining']);
            Route::put('/training/{id}', [App\Http\Controllers\Admin\ChatbotController::class, 'updateTraining']);
            Route::delete('/training/{id}', [App\Http\Controllers\Admin\ChatbotController::class, 'deleteTraining']);
        });
        
        // File Upload
        Route::post('/upload', [App\Http\Controllers\Admin\UploadController::class, 'upload']);

        // AI Blog Generator
        Route::post('/ai/blog-generate', [App\Http\Controllers\Admin\AiBlogController::class, 'generate']);
        Route::post('/ai/service-generate', [App\Http\Controllers\Admin\AiContentController::class, 'generateService']);
        Route::post('/ai/case-study-generate', [App\Http\Controllers\Admin\AiContentController::class, 'generateCaseStudy']);
    });
});
