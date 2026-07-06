<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h1>Order Tracking</h1>
            <p>{{ $orders->count() }} active orders in fulfillment</p>
        </div>
        <div class="admin-search-wrap">
            @include('components.admin.icons.search')
            <input type="text" class="admin-search" placeholder="Search orders…" wire:model.live.debounce.300ms="search">
        </div>
    </div>

    <div class="admin-page-body">
        @if ($message)
            <p class="admin-success-msg">{{ $message }}</p>
        @endif

        <div class="admin-table-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:7rem">Order ID</th>
                        <th style="width:10rem">Customer</th>
                        <th style="width:9rem">Status</th>
                        <th class="text-right" style="width:7rem">Amount</th>
                        <th>Tracking note</th>
                        <th style="width:10rem"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr wire:key="track-{{ $order->id }}">
                            <td><span class="admin-mono">{{ \App\Support\AdminUi::orderRef($order) }}</span></td>
                            <td><strong>{{ $order->user->name }}</strong></td>
                            <td><x-admin.status-badge :status="$order->status" /></td>
                            <td class="text-right"><span class="admin-amount">${{ number_format((float) $order->total_amount, 2) }}</span></td>
                            <td>
                                @if ($editingOrderId === $order->id)
                                    <div style="display:flex;gap:0.5rem;align-items:center">
                                        <input type="text" class="admin-note-input" wire:model="trackingNote" wire:keydown.enter="saveTrackingNote" placeholder="Add tracking note…" autofocus>
                                        <button type="button" class="admin-btn" wire:click="saveTrackingNote">Save</button>
                                    </div>
                                @else
                                    <button type="button" class="admin-note-btn" wire:click="startTrackingNote({{ $order->id }})" title="Click to edit note">
                                        {{ $order->tracking_note ?: 'Add note…' }}
                                    </button>
                                @endif
                            </td>
                            <td class="text-right">
                                @if ($order->status === \App\Enums\OrderStatus::PaymentConfirmed)
                                    <button type="button" class="admin-btn" wire:click="advance({{ $order->id }})">
                                        @include('components.admin.icons.arrow-right')
                                        Mark as ordered
                                    </button>
                                @elseif ($order->status === \App\Enums\OrderStatus::Ordered)
                                    <button type="button" class="admin-btn" wire:click="advance({{ $order->id }})">
                                        @include('components.admin.icons.arrow-right')
                                        Mark as shipped
                                    </button>
                                @elseif ($order->status === \App\Enums\OrderStatus::Shipped)
                                    <button type="button" class="admin-btn" wire:click="advance({{ $order->id }})">
                                        @include('components.admin.icons.arrow-right')
                                        Mark as delivered
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-empty">No orders in tracking.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
