<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Livewire\Admin\DashboardOverview;
use App\Livewire\Admin\IncomingQueue;
use App\Livewire\Admin\OrderTracking;
use App\Livewire\Admin\PaymentConfirmationQueue;
use App\Livewire\Admin\QuoteBuilder;
use App\Livewire\Admin\QuoteBuilderIndex;
use App\Livewire\Admin\SettingsPage;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login']);
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/', DashboardOverview::class)->name('dashboard');
        Route::get('/incoming', IncomingQueue::class)->name('incoming');
        Route::get('/quote', QuoteBuilderIndex::class)->name('quote');
        Route::get('/orders/{order}/quote', QuoteBuilder::class)->name('orders.quote');
        Route::get('/payments', PaymentConfirmationQueue::class)->name('payments');
        Route::get('/tracking', OrderTracking::class)->name('tracking');
        Route::get('/settings', SettingsPage::class)->name('settings');
    });
});
