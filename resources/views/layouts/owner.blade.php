<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kosync - @yield('title', 'Pemilik')</title>
    <meta name="theme-color" content="#4b2038" />
    <link rel="icon" href="{{ asset('kosync-logo.jpeg') }}" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  </head>
  <body data-role="pemilik" data-page="{{ $page ?? 'home' }}">
    <div class="app-shell">
      <aside class="sidebar">
        <a class="brand" href="{{ route('pemilik.home') }}" aria-label="Kosync">
          <img src="{{ asset('kosync-logo.jpeg') }}" alt="Kosync logo" class="brand-logo" />
          <span><strong>KOSYNC</strong><small>Kost Management System</small></span>
        </a>

        <nav class="nav-section" aria-label="Menu pemilik">
          <p>Utama</p>
          <a class="nav-item visible {{ $page === 'home' ? 'active' : '' }}" href="{{ route('pemilik.home') }}"><i data-lucide="layout-dashboard"></i><span>Dashboard</span></a>
          <a class="nav-item visible {{ $page === 'laporan' ? 'active' : '' }}" href="{{ route('pemilik.laporan') }}"><i data-lucide="clipboard-list"></i><span>Laporan</span></a>
          <a class="nav-item visible {{ $page === 'komunikasi' ? 'active' : '' }}" href="{{ route('pemilik.komunikasi') }}"><i data-lucide="messages-square"></i><span>Komunikasi</span></a>
        </nav>

        <div class="sidebar-user">
          <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
          <span>
            <strong>{{ auth()->user()->name }}</strong>
            <small>{{ $kos?->nama ?? 'Pemilik Kos' }}</small>
          </span>
        </div>
      </aside>

      <main class="main-area">
        <div class="content-shell">
          <header class="topbar">
            <div>
              <p class="eyebrow">Kosync</p>
              <h1>@yield('title', 'Pemilik')</h1>
            </div>
            <div class="topbar-actions">
              <div class="segmented">
                <a class="role-switch active" href="{{ route('pemilik.home') }}">Pemilik Kos</a>
                <span class="role-switch disabled" aria-disabled="true">Penghuni Kos</span>
              </div>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="icon-button" type="submit" aria-label="Logout"><i data-lucide="log-out"></i></button>
              </form>
            </div>
          </header>
          @yield('content')
        </div>
      </main>
    </div>
    <script>
      lucide.createIcons();
    </script>
    @stack('scripts')
  </body>
</html>
