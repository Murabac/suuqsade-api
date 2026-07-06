@extends('admin.layout')

@section('title', 'Login')

@section('content')
<div class="card" style="max-width:420px;margin:4rem auto;">
    <h1 style="margin-top:0;color:#431475;">Admin Login</h1>
    <p class="muted">Sign in to manage orders and quotes.</p>

    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.login') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required>

        <label style="display:flex;align-items:center;gap:.5rem;font-weight:400;">
            <input type="checkbox" name="remember" style="width:auto;margin:0;">
            Remember me
        </label>

        <button type="submit" class="btn" style="width:100%;margin-top:.5rem;">Sign in</button>
    </form>
</div>
@endsection
