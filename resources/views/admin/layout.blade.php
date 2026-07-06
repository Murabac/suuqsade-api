<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — Suuqsade</title>
    <style>
        :root { --purple: #431475; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, sans-serif; background: #f8f8fb; color: #1a1a1a; }
        .header { background: var(--purple); color: #fff; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: #fff; text-decoration: none; }
        .container { max-width: 960px; margin: 2rem auto; padding: 0 1rem; }
        .card { background: #fff; border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        .btn { display: inline-block; background: var(--purple); color: #fff; border: 0; padding: .65rem 1rem; border-radius: 8px; cursor: pointer; text-decoration: none; font-size: .95rem; }
        .btn-secondary { background: #e5e7eb; color: #111; }
        label { display: block; margin-bottom: .35rem; font-weight: 600; font-size: .9rem; }
        input { width: 100%; padding: .65rem .75rem; border: 1px solid #d1d5db; border-radius: 8px; margin-bottom: 1rem; }
        .error { color: #b91c1c; font-size: .875rem; margin-bottom: 1rem; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    @hasSection('show_header')
        <header class="header">
            <strong>Suuqsade Admin</strong>
            <div>
                <span>{{ auth('admin')->user()->name }}</span>
                <form action="{{ route('admin.logout') }}" method="POST" style="display:inline;margin-left:1rem;">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="padding:.4rem .75rem;">Logout</button>
                </form>
            </div>
        </header>
    @endif

    <main class="container">
        @yield('content')
    </main>
</body>
</html>
