@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')

{{-- Stat Cards --}}
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-card-title">Laporan Aktif</div>
        <div class="stat-card-value c-rose">{{ $laporanAktif }}</div>
        <div class="stat-card-meta">
            <span class="dot dot-red"></span> {{ $pendingCount }} pending
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title">Selesai Bulan Ini</div>
        <div class="stat-card-value c-forest">{{ $selesaiBulanIni }}</div>
        <div class="stat-card-meta" style="color:#323C31;">
            <span class="dot dot-green"></span> +{{ $selesaiMingguIni }} minggu ini
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title">Rata-rata Perbaikan</div>
        <div class="stat-card-value c-black">
            {{ $avgPerbaikan }} <span class="unit">hari</span>
        </div>
        <div class="stat-sub">Target ≤ 3 hari</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title">Rating Kepuasan</div>
        <div class="stat-card-value c-sand">4.6 ★</div>
        <div class="stat-sub c-sand">dari 28 penilai</div>
    </div>
</div>

{{-- Mid Grid --}}
<div class="mid-grid">

    {{-- Laporan Terbaru --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">Laporan terbaru</div>
            <a class="card-link" href="{{ route('laporan.index') }}">lihat semua →</a>
        </div>

        @forelse($laporanTerbaru as $item)
            @php
                $initials    = strtoupper(substr($item->penghuni, 0, 2));
                $statusClass = match($item->status) {
                    'Diproses' => 'badge-proses',
                    'Selesai'  => 'badge-selesai',
                    default    => 'badge-pending',
                };
            @endphp
            <div class="report-item">
                <div class="avatar">{{ $initials }}</div>
                <div class="report-info">
                    <div class="report-room">Kamar {{ $item->kamar }} · {{ $item->kategori }}</div>
                    <div class="report-desc">{{ $item->masalah }}</div>
                </div>
                <span class="badge {{ $statusClass }}">{{ $item->status }}</span>
            </div>
        @empty
            <div style="padding:24px 0; text-align:center; color:#aaa; font-size:13px;">
                Belum ada laporan.
            </div>
        @endforelse
    </div>

    {{-- Tren Chart --}}
    <div class="card">
        <div class="chart-header">
            <div class="card-title">Tren Laporan</div>
            <div class="chart-range">
                {{ \Carbon\Carbon::now()->subMonths(5)->locale('id')->isoFormat('MMM YY') }}
                –
                {{ \Carbon\Carbon::now()->locale('id')->isoFormat('MMM YY') }}
            </div>
        </div>
        <div class="chart-bars" id="trendChart"
             data-trend="{{ $tren->toJson() }}">
        </div>
    </div>

</div>

{{-- Bottom Grid --}}
<div class="bottom-grid">

    {{-- Kategori Kerusakan --}}
    @php
        $maxKat  = $kategori->max() ?: 1;
        $katList = [
            'Listrik'  => 'Listrik',
            'Plumbing' => 'Plumbing',
            'AC'       => 'AC/Elektronik',
            'Lainnya'  => 'Lainnya',
        ];
    @endphp
    <div class="card">
        <div class="card-title" style="margin-bottom:16px">Kategori kerusakan</div>
        @foreach($katList as $key => $label)
            @php $count = $kategori[$key] ?? 0; @endphp
            <div class="kategori-row">
                <div class="kategori-name">{{ $label }}</div>
                <div class="bar-track">
                    <div class="bar-fill {{ $key === 'Lainnya' ? 'grey' : '' }}"
                         style="width:{{ $maxKat > 0 ? round(($count / $maxKat) * 100) : 0 }}%">
                    </div>
                </div>
                <div class="kategori-count">{{ $count }}</div>
            </div>
        @endforeach
    </div>

    {{-- Broadcast --}}
    <div class="card">
        <div class="card-title" style="margin-bottom:12px">Pengumuman broadcast</div>

        @forelse($broadcasts as $bc)
            @php
                $dotClass = match($loop->index) {
                    0       => 'b-grey',
                    1       => 'b-green',
                    default => 'b-dgreen',
                };
            @endphp
            <div class="broadcast-item">
                <div class="broadcast-dot {{ $dotClass }}"></div>
                <div>
                    <div class="broadcast-msg">{{ $bc->pesan }}</div>
                    <div class="broadcast-meta">{{ $bc->tanggal }} · {{ $bc->target }}</div>
                </div>
            </div>
        @empty
            <div style="font-size:13px; color:#aaa;">Belum ada broadcast.</div>
        @endforelse
    </div>

</div>

@endsection

@push('scripts')
<script>
// ── Header dropdowns ────────────────────────────────────────
const notifBtn     = document.getElementById('notifBtn');
const notifPanel   = document.getElementById('notifPanel');
const profileBtn   = document.getElementById('profileBtn');
const profilePanel = document.getElementById('profilePanel');

function closeAllDropdowns() {
    notifPanel.classList.remove('open');
    profilePanel.classList.remove('open');
}

notifBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    const wasOpen = notifPanel.classList.contains('open');
    closeAllDropdowns();
    if (!wasOpen) notifPanel.classList.add('open');
});

profileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    const wasOpen = profilePanel.classList.contains('open');
    closeAllDropdowns();
    if (!wasOpen) profilePanel.classList.add('open');
});

document.addEventListener('click', () => closeAllDropdowns());
notifPanel.addEventListener('click',  e => e.stopPropagation());
profilePanel.addEventListener('click', e => e.stopPropagation());

// ── Mark all read ────────────────────────────────────────────
document.getElementById('markAllBtn').addEventListener('click', () => {
    document.querySelectorAll('.np-item.unread').forEach(item => {
        item.classList.remove('unread');
        item.querySelector('.np-unread-dot')?.remove();
    });
    document.getElementById('notifDot').style.display = 'none';
});

document.querySelectorAll('.np-item').forEach(item => {
    item.addEventListener('click', () => {
        item.classList.remove('unread');
        item.querySelector('.np-unread-dot')?.remove();
        if (!document.querySelector('.np-item.unread')) {
            document.getElementById('notifDot').style.display = 'none';
        }
    });
});

// ── Profile logout ───────────────────────────────────────────
document.getElementById('profileLogout').addEventListener('click', () => {
    if (confirm('Yakin ingin keluar?')) {
        const form  = document.createElement('form');
        form.method = 'POST';
        form.action = '/logout';
        const csrf  = document.createElement('input');
        csrf.type   = 'hidden';
        csrf.name   = '_token';
        csrf.value  = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    }
});

// ── Trend Chart ──────────────────────────────────────────────
(function () {
    const container = document.getElementById('trendChart');
    if (!container) return;

    const raw    = JSON.parse(container.dataset.trend || '[]');
    const colors = ['#c8d9cf', '#b8cec4', '#c8d9cf', '#A6BCAE', '#A6BCAE', '#7a9e87'];
    const max    = Math.max(...raw.map(d => d.value), 1);

    raw.forEach((d, i) => {
        const pct  = (d.value / max) * 100;
        const wrap = document.createElement('div');
        wrap.className = 'bar-wrap';
        wrap.innerHTML = `
            <div class="bar"
                 style="height:${pct}%; background:${colors[i % colors.length]}"
                 title="${d.value} laporan">
            </div>
            <div class="bar-label">${d.label}</div>
        `;
        container.appendChild(wrap);
    });
})();
</script>
@endpush