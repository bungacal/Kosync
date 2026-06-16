@extends('layouts.owner')

@section('title', 'Denah Lantai')

@php $page = 'denah-lantai'; @endphp

@push('styles')
    @vite(['resources/css/denah-lantai.css'])
@endpush

@section('content')

{{-- Stat Cards --}}
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-card-title">Total Kamar</div>
        <div class="stat-card-value">{{ $totalKamar }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title">Terisi</div>
        <div class="stat-card-value c-forest">{{ $terisi }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title">Laporan Aktif</div>
        <div class="stat-card-value c-rose">{{ $adaLaporan }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card-title">Kamar Kosong</div>
        <div class="stat-card-value">{{ $kosong }}</div>
    </div>
</div>

{{-- Main Grid --}}
<div class="floor-grid">

    {{-- Kiri: Denah --}}
    <div>
        <div class="legend">
            <div class="legend-item">
                <div class="legend-dot ld-terisi"></div> Terisi
            </div>
            <div class="legend-item">
                <div class="legend-dot ld-kosong"></div> Kosong
            </div>
            <div class="legend-item">
                <div class="legend-dot ld-maint"></div> Maintenance
            </div>
            <div class="legend-item">
                <div class="legend-dot ld-notif"></div> Ada Laporan
            </div>
        </div>

        <div class="floor-panel">
            @foreach($kamarPerLantai as $lantai => $kamarList)
                <div class="floor-section">
                    <div class="floor-title">{{ $lantai }}</div>
                    <div class="rooms-grid">
                        @foreach($kamarList as $kamar)
                            @php
                                $status = $kamar->status_tampilan;
                                $statusClass = match($status) {
                                    'terisi'      => 'terisi',
                                    'kosong'      => 'kosong',
                                    'maintenance' => 'maintenance',
                                    'laporan'     => 'notif',
                                    default       => 'kosong',
                                };
                                $hasBadge = in_array($status, ['maintenance', 'laporan']);
                                $tenant   = $kamar->penghuni_nama ?? 'Kosong';
                            @endphp
                            <div class="room-card {{ $statusClass }}"
                                 data-room="{{ $kamar->id }}">
                                @if($hasBadge)
                                    <div class="room-badge"></div>
                                @endif
                                <div class="room-number">{{ $kamar->nomor }}</div>
                                <div class="room-tenant">{{ $tenant }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Kanan: Detail Panel --}}
    <div class="detail-panel" id="detailPanel">
        <div class="detail-empty" id="detailEmpty">
            <svg viewBox="0 0 24 24">
                <rect x="3" y="3" width="18" height="18" rx="2"/>
                <path d="M3 9h18M9 3v18"/>
            </svg>
            <p>Klik salah satu kamar<br>untuk melihat detailnya</p>
        </div>
        <div id="detailContent" style="display:none;">
            <div class="detail-header">
                <div>
                    <div class="detail-room-name" id="dRoomName"></div>
                    <div class="detail-floor" id="dFloor"></div>
                </div>
                <div class="status-badge" id="dStatusBadge"></div>
            </div>
            <div id="dTenantSection"></div>
            <div class="detail-specs">
                <div class="spec-row">
                    <span class="spec-label">Harga/bulan</span>
                    <span class="spec-value" id="dPrice"></span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Luas kamar</span>
                    <span class="spec-value" id="dSize"></span>
                </div>
                <div class="spec-row">
                    <span class="spec-label">Tipe Kamar</span>
                    <span class="spec-value" id="dType"></span>
                </div>
            </div>
            <div class="detail-facilities">
                <div class="fac-title">Fasilitas</div>
                <div class="fac-tags" id="dFacilities"></div>
            </div>
            <div class="detail-actions" id="dActions"></div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
const roomData = @json($roomDetail);

const statusLabel = {
    terisi:      'Terisi',
    kosong:      'Kosong',
    maintenance: 'Maintenance',
    laporan:     'Ada Laporan',
};
const statusClass = {
    terisi:      'sb-terisi',
    kosong:      'sb-kosong',
    maintenance: 'sb-maint',
    laporan:     'sb-notif',
};

function getInitials(nama) {
    if (!nama) return '';
    return nama.split(' ').slice(0, 2).map(w => w[0]).join('').toUpperCase();
}

function showDetail(roomId) {
    const d = roomData[roomId];
    if (!d) return;

    document.getElementById('detailEmpty').style.display   = 'none';
    document.getElementById('detailContent').style.display = 'block';

    document.getElementById('dRoomName').textContent =
        'Kamar ' + d.nomor_kamar;
    document.getElementById('dFloor').textContent =
        d.lantai;
    document.getElementById('dPrice').textContent =
        'Rp ' + Number(d.harga).toLocaleString('id-ID');
    document.getElementById('dSize').textContent  = d.ukuran ?? '3 × 4 m²';
    document.getElementById('dType').textContent  = d.tipe   ?? 'Kamar standar';

    const badge = document.getElementById('dStatusBadge');
    badge.textContent = statusLabel[d.status] ?? d.status;
    badge.className   = 'status-badge ' + (statusClass[d.status] ?? 'sb-kosong');

    // ── Tenant section ──
    const tenantSec = document.getElementById('dTenantSection');
    if (d.penghuni) {
        const avatarColor = d.status === 'laporan' ? '#1565c0' : '#2e6644';
        const initials    = getInitials(d.penghuni);
        tenantSec.innerHTML = `
            <div class="tenant-row">
                <div class="tenant-avatar" style="background:${avatarColor}">
                    ${initials}
                </div>
                <div>
                    <div class="tenant-name">${d.penghuni}</div>
                    <div class="tenant-since">Sejak ${d.sejak ?? '-'}</div>
                </div>
            </div>`;

        if (d.laporan_aktif) {
            const alertBg  = d.status === 'laporan' ? '#e8f0fa' : '#fbe8ec';
            const alertTxt = d.status === 'laporan' ? '#1b5e99' : '#8c2040';
            tenantSec.innerHTML += `
                <div style="margin:10px 20px 0;padding:9px 12px;
                     background:${alertBg};border-radius:8px;
                     font-size:12px;color:${alertTxt};
                     display:flex;align-items:center;gap:8px;">
                    <svg style="width:14px;height:14px;stroke:currentColor;
                         fill:none;stroke-width:2;stroke-linecap:round;
                         stroke-linejoin:round;flex-shrink:0"
                         viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span>${d.laporan_aktif}</span>
                </div>`;
        }
    } else if (d.status === 'maintenance') {
        tenantSec.innerHTML = `
            <div class="tenant-row" style="background:#fce8ec;">
                <div class="tenant-avatar" style="background:#8c2040;">
                    <svg style="width:18px;height:18px;stroke:#fff;fill:none;
                         stroke-width:2;stroke-linecap:round;stroke-linejoin:round"
                         viewBox="0 0 24 24">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0
                                 l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12
                                 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                    </svg>
                </div>
                <div>
                    <div class="tenant-name" style="color:#8c2040;">Dalam Perbaikan</div>
                    <div class="tenant-since">${d.laporan_aktif ?? 'Sedang direnovasi'}</div>
                </div>
            </div>`;
    } else {
        tenantSec.innerHTML = `
            <div class="tenant-row" style="background:#f5f0ea;">
                <div class="tenant-avatar" style="background:#cfc7b4;">
                    <svg style="width:18px;height:18px;stroke:#fff;fill:none;
                         stroke-width:2;stroke-linecap:round;stroke-linejoin:round"
                         viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="12" cy="7" r="4"/>
                    </svg>
                </div>
                <div>
                    <div class="tenant-name" style="color:#857c6a;">Tidak ada penghuni</div>
                    <div class="tenant-since">Kamar siap disewa</div>
                </div>
            </div>`;
    }

    // ── Facilities ──
    const fasilitas = d.fasilitas ?? [];
    document.getElementById('dFacilities').innerHTML = fasilitas.length
        ? fasilitas.map(f => `<span class="fac-tag">${f}</span>`).join('')
        : '<span style="font-size:12px;color:#aaa;">Belum ada data.</span>';

    // ── Actions ──
    const actionsEl  = document.getElementById('dActions');
    const editUrl    = `{{ url('/owner/kamar') }}/${roomId}/edit`;
    const lapUrl     = `{{ route('pemilik.laporan') }}`;

    if (d.status === 'kosong') {
        actionsEl.innerHTML = `
            <button class="btn-outline"
                onclick="window.location='${editUrl}'">Edit Kamar</button>
            <button class="btn-solid">Tambah Penghuni</button>`;
    } else if (d.status === 'maintenance') {
        actionsEl.innerHTML = `
            <button class="btn-outline"
                onclick="window.location='${editUrl}'">Edit Kamar</button>
            <button class="btn-solid" style="background:#8c2040;"
                onclick="window.location='${lapUrl}'">Lihat Laporan</button>`;
    } else if (d.status === 'laporan') {
        actionsEl.innerHTML = `
            <button class="btn-outline"
                onclick="window.location='${editUrl}'">Edit Kamar</button>
            <button class="btn-solid" style="background:#1565c0;"
                onclick="window.location='${lapUrl}'">Lihat Laporan</button>`;
    } else {
        actionsEl.innerHTML = `
            <button class="btn-outline"
                onclick="window.location='${editUrl}'">Edit Kamar</button>
            <button class="btn-solid"
                onclick="window.location='{{ route('pemilik.komunikasi') }}'">
                Hubungi Penghuni
            </button>`;
    }
}

// ── Room click ──
document.querySelectorAll('.room-card').forEach(card => {
    card.addEventListener('click', function () {
        document.querySelectorAll('.room-card')
            .forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        showDetail(this.dataset.room);
    });
});

// Auto-select kamar pertama
const firstCard = document.querySelector('.room-card');
if (firstCard) {
    firstCard.classList.add('selected');
    showDetail(firstCard.dataset.room);
}
</script>
@endpush
