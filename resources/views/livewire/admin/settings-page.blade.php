<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h1>Settings</h1>
            <p>Global defaults for quotes and payment collection</p>
        </div>
    </div>

    <div class="admin-page-body">
        <div class="admin-settings-stack">
            <div class="admin-panel">
                <p class="admin-panel-label">Pricing Defaults</p>

                <form wire:submit="save">
                    <div class="admin-field">
                        <label>Default Service Fee (%)</label>
                        <p class="admin-field-hint">Pre-filled in the Quote Builder. Overridable per order.</p>
                        <div class="admin-input-wrap admin-input-narrow">
                            <input type="number" step="0.5" min="0" wire:model="default_service_fee_pct" class="admin-input has-suffix">
                            <span class="admin-input-suffix">%</span>
                        </div>
                    </div>

                    <div class="admin-field">
                        <label>Default Shipping &amp; Customs (USD)</label>
                        <p class="admin-field-hint">Pre-filled in the Quote Builder. Overridable per order.</p>
                        <div class="admin-input-wrap admin-input-narrow">
                            <span class="admin-input-prefix">$</span>
                            <input type="number" step="0.5" min="0" wire:model="default_shipping_fee" class="admin-input has-prefix">
                        </div>
                    </div>

                    <div class="admin-panel" style="margin-top:1.5rem;padding:0;border:0">
                        <p class="admin-panel-label">Merchant Payment Numbers</p>
                        <p class="admin-field-hint">Shared with customers when requesting payment. Same number for ZAAD and eDahab (MVP).</p>

                        <div class="admin-field">
                            <label>Merchant Number</label>
                            <input type="tel" wire:model="merchant_number" placeholder="e.g. 0634-000000" class="admin-input admin-mono">
                        </div>

                        <div class="admin-field">
                            <label>Quote response hours</label>
                            <input type="number" min="1" wire:model="quote_response_hours" class="admin-input admin-input-narrow">
                        </div>

                        <div class="admin-field">
                            <label>Payment confirm minutes</label>
                            <input type="number" min="1" wire:model="payment_confirm_minutes" class="admin-input admin-input-narrow">
                        </div>
                    </div>

                    <button type="submit" class="admin-btn @if($saved) admin-btn-success @endif" style="margin-top:0.5rem">
                        @if ($saved)
                            @include('components.admin.icons.check-circle')
                            Saved!
                        @else
                            @include('components.admin.icons.save')
                            Save settings
                        @endif
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
