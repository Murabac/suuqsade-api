<?php

namespace App\Livewire\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Dashboard')]
class DashboardOverview extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard-overview', [
            'incomingCount' => Order::query()->where('status', OrderStatus::Submitted)->count(),
            'quotedCount' => Order::query()->where('status', OrderStatus::Quoted)->count(),
            'paymentCount' => Order::query()->where('status', OrderStatus::PaymentPending)->count(),
            'trackingCount' => Order::query()->whereIn('status', [
                OrderStatus::PaymentConfirmed,
                OrderStatus::Ordered,
                OrderStatus::Shipped,
            ])->count(),
            'deliveredToday' => Order::query()
                ->where('status', OrderStatus::Delivered)
                ->whereDate('updated_at', today())
                ->count(),
        ]);
    }
}
