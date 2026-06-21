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
              <div class="notification-wrap">
                <button class="icon-button notification-button" type="button" id="tenantNotificationButton"
                  aria-label="Notifikasi" aria-expanded="false">
                  <i data-lucide="bell"></i>
                  @if (($tenantUnreadNotifications ?? 0) > 0)
                    <span class="notification-count">{{ $tenantUnreadNotifications }}</span>
                  @endif
                </button>
                <div class="notification-menu tenant-notification-menu" id="tenantNotificationMenu" hidden>
                  <div class="notification-heading">
                    <strong>Notifikasi</strong>
                    <form method="POST" action="{{ route('penghuni.notifications.read') }}">
                      @csrf
                      <button class="link-button" type="submit">Tandai semua dibaca</button>
                    </form>
                  </div>
                  @forelse (($tenantNotifications ?? collect()) as $notification)
                    @php($canRate = $notification->tipe === 'laporan_selesai' && $notification->laporan?->nilai_rating === null)
                    @if ($canRate)
                      <button class="notification-item notification-action {{ $notification->read_at ? '' : 'unread' }}"
                        type="button" data-open-rating>
                        <span class="notification-icon {{ $notification->warna }}"><i data-lucide="{{ $notification->ikon }}"></i></span>
                        <span>
                          <strong>{{ $notification->judul }}</strong>
                          <small>{{ $notification->isi }}</small>
                          <em>{{ $notification->created_at->diffForHumans() }}</em>
                        </span>
                      </button>
                    @else
                      <a class="notification-item {{ $notification->read_at ? '' : 'unread' }}"
                        href="{{ $notification->url ?? route('penghuni.home') }}">
                        <span class="notification-icon {{ $notification->warna }}"><i data-lucide="{{ $notification->ikon }}"></i></span>
                        <span>
                          <strong>{{ $notification->judul }}</strong>
                          <small>{{ $notification->isi }}</small>
                          <em>{{ $notification->created_at->diffForHumans() }}</em>
                        </span>
                      </a>
                    @endif
                  @empty
                    <div class="notification-empty">Belum ada notifikasi baru.</div>
                  @endforelse
                  <a class="notification-footer" href="{{ route('penghuni.history') }}">Lihat semua notifikasi</a>
                </div>
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
    @if ($tenantPendingRatingReport)
      <div class="rating-modal-backdrop" id="ratingModal" hidden>
        <section class="rating-modal" role="dialog" aria-modal="true" aria-labelledby="ratingModalTitle">
          <button class="modal-close" type="button" data-close-rating aria-label="Tutup modal">
            <i data-lucide="x"></i>
          </button>
          <div class="rating-hero">
            <span class="rating-hero-icon"><i data-lucide="clipboard-check"></i></span>
            <span class="rating-check"><i data-lucide="check"></i></span>
          </div>
          <h2 id="ratingModalTitle">Beri Rating Hasil Perbaikan</h2>
          <h3>Laporan ini sudah selesai dikerjakan!</h3>
          <p>Yuk, beri penilaian atas hasil perbaikan yang telah dilakukan.</p>

          <form class="rating-modal-form" method="POST"
            action="{{ route('penghuni.laporan.rating', $tenantPendingRatingReport) }}">
            @csrf
            <div class="rating-box">
              <strong>Pilih rating</strong>
              <div class="rating-stars modal-stars">
                @for ($i = 5; $i >= 1; $i--)
                  <input type="radio" id="modal-star-{{ $i }}" name="nilai_rating" value="{{ $i }}" @checked(old('nilai_rating', 4) == $i)>
                  <label for="modal-star-{{ $i }}" aria-label="{{ $i }} bintang">&#9733;</label>
                @endfor
              </div>
              <small id="ratingLabel">Baik</small>
            </div>

            <label class="field">
              <span>Ulasan (Opsional)</span>
              <small>Ceritakan pengalaman Anda terkait perbaikan ini.</small>
              <textarea name="ulasan_rating" placeholder="Tulis ulasan Anda di sini...">{{ old('ulasan_rating') }}</textarea>
            </label>

            @if ($errors->any())
              <div class="alert">{{ $errors->first() }}</div>
            @endif

            <div class="modal-actions">
              <button class="ghost-button" type="button" data-close-rating>Batal</button>
              <button class="primary-button" type="submit">Kirim</button>
            </div>
          </form>
        </section>
      </div>
    @endif
    <script>
      lucide.createIcons();
    </script>
  </body>
</html>
