<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AppSettingController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CaptchaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Prefix: /api/v1
| All routes in this file are versioned RESTful endpoints.
|
*/

// Public Store Settings
Route::get('/app/settings', [AppSettingController::class, 'index'])->name('app.settings');

// Captcha
Route::get('/captcha/generate', [CaptchaController::class, 'generate'])->name('captcha.generate');
Route::post('/captcha/solve', [CaptchaController::class, 'solve'])->name('captcha.solve');

// Customer Authentication
Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('/otp/request', [AuthController::class, 'requestOtp'])->name('otp.request');
    Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])->name('otp.verify');

    // Authenticated User Endpoints
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');
    });
});
