<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);

Route::get('/settings/public', [SettingsController::class, 'public']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/user', [UserController::class, 'show']);
    Route::put('/user', [UserController::class, 'update']);
    Route::post('/user/fcm-token', [UserController::class, 'storeFcmToken']);

    Route::get('/notifications', [UserController::class, 'notifications']);
});
