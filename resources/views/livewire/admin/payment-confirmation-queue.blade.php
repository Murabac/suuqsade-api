<div class="admin-page" wire:poll.15s>
    <div class="admin-page-header">
        <div>
            <h1>Payment Confirmation</h1>
            <p>{{ $orders->count() }} orders awaiting payment verification</p>
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

        <div class="admin-alert admin-alert-warning">
            @include('components.admin.icons.alert-triangle')
            <span>Rows highlighted in amber have been waiting {{ $confirmMinutes }}+ minutes since payment was claimed.</span>
        </div>

        <div class="admin-table-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:7rem">Order ID</th>
                        <th style="width:10rem">Customer</th>
                        <th style="width:9rem">Phone</th>
                        <th class="text-right" style="width:8rem">Expected (USD)</th>
                        <th style="width:9rem">Claimed</th>
                        <th style="width:9rem"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        @php
                            $claimedAt = $order->payments->last()?->created_at;
                            $claimedMins = $claimedAt ? $claimedAt->diffInMinutes(now()) : 0;
                            $isUrgent = $claimedMins >= $confirmMinutes;
                        @endphp
                        <tr wire:key="pay-{{ $order->id }}" @class(['row-urgent' => $isUrgent])>
                            <td><span class="admin-mono">{{ \App\Support\AdminUi::orderRef($order) }}</span></td>
                            <td><strong>{{ $order->user->name }}</strong></td>
                            <td><span class="admin-mono">{{ $order->user->phone_number }}</span></td>
                            <td class="text-right"><span class="admin-amount">${{ number_format((float) $order->total_amount, 2) }}</span></td>
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
                            <td colspan="6" class="admin-empty">No orders awaiting payment confirmation.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
