<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API routes for future mobile app or external integrations
Route::prefix('v1')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        // User notifications API
        Route::get('/notifications', function (Request $request) {
            return response()->json([
                'notifications' => $request->user()->notifications()->latest()->take(20)->get(),
                'unread_count' => $request->user()->unreadNotifications()->count()
            ]);
        });
        
        // User progress API
        Route::get('/progress', function (Request $request) {
            return response()->json([
                'user' => $request->user(),
                'xp' => $request->user()->xp,
                'level' => $request->user()->level,
                'streak' => $request->user()->current_streak
            ]);
        });
    });
});