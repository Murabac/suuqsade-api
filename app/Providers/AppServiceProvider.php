<?php

namespace App\Providers;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.admin', function ($view) {
            $view->with([
                'incomingCount' => Order::query()->where('status', OrderStatus::Submitted)->count(),
                'quotedCount' => Order::query()->where('status', OrderStatus::Quoted)->count(),
                'paymentCount' => Order::query()->where('status', OrderStatus::PaymentPending)->count(),
            ]);
        });
    }
}
