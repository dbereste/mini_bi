<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>@yield('title', 'Mini BI')</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-bar-chart-fill"></i> Mini BI
        </div>
        <nav class="sidebar-nav">
            <a href="{{ route('overview') }}" class="active"><i class="bi bi-speedometer2"></i> Overview</a>
            <a href="#"><i class="bi bi-people"></i> Affiliates</a>
            <a href="#"><i class="bi bi-cash-stack"></i> Payments</a>
            <a href="#"><i class="bi bi-graph-up"></i> Retention</a>
            <a href="#"><i class="bi bi-gear"></i> Settings</a>
        </nav>
    </aside>

    <div class="main">
        <header class="topbar">
            <h1 class="page-title">@yield('page-title')</h1>
            <div class="topbar-right">
                <span class="data-badge"><i class="bi bi-database"></i> Warehouse snapshot: 2026-10-31 23:59 UTC</span>
            </div>
        </header>

        <main class="content">
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
