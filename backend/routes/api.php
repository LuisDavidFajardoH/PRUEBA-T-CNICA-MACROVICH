<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\WeatherController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => config('app.version', '1.0.0')
    ]);
});

// Weather service health check (public)
Route::get('/weather/health', [WeatherController::class, 'health']);

// Public weather endpoints for testing
Route::prefix('weather/public')->group(function () {
    Route::get('/current', [WeatherController::class, 'current']);
    Route::get('/forecast', [WeatherController::class, 'forecast']);
    Route::get('/search', [WeatherController::class, 'searchLocations']);
});

// Authentication routes (public)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Authentication routes (protected)
Route::middleware('auth:sanctum')->group(function () {
    
    // User auth routes
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::patch('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/sessions', [AuthController::class, 'sessions']);
        Route::delete('/sessions/{tokenId}', [AuthController::class, 'revokeSession']);
    });

    // User info route
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Chat routes with specific rate limiting
    Route::prefix('chat')->middleware('throttle:chat')->group(function () {
        Route::get('/conversations', [ChatController::class, 'conversations']);
        Route::post('/conversations', [ChatController::class, 'createConversation']);
        Route::get('/conversations/{conversationId}', [ChatController::class, 'getConversation']);
        Route::post('/conversations/{conversationId}/messages', [ChatController::class, 'sendMessage']);
        Route::delete('/conversations/{conversationId}', [ChatController::class, 'deleteConversation']);
        Route::patch('/conversations/{conversationId}/archive', [ChatController::class, 'archiveConversation']);
        Route::get('/conversations/{conversationId}/stats', [ChatController::class, 'getConversationStats']);
        Route::get('/messages/search', [ChatController::class, 'searchMessages']);
        Route::get('/messages/recent', [ChatController::class, 'recentMessages']);
    });

    // Weather routes with specific rate limiting
    Route::prefix('weather')->middleware('throttle:weather')->group(function () {
        Route::get('/current', [WeatherController::class, 'current']);
        Route::get('/forecast', [WeatherController::class, 'forecast']);
        Route::get('/search', [WeatherController::class, 'searchLocations']);
        Route::get('/stats', [WeatherController::class, 'stats']);
        Route::delete('/cache', [WeatherController::class, 'clearCache']);
    });
});

// Fallback route for API
Route::fallback(function () {
    return response()->json([
        'message' => 'API endpoint not found',
        'error' => 'route_not_found'
    ], 404);
});
