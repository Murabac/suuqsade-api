<?php

namespace App\Livewire\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Incoming Queue')]
class IncomingQueue extends Component
{
    public string $search = '';

    public ?string $message = null;

    public function cancel(int $orderId, OrderService $orders): void
    {
        $order = Order::query()->findOrFail($orderId);

        if ($order->status !== OrderStatus::Submitted) {
            $this->message = 'Only submitted orders can be cancelled.';

            return;
        }

        $orders->cancelOrder($order, auth('admin')->user());
        $this->message = \App\Support\AdminUi::orderRef($order).' cancelled.';
    }

    public function render()
    {
        $orders = Order::query()
            ->with('user')
            ->where('status', OrderStatus::Submitted)
            ->when($this->search !== '', function ($query) {
                $term = '%'.$this->search.'%';
                $query->where(function ($q) use ($term) {
                    $q->whereHas('user', fn ($u) => $u->where('name', 'like', $term))
                        ->orWhere('id', 'like', $term);
                });
            })
            ->latest()
            ->get();

        return view('livewire.admin.incoming-queue', [
            'orders' => $orders,
        ]);
    }
}
