<div class="admin-page">
    <div class="admin-page-header">
        <div>
            <h1>Dashboard</h1>
            <p>Overview of today's order pipeline</p>
        </div>
    </div>

    <div class="admin-page-body">
        <div class="admin-stats-grid">
            <a href="{{ route('admin.incoming') }}" class="admin-stat-card">
                <span class="admin-stat-label">Incoming</span>
                <span class="admin-stat-value">{{ $incomingCount }}</span>
                <span class="admin-stat-hint">Awaiting quotes</span>
            </a>
            <a href="{{ route('admin.payments') }}" class="admin-stat-card">
                <span class="admin-stat-label">Awaiting payment</span>
                <span class="admin-stat-value">{{ $quotedCount }}</span>
                <span class="admin-stat-hint">Quoted, waiting on customer</span>
            </a>
            <a href="{{ route('admin.payments') }}" class="admin-stat-card">
                <span class="admin-stat-label">Confirm payment</span>
                <span class="admin-stat-value">{{ $paymentCount }}</span>
                <span class="admin-stat-hint">Customer claimed paid</span>
            </a>
            <a href="{{ route('admin.tracking') }}" class="admin-stat-card">
                <span class="admin-stat-label">Tracking</span>
                <span class="admin-stat-value">{{ $trackingCount }}</span>
                <span class="admin-stat-hint">In fulfillment</span>
            </a>
            <div class="admin-stat-card admin-stat-card-muted">
                <span class="admin-stat-label">Delivered today</span>
                <span class="admin-stat-value">{{ $deliveredToday }}</span>
                <span class="admin-stat-hint">Completed orders</span>
            </div>
        </div>

        <div class="admin-panel" style="margin-top:0">
            <p class="admin-panel-label">Quick actions</p>
            <div style="display:flex;flex-wrap:wrap;gap:0.5rem">
                <a href="{{ route('admin.incoming') }}" class="admin-btn">Review incoming queue</a>
                <a href="{{ route('admin.payments') }}" class="admin-btn">Confirm payments</a>
                <a href="{{ route('admin.tracking') }}" class="admin-btn admin-btn-secondary">Track orders</a>
                <a href="{{ route('admin.settings') }}" class="admin-btn admin-btn-secondary">Settings</a>
            </div>
        </div>
    </div>
</div>
