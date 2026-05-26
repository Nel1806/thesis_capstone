<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Teacher Audit System' }}</title>
    <link rel="stylesheet" href="/css/audit.css">
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="seal">SDO</div>
                <div>
                    <strong>Teacher Audit System</strong>
                    <span>Schools Division Office - Marikina City</span>
                </div>
            </div>
            <nav class="nav">
                <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                <a class="{{ request()->routeIs('schools*') ? 'active' : '' }}" href="{{ route('schools') }}">School Audit</a>
                <a class="{{ request()->routeIs('parameters') ? 'active' : '' }}" href="{{ route('parameters') }}">Parameters</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout" type="submit">Sign Out</button>
                </form>
            </nav>
        </aside>

        <main class="main">
            {{ $slot }}
        </main>
    </div>
</body>
</html>
