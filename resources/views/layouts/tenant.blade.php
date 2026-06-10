<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kosync - @yield('title', 'Beranda')</title>
    <meta name="theme-color" content="#4b2038" />
    <link rel="icon" href="{{ asset('kosync-logo.jpeg') }}" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
  </head>
  <body data-role="penghuni" data-page="{{ $page ?? 'home' }}">
    <div class="app-shell">
      <aside class="sidebar">
        <a class="brand" href="{{ route('penghuni.home') }}" aria-label="Kosync">
          <img src="{{ asset('kosync-logo.jpeg') }}" alt="Kosync logo" class="brand-logo" />
          <span><strong>KOSYNC</strong><small>Kost Management System</small></span>
        </a>

        <nav class="nav-section" aria-label="Menu utama">
          <p>Utama</p>
          <a class="nav-item visible {{ $page === 'home' ? 'active' : '' }}" href="{{ route('penghuni.home') }}"><i data-lucide="home"></i><span>Beranda</span></a>
          <a class="nav-item visible {{ $page === 'reports' ? 'active' : '' }}" href="{{ route('penghuni.reports') }}"><i data-lucide="file-plus-2"></i><span>Laporan Saya</span></a>
          <a class="nav-item visible {{ $page === 'messages' ? 'active' : '' }}" href="{{ route('penghuni.messages') }}"><i data-lucide="message-circle"></i><span>Pesan Saya</span></a>
        </nav>

        <nav class="nav-section" aria-label="Kelola">
          <p>Kelola</p>
          <a class="nav-item visible {{ $page === 'history' ? 'active' : '' }}" href="{{ route('penghuni.history') }}"><i data-lucide="history"></i><span>Riwayat Laporan</span></a>
        </nav>

        <div class="sidebar-user">
          <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
          <span>
            <strong>{{ auth()->user()->name }}</strong>
            <small>Kamar {{ auth()->user()->kamar?->nomor ?? '-' }}</small>
          </span>
        </div>
      </aside>
      <main class="main-area">
        <div class="content-shell">
          <header class="topbar">
            <div>
              <p class="eyebrow">Kosync</p>
              <h1>@yield('title', 'Beranda')</h1>
            </div>
            <div class="topbar-actions">
              <div class="segmented">
                <span class="role-switch disabled" aria-disabled="true">Pemilik Kos</span>
                <a class="role-switch active" href="{{ route('penghuni.home') }}">Penghuni Kos</a>
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
  </body>
</html>
