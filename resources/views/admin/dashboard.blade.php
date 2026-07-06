@extends('admin.layout')

@section('title', 'Dashboard')
@section('show_header')

@section('content')
<div class="card">
    <h1 style="margin-top:0;color:#431475;">Week 1 complete</h1>
    <p class="muted">Database, auth, and admin login are ready. Week 2 adds order queues and Livewire components.</p>

    <ul>
        <li><strong>Incoming queue</strong> — submitted orders</li>
        <li><strong>Quote builder</strong> — send USD quotes</li>
        <li><strong>Payment confirmation</strong> — verify ZAAD/eDahab</li>
        <li><strong>Order tracking</strong> — advance status</li>
        <li><strong>Settings</strong> — fees and merchant number</li>
    </ul>

    <p class="muted" style="margin-top:1.5rem;">
        Seeded admin: <code>admin@suuqsade.com</code> / <code>password</code>
    </p>
</div>
@endsection
