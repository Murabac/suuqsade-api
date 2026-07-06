<?php

namespace App\Livewire\Admin;

use App\Enums\OrderStatus;
use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Incoming Queue')]
class IncomingQueue extends Component
{
    public string $search = '';

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
