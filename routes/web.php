<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\MistakeController;
use App\Http\Controllers\PaymentSettingsController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');

// Routes that require authentication
Route::middleware('auth')->group(function () {
    // Home route (authenticated)
    Route::get('/home', [HomeController::class, 'index'])->name('home.auth');
    
    // Question management routes (admin only)
    Route::middleware('admin')->group(function () {
        Route::get('/add-question', [QuestionController::class, 'showAddQuestionForm'])->name('questions.add');
        Route::post('/add-question', [QuestionController::class, 'storeQuestion'])->name('questions.store');
        Route::get('/edit-question/{id}', [QuestionController::class, 'editQuestion'])->name('questions.edit');
        Route::put('/edit-question/{id}', [QuestionController::class, 'updateQuestion'])->name('questions.update');
        Route::delete('/delete-question/{id}', [QuestionController::class, 'deleteQuestion'])->name('questions.delete');
    });
    
    // Mistake/Answer routes
    Route::post('/mistakes', [MistakeController::class, 'store'])->name('mistakes.store');
    Route::get('/mistakes', [MistakeController::class, 'index'])->name('mistakes.index');
    
    // Payment settings routes
    Route::get('/payment-settings', [PaymentSettingsController::class, 'index'])->name('payment.settings');
    Route::post('/payment-settings', [PaymentSettingsController::class, 'store'])->name('payment.settings.store');
    
    // Notification routes
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark_read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark_all_read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

// Include admin routes
require __DIR__.'/admin.php';

// Include auth routes
require __DIR__.'/auth.php';