<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BackupController;
use App\Http\Controllers\GoogleAuthController;

Route::post('/signup', [AuthController::class, 'signup']); // Register a new user and return token
Route::post('/signin', [AuthController::class, 'signin']); // Authenticate user and return token
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware('signed')->name('verify.email'); // Verify email address
Route::post('/email/verify/resend', [AuthController::class, 'resendVerificationMail'])->middleware('throttle:60,1'); // Resend verification email
Route::post('/password/forgot', [AuthController::class, 'sendResetPasswordMail']); // Send password reset link
Route::post('/password/reset', [AuthController::class, 'setNewPassword'])->name('reset.password'); // Reset password using token


// Google OAuth routes
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback']);

// Group protected routes under auth:sanctum middleware
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/signout', [AuthController::class, 'signout']);
    Route::patch('/token/refresh', [AuthController::class, 'refreshToken']);
    Route::get('/verify/account', [AuthController::class, 'verifyAccount']);
    Route::patch('/password/change', [AuthController::class, 'changePassword']);
    Route::patch('/password/create', [AuthController::class, 'createPassword']);
    // ... existing routes
    Route::patch('/update/photo', [AuthController::class, 'updateUserPhoto']);


    // Backup routes
    Route::prefix('backups')->group(function () {
        Route::get('/', [BackupController::class, 'getBackups']);
        Route::post('/create', [BackupController::class, 'createBackup']);
        Route::get('/download/{filename}', [BackupController::class, 'downloadBackup']);
        Route::delete('/delete/{filename}', [BackupController::class, 'deleteBackup']);
    });
});
