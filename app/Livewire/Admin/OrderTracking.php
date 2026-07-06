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

    public ?int $editingOrderId = null;

    public ?string $trackingNote = null;

    public ?string $message = null;

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

        try {
            $orders->advanceTracking($order, auth('admin')->user());
            $this->message = \App\Support\AdminUi::orderRef($order).' updated.';
        } catch (\InvalidArgumentException $e) {
            $this->message = $e->getMessage();
        }
    }

    public function render()
    {
        $orders = Order::query()
            ->with('user')
            ->whereIn('status', [
                OrderStatus::PaymentConfirmed,
                OrderStatus::Ordered,
                OrderStatus::Shipped,
            ])
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
