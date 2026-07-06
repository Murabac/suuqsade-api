@props(['status'])

<span class="admin-badge {{ \App\Support\AdminUi::statusBadgeClass($status) }}">
    {{ \App\Support\AdminUi::statusLabel($status) }}
</span>
