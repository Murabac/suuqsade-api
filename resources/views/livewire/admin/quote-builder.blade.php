<div class="admin-page">
    <div class="admin-breadcrumb-header">
        <a href="{{ route('admin.incoming') }}" class="admin-back-link">
            @include('components.admin.icons.rotate-ccw')
            Back
        </a>
        <span class="admin-breadcrumb-sep">/</span>
        <h1>Quote Builder</h1>
        <span class="admin-order-ref">{{ \App\Support\AdminUi::orderRef($order) }}</span>
    </div>

    <div class="admin-quote-layout">
        <div class="admin-quote-left">
            <div class="admin-panel">
                <p class="admin-panel-label">Customer Submission</p>
                <p style="font-weight:600;margin:0 0 0.25rem">{{ $order->user->name }}</p>
                <a href="{{ $order->product_link }}" target="_blank" rel="noopener" class="admin-link" style="word-break:break-all">
                    @include('components.admin.icons.external-link')
                    {{ $order->product_link }}
                </a>
                @if ($order->product_note)
                    <div style="margin-top:0.75rem;padding:0.75rem;background:#f9fafb;border-radius:0.5rem">
                        <p style="margin:0 0 0.25rem;font-size:0.6875rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:0.05em">Variant details</p>
                        <p style="margin:0;font-size:0.8125rem;color:#374151;white-space:pre-line">{{ $order->product_note }}</p>
                    </div>
                @endif
                <p style="margin:0.75rem 0 0;font-size:0.75rem;color:#6b7280">
                    Submitted {{ $order->created_at->diffForHumans() }}
                </p>
            </div>

            <div class="admin-alert admin-alert-warning">
                @include('components.admin.icons.alert-triangle')
                <span>Always open the link before quoting to verify availability, size options, and current price. Prices may differ from when the customer submitted.</span>
            </div>
        </div>

        <div class="admin-quote-right">
            <div class="admin-panel">
                <p class="admin-panel-label">Build Quote</p>

                <form wire:submit="submitQuote">
                    <div class="admin-field">
                        <label>Item Cost (USD)</label>
                        <div class="admin-input-wrap">
                            <span class="admin-input-prefix">$</span>
                            <input type="number" step="0.01" min="0.01" wire:model.live="item_cost" placeholder="0.00" class="admin-input has-prefix">
                        </div>
                        @error('item_cost') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="admin-field">
                        <label>Service Fee (%)</label>
                        <div class="admin-input-wrap">
                            <input type="number" step="0.5" min="0" max="100" wire:model.live="service_fee_pct" class="admin-input has-suffix">
                            <span class="admin-input-suffix">%</span>
                        </div>
                        @if ($this->itemNum > 0)
                            <p style="font-size:0.75rem;color:#6b7280;margin:0.25rem 0 0">= ${{ number_format($this->feeAmount, 2) }} on ${{ number_format($this->itemNum, 2) }}</p>
                        @endif
                    </div>

                    <p style="font-size:0.75rem;color:#6b7280;margin:0 0 1rem">
                        Shipping &amp; customs are added when the order is delivered — the amount is not known at quote time.
                    </p>

                    <div class="admin-breakdown-divider"></div>

                    <div class="admin-breakdown-row">
                        <span style="color:#6b7280">Item cost</span>
                        <span>${{ number_format($this->itemNum, 2) }}</span>
                    </div>
                    <div class="admin-breakdown-row" style="margin-bottom:1rem">
                        <span style="color:#6b7280">Service fee ({{ $service_fee_pct }}%)</span>
                        <span>${{ number_format($this->feeAmount, 2) }}</span>
                    </div>

                    <div class="admin-total-box">
                        <span class="total-label">Customer pays now</span>
                        <span class="total-value">${{ number_format($this->total, 2) }}</span>
                    </div>

                    <button type="submit" class="admin-btn admin-btn-lg @if($sent) admin-btn-success @endif" style="margin-top:1rem" @disabled($this->total <= 0 || $sent)>
                        @if ($sent)
                            @include('components.admin.icons.check-circle')
                            Quote Sent!
                        @else
                            @include('components.admin.icons.send')
                            Send quote to customer
                        @endif
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
