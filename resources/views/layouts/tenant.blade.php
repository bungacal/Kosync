<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kosync - @yield('title', 'Beranda')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body>
    <div class="app-shell">
      <aside class="sidebar">
        <a class="brand" href="{{ route('tenant.home') }}">
          <span class="brand-mark">K</span>
          <span><strong>KOSYNC</strong><small>Kost Management System</small></span>
        </a>
        <nav class="nav-section">
          <p>Penghuni</p>
          <a class="nav-item {{ $page === 'home' ? 'active' : '' }}" href="{{ route('tenant.home') }}">Beranda</a>
          <a class="nav-item {{ $page === 'reports' ? 'active' : '' }}" href="{{ route('tenant.reports') }}">Laporan Saya</a>
          <a class="nav-item {{ $page === 'history' ? 'active' : '' }}" href="#">Riwayat Laporan</a>
        </nav>
        <div class="sidebar-user">
          <strong>{{ auth()->user()->name }}</strong>
          <small>Kamar {{ auth()->user()->room?->number ?? '-' }}</small>
        </div>
      </aside>
      <main class="main-area">
        <div class="content-shell">
          <header class="topbar">
            <div>
              <p class="eyebrow">Kosync</p>
              <h1>@yield('title', 'Beranda')</h1>
            </div>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button class="ghost-button" type="submit">Logout</button>
            </form>
          </header>
          @yield('content')
        </div>
      </main>
    </div>
  </body>
</html>
