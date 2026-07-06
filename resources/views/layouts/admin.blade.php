<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin' }} — Suuqsade</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @livewireStyles
</head>
<body class="admin-app">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-logo">
                <div class="admin-logo-row">
                    <div class="admin-logo-mark">S</div>
                    <div>
                        <p class="admin-logo-title">Suuqsade</p>
                        <p class="admin-logo-sub">Admin</p>
                    </div>
                </div>
            </div>

            <nav class="admin-nav">
                <a href="{{ route('admin.incoming') }}" class="admin-nav-link @if(request()->routeIs('admin.incoming')) active @endif">
                    @include('components.admin.icons.inbox')
                    <span class="admin-nav-label">Incoming Queue</span>
                    @if(($incomingCount ?? 0) > 0)
                        <span class="admin-nav-badge">{{ $incomingCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.quote') }}" class="admin-nav-link @if(request()->routeIs('admin.quote') || request()->routeIs('admin.orders.quote')) active @endif">
                    @include('components.admin.icons.file-text')
                    <span class="admin-nav-label">Quote Builder</span>
                </a>
                <a href="{{ route('admin.payments') }}" class="admin-nav-link @if(request()->routeIs('admin.payments')) active @endif">
                    @include('components.admin.icons.credit-card')
                    <span class="admin-nav-label">Payment Confirmation</span>
                    @if(($paymentCount ?? 0) > 0)
                        <span class="admin-nav-badge">{{ $paymentCount }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.tracking') }}" class="admin-nav-link @if(request()->routeIs('admin.tracking')) active @endif">
                    @include('components.admin.icons.package')
                    <span class="admin-nav-label">Order Tracking</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="admin-nav-link @if(request()->routeIs('admin.settings')) active @endif">
                    @include('components.admin.icons.settings')
                    <span class="admin-nav-label">Settings</span>
                </a>
            </nav>

            <div class="admin-sidebar-footer">
                <p>Internal Tool · v1.0</p>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="admin-sidebar-logout">Sign out ({{ auth('admin')->user()->name }})</button>
                </form>
            </div>
        </aside>

        <main class="admin-main">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
