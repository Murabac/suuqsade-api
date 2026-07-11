<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h1>Order Tracking</h1>
            <p>{{ $orders->count() }} {{ $filter === 'delivered' ? 'delivered' : 'active' }} orders</p>
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

        <div class="admin-filter-tabs">
            <button type="button" class="admin-filter-tab @if($filter === 'active') active @endif" wire:click="setFilter('active')">Active</button>
            <button type="button" class="admin-filter-tab @if($filter === 'delivered') active @endif" wire:click="setFilter('delivered')">Delivered</button>
        </div>

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
                            <td class="text-right">
                                @if ($order->shipping_fee !== null)
                                    <span class="admin-amount">${{ number_format((float) $order->item_cost + ((float) $order->item_cost * (float) $order->service_fee_pct / 100) + (float) $order->shipping_fee, 2) }}</span>
                                @else
                                    <span class="admin-amount">${{ number_format((float) $order->total_amount, 2) }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($editingOrderId === $order->id)
                                    <div style="display:flex;gap:0.5rem;align-items:center">
                                        <input type="text" class="admin-note-input" wire:model="trackingNote" wire:keydown.enter="saveTrackingNote" placeholder="Add tracking note…" autofocus>
                                        <button type="button" class="admin-btn admin-btn-sm" wire:click="saveTrackingNote" wire:loading.attr="disabled">Save</button>
                                    </div>
                                @else
                                    <button type="button" class="admin-note-btn" wire:click="startTrackingNote({{ $order->id }})" title="Click to edit note">
                                        {{ $order->tracking_note ?: 'Add note…' }}
                                    </button>
                                @endif
                            </td>
                            <td class="text-right">
                                @if ($order->status === \App\Enums\OrderStatus::Delivered)
                                    <span class="admin-delivered-label">
                                        @include('components.admin.icons.check-circle')
                                        Delivered
                                    </span>
                                @elseif ($order->status === \App\Enums\OrderStatus::PaymentConfirmed)
                                    <button type="button" class="admin-btn" wire:click="advance({{ $order->id }})" wire:loading.attr="disabled" wire:target="advance({{ $order->id }})">
                                        @include('components.admin.icons.arrow-right')
                                        Mark as ordered
                                    </button>
                                @elseif ($order->status === \App\Enums\OrderStatus::Ordered)
                                    <button type="button" class="admin-btn" wire:click="advance({{ $order->id }})" wire:loading.attr="disabled" wire:target="advance({{ $order->id }})">
                                        @include('components.admin.icons.arrow-right')
                                        Mark as shipped
                                    </button>
                                @elseif ($order->status === \App\Enums\OrderStatus::Shipped)
                                    @if ($deliveringOrderId === $order->id)
                                        <div style="display:flex;gap:0.35rem;align-items:center;justify-content:flex-end;flex-wrap:wrap">
                                            <div class="admin-input-wrap" style="width:6rem">
                                                <span class="admin-input-prefix">$</span>
                                                <input type="number" step="0.5" min="0" wire:model="delivery_shipping_fee" class="admin-input has-prefix" placeholder="0.00">
                                            </div>
                                            <button type="button" class="admin-btn admin-btn-sm" wire:click="confirmDelivery" wire:loading.attr="disabled">Deliver</button>
                                            <button type="button" class="admin-btn admin-btn-sm admin-btn-secondary" wire:click="cancelDelivery">Cancel</button>
                                        </div>
                                    @else
                                        <button type="button" class="admin-btn" wire:click="advance({{ $order->id }})" wire:loading.attr="disabled" wire:target="advance({{ $order->id }})">
                                            @include('components.admin.icons.arrow-right')
                                            Mark as delivered
                                        </button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-empty">No orders in this view.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
