<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Suuqsade Admin</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
    <div class="admin-login-page">
        <div class="admin-login-card">
            <div class="admin-login-brand">
                <div class="admin-logo-mark">S</div>
                <div>
                    <p class="admin-logo-title" style="color:#431475;">Suuqsade</p>
                    <p class="admin-logo-sub" style="color:#6b7280;">Admin</p>
                </div>
            </div>

            <h1>Sign in</h1>
            <p class="subtitle">Manage orders, quotes, and payments.</p>

            @if ($errors->any())
                <div class="admin-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}">
                @csrf
                <div class="admin-field">
                    <label for="email">Email</label>
                    <input class="admin-input" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="admin-field">
                    <label for="password">Password</label>
                    <input class="admin-input" id="password" type="password" name="password" required>
                </div>
                <label class="admin-checkbox-row">
                    <input type="checkbox" name="remember">
                    Remember me
                </label>
                <button type="submit" class="admin-btn admin-btn-lg">Sign in</button>
            </form>
        </div>
    </div>
</body>
</html>
