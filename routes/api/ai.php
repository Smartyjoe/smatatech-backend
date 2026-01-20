<?php

use App\Http\Controllers\Api\AiToolsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AI Tools API Routes
|--------------------------------------------------------------------------
|
| Routes prefixed with /ai for AI tools functionality.
| All routes require user authentication.
|
*/

Route::middleware(['auth.user'])->group(function () {
    // AI Tools listing
    Route::get('/tools', [AiToolsController::class, 'index']);
    
    // Credits
    Route::get('/credits', [AiToolsController::class, 'credits']);
    Route::post('/credits/purchase', [AiToolsController::class, 'purchaseCredits']);
    
    // Usage history
    Route::get('/usage', [AiToolsController::class, 'usage']);
    
    // Execute AI tool
    Route::post('/tools/{id}/execute', [AiToolsController::class, 'execute']);
});
