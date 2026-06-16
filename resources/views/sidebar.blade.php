{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>KOSYNC – @yield('title', 'Kos Management Platform')</title>

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
          rel="stylesheet" />

    {{-- Global app CSS --}}
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}" />

    {{-- Page-specific CSS --}}
    @stack('styles')
</head>
<body>

    {{-- ===== SIDEBAR ===== --}}
    <aside class="sidebar" id="sidebar">

        {{-- Logo --}}
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">K</div>
            <div class="sidebar-logo-text">
                <div class="brand">KOSYNC</div>
                <div class="tagline">Kos Management Platform</div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="sidebar-nav">

            <div class="nav-section">
                <div class="nav-section-label">Utama</div>

                <a href="{{ route('dashboard') }}"
                   class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    {{-- Icon: Home --}}
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('laporan.index') }}"
                   class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                    {{-- Icon: ClipboardList --}}
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="2" width="6" height="4" rx="1"/>
                        <path d="M7 4H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2h-2"/>
                        <line x1="9" y1="12" x2="15" y2="12"/>
                        <line x1="9" y1="16" x2="13" y2="16"/>
                    </svg>
                    Laporan
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-label">Kelola</div>

                <div class="nav-group">
                    <a href="{{ route('manajemen-kos.index') }}"
                    class="nav-link {{ request()->routeIs('manajemen-kos.*') ? 'active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="4" y="2" width="16" height="20" rx="2"/>
                            <line x1="9" y1="7" x2="9" y2="7.01"/>
                            <line x1="15" y1="7" x2="15" y2="7.01"/>
                            <line x1="9" y1="12" x2="9" y2="12.01"/>
                            <line x1="15" y1="12" x2="15" y2="12.01"/>
                            <path d="M9 17h6"/>
                        </svg>
                        Manajemen Kos
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            style="margin-left:auto">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </a>

                    {{-- Submenu --}}
                    <div class="nav-submenu {{ request()->routeIs('manajemen-kos.*') ? 'open' : '' }}">
                        <a href="{{ route('manajemen-kos.denah-lantai') }}"
                        class="nav-sublink {{ request()->routeIs('manajemen-kos.denah-lantai') ? 'active' : '' }}">
                            Denah Lantai
                        </a>
                        <a href="{{ route('manajemen-kos.daftar-kamar') }}"
                        class="nav-sublink {{ request()->routeIs('manajemen-kos.daftar-kamar') ? 'active' : '' }}">
                            Daftar Kamar
                        </a>
                    </div>
                </div>

                <a href="{{ route('komunikasi.index') }}"
                class="nav-link {{ request()->routeIs('komunikasi.*') ? 'active' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                    </svg>
                    Komunikasi
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-label">Insight</div>

                <a href="{{ route('analytics.index') }}"
                   class="nav-link {{ request()->routeIs('analytics.*') ? 'active' : '' }}">
                    {{-- Icon: BarChart2 --}}
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="20" x2="18" y2="10"/>
                        <line x1="12" y1="20" x2="12" y2="4"/>
                        <line x1="6" y1="20" x2="6" y2="14"/>
                    </svg>
                    Analytics
                </a>
            </div>

        </nav>

        {{-- Footer / User info --}}
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name ?? 'Nama Pemilik Kos' }}</div>
                    <div class="user-sub">{{ auth()->user()->kos_name ?? 'Nama Kos (Nomor Kamar)' }}</div>
                </div>
                <button class="user-menu-btn" aria-label="Menu pengguna">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="5"  r="1"/><circle cx="12" cy="12" r="1"/>
                        <circle cx="12" cy="19" r="1"/>
                    </svg>
                </button>
            </div>
        </div>

    </aside>

    {{-- ===== MAIN WRAPPER ===== --}}
    <div class="main-wrapper">

        {{-- Topbar --}}
        <header class="topbar">
            <h1 class="topbar-title">@yield('page-title', 'Dashboard')</h1>
            <div class="topbar-actions">
                <button class="btn-role">Pemilik Kos</button>
                <a href="#" class="icon-btn" aria-label="Profil">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </a>
                <a href="#" class="icon-btn" aria-label="Notifikasi">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 01-3.46 0"/>
                    </svg>
                </a>
            </div>
        </header>

        {{-- Page body --}}
        <main class="page-content">
            @yield('content')
        </main>

    </div>

    @stack('scripts')
</body>
</html>