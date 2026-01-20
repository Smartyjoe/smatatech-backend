<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\BrandController;
use App\Http\Controllers\Api\Admin\CaseStudyController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ChatbotController;
use App\Http\Controllers\Api\Admin\CommentController;
use App\Http\Controllers\Api\Admin\ContactController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\EmailController;
use App\Http\Controllers\Api\Admin\PostController;
use App\Http\Controllers\Api\Admin\ServiceController;
use App\Http\Controllers\Api\Admin\SettingsController;
use App\Http\Controllers\Api\Admin\TestimonialController;
use App\Http\Controllers\Api\Admin\UploadController;
use App\Http\Controllers\Api\Admin\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
|
| Routes prefixed with /admin for admin panel functionality.
| All routes (except login) require admin authentication.
|
*/

// Admin Authentication (rate limited)
Route::middleware('throttle:api')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected Admin Routes
Route::middleware(['auth.admin'])->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Dashboard (Viewer+)
    Route::middleware('role:viewer')->group(function () {
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
        Route::get('/dashboard/activity', [DashboardController::class, 'activity']);
    });

    // User Management (Admin+)
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::post('/users/{id}/activate', [UserController::class, 'activate']);
        Route::post('/users/{id}/deactivate', [UserController::class, 'deactivate']);
    });

    // User Management - Super Admin Only
    Route::middleware('role:super_admin')->group(function () {
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::post('/users/{id}/role', [UserController::class, 'assignRole']);
    });

    // Blog Posts (Editor+)
    Route::middleware('role:editor')->group(function () {
        Route::get('/posts', [PostController::class, 'index']);
        Route::get('/posts/{id}', [PostController::class, 'show']);
        Route::post('/posts', [PostController::class, 'store']);
        Route::put('/posts/{id}', [PostController::class, 'update']);
        Route::post('/posts/{id}/publish', [PostController::class, 'publish']);
        Route::post('/posts/{id}/unpublish', [PostController::class, 'unpublish']);
    });

    // Blog Posts - Admin+ for delete
    Route::middleware('role:admin')->group(function () {
        Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    });

    // Categories (Editor+ for read, Admin+ for write)
    Route::middleware('role:editor')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/categories/{id}', [CategoryController::class, 'show']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    });

    // Comments (Editor+)
    Route::middleware('role:editor')->group(function () {
        Route::get('/comments', [CommentController::class, 'index']);
        Route::get('/comments/{id}', [CommentController::class, 'show']);
        Route::post('/comments/{id}/approve', [CommentController::class, 'approve']);
        Route::post('/comments/{id}/reject', [CommentController::class, 'reject']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
    });

    // Services (Editor+ for read/update, Admin+ for create/delete)
    Route::middleware('role:editor')->group(function () {
        Route::get('/services', [ServiceController::class, 'index']);
        Route::get('/services/{id}', [ServiceController::class, 'show']);
        Route::put('/services/{id}', [ServiceController::class, 'update']);
        Route::post('/services/reorder', [ServiceController::class, 'reorder']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::post('/services', [ServiceController::class, 'store']);
        Route::delete('/services/{id}', [ServiceController::class, 'destroy']);
    });

    // Case Studies (Editor+ for read/update, Admin+ for create/delete)
    Route::middleware('role:editor')->group(function () {
        Route::get('/case-studies', [CaseStudyController::class, 'index']);
        Route::get('/case-studies/{id}', [CaseStudyController::class, 'show']);
        Route::put('/case-studies/{id}', [CaseStudyController::class, 'update']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::post('/case-studies', [CaseStudyController::class, 'store']);
        Route::delete('/case-studies/{id}', [CaseStudyController::class, 'destroy']);
    });

    // Testimonials (Editor+ for read/update, Admin+ for create/delete)
    Route::middleware('role:editor')->group(function () {
        Route::get('/testimonials', [TestimonialController::class, 'index']);
        Route::get('/testimonials/{id}', [TestimonialController::class, 'show']);
        Route::put('/testimonials/{id}', [TestimonialController::class, 'update']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::post('/testimonials', [TestimonialController::class, 'store']);
        Route::delete('/testimonials/{id}', [TestimonialController::class, 'destroy']);
    });

    // Brands (Editor+ for read/update, Admin+ for create/delete)
    Route::middleware('role:editor')->group(function () {
        Route::get('/brands', [BrandController::class, 'index']);
        Route::get('/brands/{id}', [BrandController::class, 'show']);
        Route::put('/brands/{id}', [BrandController::class, 'update']);
        Route::post('/brands/reorder', [BrandController::class, 'reorder']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::post('/brands', [BrandController::class, 'store']);
        Route::delete('/brands/{id}', [BrandController::class, 'destroy']);
    });

    // Contacts (Admin+)
    Route::middleware('role:admin')->group(function () {
        Route::get('/contacts', [ContactController::class, 'index']);
        Route::get('/contacts/{id}', [ContactController::class, 'show']);
        Route::post('/contacts/{id}/read', [ContactController::class, 'markAsRead']);
        Route::post('/contacts/{id}/unread', [ContactController::class, 'markAsUnread']);
        Route::delete('/contacts/{id}', [ContactController::class, 'destroy']);
    });

    // Chatbot Configuration (Admin+)
    Route::middleware('role:admin')->group(function () {
        Route::get('/chatbot/config', [ChatbotController::class, 'getConfig']);
        Route::put('/chatbot/config', [ChatbotController::class, 'updateConfig']);
        Route::post('/chatbot/toggle', [ChatbotController::class, 'toggle']);
    });

    // Email Settings (Admin+)
    Route::middleware('role:admin')->group(function () {
        Route::get('/email/settings', [EmailController::class, 'getSettings']);
        Route::put('/email/settings', [EmailController::class, 'updateSettings']);
        Route::get('/email/templates', [EmailController::class, 'getTemplates']);
        Route::get('/email/templates/{id}', [EmailController::class, 'getTemplate']);
        Route::put('/email/templates/{id}', [EmailController::class, 'updateTemplate']);
    });

    // Brevo Config (Super Admin only)
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/email/brevo', [EmailController::class, 'getBrevoConfig']);
        Route::put('/email/brevo', [EmailController::class, 'updateBrevoConfig']);
        Route::post('/email/brevo/test', [EmailController::class, 'testBrevoConnection']);
    });

    // Site Settings (Admin+)
    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index']);
        Route::put('/settings', [SettingsController::class, 'update']);
    });

    // File Upload (Editor+, rate limited)
    Route::middleware(['role:editor', 'throttle:api'])->group(function () {
        Route::post('/upload', [UploadController::class, 'store']);
        Route::delete('/upload', [UploadController::class, 'destroy']);
    });
});
