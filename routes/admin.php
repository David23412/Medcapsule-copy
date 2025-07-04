<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\PaymentVerificationController;
use App\Http\Controllers\PaymentSettingsController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| These routes are protected by auth and admin middleware
| All routes require admin privileges to access
|
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    
    // Payment Verification Routes
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/pending', [PaymentVerificationController::class, 'pendingPayments'])->name('pending');
        Route::get('/all', [PaymentVerificationController::class, 'allPayments'])->name('all');
        Route::get('/{payment}', [PaymentVerificationController::class, 'showPayment'])->name('show');
        
        // Payment Actions
        Route::post('/{payment}/approve', [PaymentVerificationController::class, 'approvePayment'])->name('approve');
        Route::post('/{payment}/reject', [PaymentVerificationController::class, 'rejectPayment'])->name('reject');
        Route::post('/{payment}/request-info', [PaymentVerificationController::class, 'requestMoreInfo'])->name('request-info');
        
        // Bulk Actions
        Route::post('/bulk/approve', [PaymentVerificationController::class, 'bulkApprove'])->name('bulk-approve');
        Route::post('/bulk/reject', [PaymentVerificationController::class, 'bulkReject'])->name('bulk-reject');
    });
    
    // Payment Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/payments', [PaymentSettingsController::class, 'showPaymentSettingsForm'])->name('payments');
        Route::post('/payments', [PaymentSettingsController::class, 'updatePaymentSettings'])->name('payments.update');
    });
    
    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::post('/{user}/courses/{course}/enroll', [AdminController::class, 'manualEnrollment'])->name('enroll');
        Route::delete('/{user}/courses/{course}/unenroll', [AdminController::class, 'manualUnenrollment'])->name('unenroll');
    });
    
    // Dashboard and Analytics
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
});

// CSRF Protection for all admin routes
Route::middleware(['auth', 'admin'])->group(function () {
    // API-style routes for AJAX calls
    Route::prefix('api/admin')->name('api.admin.')->group(function () {
        Route::get('/payments/stats', [PaymentVerificationController::class, 'getPaymentStats']);
        Route::get('/payments/{payment}/receipt', [PaymentVerificationController::class, 'getReceiptImage']);
    });
});