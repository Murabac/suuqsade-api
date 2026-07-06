<div class="admin-page">
    <div class="admin-empty-state">
        @include('components.admin.icons.file-text')
        <p>Select an order from the Incoming Queue to build a quote.</p>
        <a href="{{ route('admin.incoming') }}">Go to Incoming Queue</a>
    </div>
</div>
