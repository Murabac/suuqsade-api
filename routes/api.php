<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
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
    Route::post('/notifications/read-all', [UserController::class, 'markAllNotificationsRead']);
    Route::post('/notifications/{notification}/read', [UserController::class, 'markNotificationRead']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/batch', [OrderController::class, 'storeBatch']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::put('/orders/{order}/variant', [OrderController::class, 'updateVariant']);
    Route::post('/orders/{order}/payment-sent', [OrderController::class, 'paymentSent']);
});
