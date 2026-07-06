<div class="admin-page" wire:poll.15s>
    <div class="admin-page-header">
        <div>
            <h1>Incoming Queue</h1>
            <p>{{ $orders->count() }} new orders awaiting quotes</p>
        </div>
        <div class="admin-search-wrap">
            @include('components.admin.icons.search')
            <input type="text" class="admin-search" placeholder="Search orders…" wire:model.live.debounce.300ms="search">
        </div>
    </div>

    <div class="admin-page-body">
        <div class="admin-table-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:7rem">Order ID</th>
                        <th style="width:11rem">Customer</th>
                        <th>Submitted Link</th>
                        <th style="width:8rem">Submitted</th>
                        <th style="width:6rem"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $index => $order)
                        <tr class="@if($index % 2 === 1) row-alt @endif" wire:key="order-{{ $order->id }}">
                            <td><span class="admin-mono">{{ \App\Support\AdminUi::orderRef($order) }}</span></td>
                            <td><strong>{{ $order->user->name }}</strong></td>
                            <td>
                                <a href="{{ $order->product_link }}" target="_blank" rel="noopener" class="admin-link" title="{{ $order->product_link }}">
                                    <span class="admin-mono">{{ \App\Support\AdminUi::truncateUrl($order->product_link) }}</span>
                                    @include('components.admin.icons.external-link')
                                </a>
                                @if ($order->product_note)
                                    <br><span style="font-size:0.75rem;color:#6b7280">{{ $order->product_note }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="admin-time">
                                    @include('components.admin.icons.clock')
                                    {{ $order->created_at->diffForHumans(short: true) }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.orders.quote', $order) }}" class="admin-btn">
                                    Quote
                                    @include('components.admin.icons.chevron-right')
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="admin-empty">
                                {{ $search ? 'No orders match your search.' : 'No submitted orders right now.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
