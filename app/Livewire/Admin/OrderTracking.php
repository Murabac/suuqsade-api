<?php

namespace App\Livewire\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Order Tracking')]
class OrderTracking extends Component
{
    public string $search = '';

    public string $filter = 'active';

    public ?int $editingOrderId = null;

    public ?string $trackingNote = null;

    public ?int $deliveringOrderId = null;

    public string $delivery_shipping_fee = '';

    public ?string $message = null;

    public function setFilter(string $filter): void
    {
        $this->filter = in_array($filter, ['active', 'delivered'], true) ? $filter : 'active';
    }

    public function startTrackingNote(int $orderId): void
    {
        $this->editingOrderId = $orderId;
        $order = Order::query()->findOrFail($orderId);
        $this->trackingNote = $order->tracking_note ?? '';
    }

    public function saveTrackingNote(): void
    {
        if (! $this->editingOrderId) {
            return;
        }

        Order::query()->whereKey($this->editingOrderId)->update([
            'tracking_note' => $this->trackingNote,
        ]);

        $this->message = 'Tracking note saved.';
        $this->editingOrderId = null;
        $this->trackingNote = null;
    }

    public function advance(int $orderId, OrderService $orders): void
    {
        $order = Order::query()->findOrFail($orderId);

        if ($order->status === OrderStatus::Shipped) {
            $this->startDelivery($orderId, $orders);

            return;
        }

        try {
            $orders->advanceTracking($order, auth('admin')->user());
            $this->message = \App\Support\AdminUi::orderRef($order).' updated.';
        } catch (\InvalidArgumentException $e) {
            $this->message = $e->getMessage();
        }
    }

    public function startDelivery(int $orderId, OrderService $orders): void
    {
        $this->deliveringOrderId = $orderId;
        $this->delivery_shipping_fee = number_format($orders->defaultShippingFee(), 2, '.', '');
    }

    public function cancelDelivery(): void
    {
        $this->deliveringOrderId = null;
        $this->delivery_shipping_fee = '';
    }

    public function confirmDelivery(OrderService $orders): void
    {
        if (! $this->deliveringOrderId) {
            return;
        }

        $this->validate([
            'delivery_shipping_fee' => ['required', 'numeric', 'min:0'],
        ]);

        $order = Order::query()->findOrFail($this->deliveringOrderId);

        try {
            $orders->markDelivered(
                $order,
                (float) $this->delivery_shipping_fee,
                auth('admin')->user(),
            );
            $this->message = \App\Support\AdminUi::orderRef($order).' delivered with shipping fee applied.';
            $this->deliveringOrderId = null;
            $this->delivery_shipping_fee = '';
        } catch (\InvalidArgumentException $e) {
            $this->message = $e->getMessage();
        }
    }

    public function render()
    {
        $statuses = $this->filter === 'delivered'
            ? [OrderStatus::Delivered]
            : [
                OrderStatus::PaymentConfirmed,
                OrderStatus::Ordered,
                OrderStatus::Shipped,
            ];

        $orders = Order::query()
            ->with('user')
            ->whereIn('status', $statuses)
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($q) use ($term) {
                    $q->whereHas('user', fn ($u) => $u->where('name', 'like', $term))
                        ->orWhere('id', 'like', $term);
                });
            })
            ->latest()
            ->get();

        return view('livewire.admin.order-tracking', [
            'orders' => $orders,
        ]);
    }
}
