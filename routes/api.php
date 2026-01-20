<?php

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
*/

// Admin Routes
Route::prefix('admin')->group(base_path('routes/api/admin.php'));

// Public User Auth Routes
Route::prefix('auth')->group(base_path('routes/api/auth.php'));

// AI Tools Routes
Route::prefix('ai')->group(base_path('routes/api/ai.php'));

// Public Website Routes (no prefix)
require base_path('routes/api/public.php');