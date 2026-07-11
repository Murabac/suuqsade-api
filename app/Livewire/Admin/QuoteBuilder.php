<?php

namespace App\Livewire\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\OrderService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Quote Builder')]
class QuoteBuilder extends Component
{
    public Order $order;

    public string $item_cost = '';

    public string $service_fee_pct = '';

    public bool $sent = false;

    public function mount(Order $order, OrderService $orders): void
    {
        if ($order->status !== OrderStatus::Submitted) {
            abort(404);
        }

        $this->order = $order->load('user');
        $this->service_fee_pct = (string) $orders->defaultServiceFeePct();
    }

    public function submitQuote(OrderService $orders): void
    {
        $this->validate([
            'item_cost' => ['required', 'numeric', 'min:0.01'],
            'service_fee_pct' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $orders->applyQuote(
            $this->order,
            (float) $this->item_cost,
            (float) $this->service_fee_pct,
            auth('admin')->user(),
        );

        $this->sent = true;
        $this->redirect(route('admin.payments'), navigate: true);
    }

    public function getItemNumProperty(): float
    {
        return (float) ($this->item_cost ?: 0);
    }

    public function getFeeNumProperty(): float
    {
        return (float) ($this->service_fee_pct ?: 0);
    }

    public function getFeeAmountProperty(): float
    {
        return round($this->itemNum * ($this->feeNum / 100), 2);
    }

    public function getTotalProperty(): float
    {
        return round($this->itemNum + $this->feeAmount, 2);
    }

    public function render()
    {
        return view('livewire.admin.quote-builder');
    }
}
