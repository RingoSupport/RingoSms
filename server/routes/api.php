<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WalletController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



Route::prefix('auth')->group(function () {

    // Public routes (no auth required)
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

    // Protected routes (require JWT auth)
    Route::middleware(['jwt.auth'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']); // optional: get current user info
        Route::post('/debit', [WalletController::class, 'debit']);
        Route::post('/credit', [WalletController::class, 'credit']);
        Route::get('/wallet/balance', [WalletController::class, 'fetchWalletBalance']);
        Route::get('/wallet/history', [WalletController::class, 'fetchWalletHistory']);
    });

});
