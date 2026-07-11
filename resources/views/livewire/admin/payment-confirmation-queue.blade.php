<div class="admin-page" wire:poll.15s>
    <div class="admin-page-header">
        <div>
            <h1>Payments</h1>
            <p>
                @if ($filter === 'confirm')
                    {{ $orders->count() }} orders awaiting payment verification
                @else
                    {{ $orders->count() }} quoted orders waiting for customer payment
                @endif
            </p>
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
            <button type="button" class="admin-filter-tab @if($filter === 'awaiting') active @endif" wire:click="setFilter('awaiting')">
                Awaiting customer
                @if ($awaitingCount > 0)
                    <span class="admin-tab-count">{{ $awaitingCount }}</span>
                @endif
            </button>
            <button type="button" class="admin-filter-tab @if($filter === 'confirm') active @endif" wire:click="setFilter('confirm')">
                Confirm payment
                @if ($confirmCount > 0)
                    <span class="admin-tab-count">{{ $confirmCount }}</span>
                @endif
            </button>
        </div>

        @if ($filter === 'confirm')
            <div class="admin-alert admin-alert-warning">
                @include('components.admin.icons.alert-triangle')
                <span>Rows highlighted in amber have been waiting {{ $confirmMinutes }}+ minutes since payment was claimed.</span>
            </div>
        @else
            <div class="admin-alert">
                @include('components.admin.icons.clock')
                <span>These quotes were sent to customers. They appear here until the customer taps "I've sent the payment" in the app.</span>
            </div>
        @endif

        <div class="admin-table-card">
            @if ($filter === 'confirm')
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:7rem">Order ID</th>
                            <th style="width:10rem">Customer</th>
                            <th style="width:9rem">Phone</th>
                            <th class="text-right" style="width:8rem">Expected (USD)</th>
                            <th style="width:7rem">Method</th>
                            <th style="width:9rem">Claimed</th>
                            <th style="width:9rem"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            @php
                                $payment = $order->payments->last();
                                $claimedAt = $payment?->created_at;
                                $claimedMins = $claimedAt ? $claimedAt->diffInMinutes(now()) : 0;
                                $isUrgent = $claimedMins >= $confirmMinutes;
                            @endphp
                            <tr wire:key="pay-{{ $order->id }}" @class(['row-urgent' => $isUrgent])>
                                <td><span class="admin-mono">{{ \App\Support\AdminUi::orderRef($order) }}</span></td>
                                <td><strong>{{ $order->user->name }}</strong></td>
                                <td><span class="admin-mono">{{ $order->user->phone_number }}</span></td>
                                <td class="text-right"><span class="admin-amount">${{ number_format((float) $order->total_amount, 2) }}</span></td>
                                <td>
                                    <span class="admin-badge {{ \App\Support\AdminUi::paymentMethodBadgeClass($payment?->method) }}">
                                        {{ \App\Support\AdminUi::paymentMethodLabel($payment?->method) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="admin-time @if($isUrgent) urgent @endif">
                                        @include('components.admin.icons.clock')
                                        {{ $claimedAt?->diffForHumans(short: true) ?? '—' }}
                                        @if ($isUrgent)
                                            @include('components.admin.icons.alert-triangle')
                                        @endif
                                    </span>
                                </td>
                                <td class="text-right">
                                    <button type="button" class="admin-btn" wire:click="confirm({{ $order->id }})">
                                        @include('components.admin.icons.check-circle')
                                        Confirm payment
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="admin-empty">No orders awaiting payment confirmation.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th style="width:7rem">Order ID</th>
                            <th style="width:10rem">Customer</th>
                            <th style="width:9rem">Phone</th>
                            <th class="text-right" style="width:8rem">Quoted (USD)</th>
                            <th style="width:9rem">Quoted</th>
                            <th style="width:9rem"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr wire:key="quoted-{{ $order->id }}">
                                <td><span class="admin-mono">{{ \App\Support\AdminUi::orderRef($order) }}</span></td>
                                <td><strong>{{ $order->user->name }}</strong></td>
                                <td><span class="admin-mono">{{ $order->user->phone_number }}</span></td>
                                <td class="text-right"><span class="admin-amount">${{ number_format((float) $order->total_amount, 2) }}</span></td>
                                <td>
                                    <span class="admin-time">
                                        @include('components.admin.icons.clock')
                                        {{ $order->updated_at->diffForHumans(short: true) }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <button type="button" class="admin-btn admin-btn-danger" wire:click="cancel({{ $order->id }})" wire:confirm="Cancel this quoted order?">
                                        Cancel order
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="admin-empty">No quoted orders awaiting customer payment.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
