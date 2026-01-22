<?php

use App\Http\Controllers\Api\ApiMetaController;
use App\Http\Controllers\Api\SwaggerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
| Route Files:
| - admin.php: Admin panel routes (/admin/*)
| - auth.php: Public user authentication (/auth/*)
| - public.php: Public website data routes
| - ai.php: AI tools routes (/ai/*)
|
| Versioning:
| - /api/v1/* - Version 1 endpoints
| - /api/v2/* - Version 2 endpoints (when ready)
| - Unversioned routes (/api/*) default to v1
|
*/

// =============================================================================
// Versioned API Routes
// =============================================================================

// Define a function to register versioned routes
$registerVersionedRoutes = function (string $version) {
    // Versioned Meta endpoints
    Route::prefix('meta')->group(function () use ($version) {
        Route::get('/', [ApiMetaController::class, 'spec'])->defaults('version', $version);
        Route::get('/endpoints', [ApiMetaController::class, 'endpoints'])->defaults('version', $version);
        Route::get('/openapi', [ApiMetaController::class, 'openapi'])->defaults('version', $version);
    });

    // Versioned Swagger UI
    Route::get('/swagger', [SwaggerController::class, 'index'])->defaults('version', $version);

    // Versioned Admin Routes
    Route::prefix('admin')->group(base_path('routes/api/admin.php'));

    // Versioned Public User Auth Routes
    Route::prefix('auth')->group(base_path('routes/api/auth.php'));

    // Versioned AI Tools Routes
    Route::prefix('ai')->group(base_path('routes/api/ai.php'));
};

// Register v1 routes
Route::prefix('v1')->group(function () use ($registerVersionedRoutes) {
    $registerVersionedRoutes('v1');
    
    // Include public routes (services, posts, etc.)
    require base_path('routes/api/public-versioned.php');
});

// Register v2 routes (when ready, uncomment and customize)
// Route::prefix('v2')->group(function () use ($registerVersionedRoutes) {
//     $registerVersionedRoutes('v2');
//     require base_path('routes/api/public-versioned.php');
// });

// =============================================================================
// Non-versioned routes (backward compatibility - defaults to v1)
// =============================================================================

// Admin Routes
Route::prefix('admin')->group(base_path('routes/api/admin.php'));

// Public User Auth Routes
Route::prefix('auth')->group(base_path('routes/api/auth.php'));

// AI Tools Routes
Route::prefix('ai')->group(base_path('routes/api/ai.php'));

// Public Website Routes (no prefix)
require base_path('routes/api/public.php');