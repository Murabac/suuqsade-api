<?php

namespace App\Livewire\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\SettingsService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Payment Confirmation')]
class PaymentConfirmationQueue extends Component
{
    public string $search = '';

    public string $filter = 'awaiting';

    public ?string $message = null;

    public function setFilter(string $filter): void
    {
        $this->filter = in_array($filter, ['awaiting', 'confirm'], true) ? $filter : 'awaiting';
    }

    public function cancel(int $orderId, OrderService $orders): void
    {
        $order = Order::query()->findOrFail($orderId);

        if ($order->status !== OrderStatus::Quoted) {
            $this->message = 'Only quoted orders awaiting payment can be cancelled here.';

            return;
        }

        $orders->cancelOrder($order, auth('admin')->user());
        $this->message = \App\Support\AdminUi::orderRef($order).' cancelled.';
    }

    public function confirm(int $orderId, OrderService $orders): void
    {
        $order = Order::query()->findOrFail($orderId);

        if ($order->status !== OrderStatus::PaymentPending) {
            $this->message = 'Order is no longer awaiting payment confirmation.';

            return;
        }

        $orders->confirmPayment($order, auth('admin')->user());
        $this->message = 'Payment confirmed for '.\App\Support\AdminUi::orderRef($order).'.';
    }

    public function render(SettingsService $settings)
    {
        $confirmMinutes = (int) $settings->get('payment_confirm_minutes', '30');

        $status = $this->filter === 'confirm'
            ? OrderStatus::PaymentPending
            : OrderStatus::Quoted;

        $orders = Order::query()
            ->with(['user', 'payments'])
            ->where('status', $status)
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($q) use ($term) {
                    $q->whereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('phone_number', 'like', $term))
                        ->orWhere('id', 'like', $term);
                });
            })
            ->latest()
            ->get();

        return view('livewire.admin.payment-confirmation-queue', [
            'orders' => $orders,
            'confirmMinutes' => $confirmMinutes,
            'awaitingCount' => Order::query()->where('status', OrderStatus::Quoted)->count(),
            'confirmCount' => Order::query()->where('status', OrderStatus::PaymentPending)->count(),
        ]);
    }
}
