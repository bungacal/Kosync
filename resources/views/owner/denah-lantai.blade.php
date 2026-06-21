@extends('layouts.owner')

@section('title', 'Denah Lantai')

@php $page = 'denah-lantai'; @endphp

@push('styles')
    @vite(['resources/css/denah-lantai.css'])
@endpush

@section('content')

@if (session('success'))
    <div class="page-alert success">{{ session('success') }}</div>
@endif

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
        <div class="floor-toolbar">
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
            <button class="btn-add-room" type="button" id="openTambahKamar">
                <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Kamar
            </button>
        </div>

        <div class="floor-panel">
            @forelse($kamarPerLantai as $lantai => $kamarList)
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
            @empty
                <div class="floor-empty">
                    <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 3v18"/></svg>
                    <strong>Belum ada denah kamar</strong>
                    <span>Tambahkan kamar dulu, nanti kamar kosongnya bisa dipilih saat menambah penghuni.</span>
                </div>
            @endforelse
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

<div class="modal-backdrop" id="tambahKamarModal" aria-hidden="true">
    <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="tambahKamarTitle">
        <div class="modal-header">
            <div>
                <p class="modal-eyebrow">Denah Lantai</p>
                <h2 id="tambahKamarTitle">Tambah Kamar</h2>
            </div>
            <button class="modal-close" type="button" id="closeTambahKamar" aria-label="Tutup">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        @if ($errors->tambahKamar->any())
            <div class="form-alert">{{ $errors->tambahKamar->first() }}</div>
        @endif

        @php
            $tipeKamar = ['Kamar Standar', 'Kamar AC', 'Kamar Premium', 'Kamar Studio'];
            $pilihanFasilitas = [
                'AC', 'WiFi', 'Kamar mandi dalam', 'Kamar mandi luar',
                'Kasur', 'Lemari', 'Meja belajar', 'Dapur bersama', 'Parkir',
            ];
            $fasilitasTerpilih = (array) old('fasilitas', []);
        @endphp

        <form class="room-form" method="POST" action="{{ route('pemilik.kamar.store') }}">
            @csrf
            <div class="form-grid-2">
                <label class="form-field">
                    <span>Nomor Kamar <b class="required-mark">*</b></span>
                    <input type="text" name="nomor" value="{{ old('nomor') }}" placeholder="Contoh: 101" required>
                </label>
                <label class="form-field">
                    <span>Lantai <b class="required-mark">*</b></span>
                    <select name="lantai" required>
                        <option value="" disabled @selected(!old('lantai'))>Pilih lantai</option>
                        @for ($lantai = 1; $lantai <= 5; $lantai++)
                            <option value="{{ $lantai }}" @selected(old('lantai') == $lantai || old('lantai') === "Lantai {$lantai}")>
                                Lantai {{ $lantai }}
                            </option>
                        @endfor
                    </select>
                </label>
            </div>
            <label class="form-field">
                <span>Harga per Bulan <b class="required-mark">*</b></span>
                <div class="currency-input">
                    <span>Rp</span>
                    <input type="text" id="hargaDisplay" inputmode="numeric" autocomplete="off" placeholder="1.200.000" required>
                    <input type="hidden" name="harga" id="hargaValue" value="{{ old('harga') }}">
                </div>
            </label>
            <label class="form-field">
                <span>Kondisi Kamar <b class="required-mark">*</b></span>
                <select name="status" required>
                    <option value="Kosong" @selected(old('status', 'Kosong') === 'Kosong')>Kosong - siap disewa</option>
                    <option value="Maintenance" @selected(old('status') === 'Maintenance')>Maintenance - sedang diperbaiki</option>
                </select>
                <small class="field-help">Status Ada Laporan muncul otomatis saat penghuni mengirim laporan.</small>
            </label>
            <div class="form-grid-2">
                <label class="form-field">
                    <span>Tipe Kamar <b class="required-mark">*</b></span>
                    <select name="tipe" required>
                        <option value="" disabled @selected(!old('tipe'))>Pilih tipe kamar</option>
                        @foreach ($tipeKamar as $tipe)
                            <option value="{{ $tipe }}" @selected(old('tipe') === $tipe)>{{ $tipe }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="form-field">
                    <span>Ukuran <b class="required-mark">*</b></span>
                    <div class="size-inputs">
                        <label>
                            <input type="number" name="panjang" value="{{ old('panjang', 3) }}" min="1" max="20" step="0.5" required>
                            <small>panjang</small>
                        </label>
                        <b>×</b>
                        <label>
                            <input type="number" name="lebar" value="{{ old('lebar', 4) }}" min="1" max="20" step="0.5" required>
                            <small>lebar</small>
                        </label>
                        <em>m</em>
                    </div>
                </div>
            </div>
            <fieldset class="form-field facility-field">
                <span>Fasilitas <b class="required-mark">*</b></span>
                <div class="facility-options">
                    @foreach ($pilihanFasilitas as $fasilitas)
                        <label class="facility-option">
                            <input
                                type="checkbox"
                                name="fasilitas[]"
                                value="{{ $fasilitas }}"
                                data-add-facility
                                @checked(in_array($fasilitas, $fasilitasTerpilih, true))
                            >
                            <span>{{ $fasilitas }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <div class="modal-actions">
                <button class="btn-secondary" type="button" id="cancelTambahKamar">Batal</button>
                <button class="btn-submit" type="submit">Simpan Kamar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="editKamarModal" aria-hidden="true">
    <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="editKamarTitle">
        <div class="modal-header">
            <div>
                <p class="modal-eyebrow">Informasi Kamar</p>
                <h2 id="editKamarTitle">Edit Kamar</h2>
            </div>
            <button class="modal-close" type="button" id="closeEditKamar" aria-label="Tutup">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        @if ($errors->editKamar->any())
            <div class="form-alert">{{ $errors->editKamar->first() }}</div>
        @endif

        <form class="room-form" id="editKamarForm" method="POST" action="{{ route('pemilik.kamar.update', 0) }}">
            @csrf
            @method('PATCH')
            <div class="form-grid-2">
                <label class="form-field">
                    <span>Nomor Kamar</span>
                    <input type="text" name="nomor" id="editNomor" required>
                </label>
                <label class="form-field">
                    <span>Lantai</span>
                    <select name="lantai" id="editLantai" required>
                        @for ($lantai = 1; $lantai <= 5; $lantai++)
                            <option value="{{ $lantai }}">Lantai {{ $lantai }}</option>
                        @endfor
                    </select>
                </label>
            </div>
            <label class="form-field">
                <span>Harga per Bulan</span>
                <div class="currency-input">
                    <span>Rp</span>
                    <input type="text" id="editHargaDisplay" inputmode="numeric" autocomplete="off" required>
                    <input type="hidden" name="harga" id="editHargaValue">
                </div>
            </label>
            <label class="form-field">
                <span>Kondisi Kamar</span>
                <select name="status" id="editStatus" required></select>
                <small class="field-help">Status Ada Laporan dikelola otomatis dari laporan penghuni.</small>
            </label>
            <div class="form-grid-2">
                <label class="form-field">
                    <span>Tipe Kamar</span>
                    <select name="tipe" id="editTipe" required>
                        @foreach ($tipeKamar as $tipe)
                            <option value="{{ $tipe }}">{{ $tipe }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="form-field">
                    <span>Ukuran</span>
                    <div class="size-inputs">
                        <label>
                            <input type="number" name="panjang" id="editPanjang" min="1" max="20" step="0.5" required>
                            <small>panjang</small>
                        </label>
                        <b>×</b>
                        <label>
                            <input type="number" name="lebar" id="editLebar" min="1" max="20" step="0.5" required>
                            <small>lebar</small>
                        </label>
                        <em>m</em>
                    </div>
                </div>
            </div>
            <fieldset class="form-field facility-field">
                <span>Fasilitas</span>
                <div class="facility-options">
                    @foreach ($pilihanFasilitas as $fasilitas)
                        <label class="facility-option">
                            <input type="checkbox" name="fasilitas[]" value="{{ $fasilitas }}" data-edit-facility>
                            <span>{{ $fasilitas }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>
            <div class="modal-actions">
                <button class="btn-secondary" type="button" id="cancelEditKamar">Batal</button>
                <button class="btn-submit" type="submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const addRoomModal = document.getElementById('tambahKamarModal');
const openAddRoom = document.getElementById('openTambahKamar');
const closeAddRoom = document.getElementById('closeTambahKamar');
const cancelAddRoom = document.getElementById('cancelTambahKamar');
const shouldOpenAddRoom = @json(session('openTambahKamar', false));
const hargaDisplay = document.getElementById('hargaDisplay');
const hargaValue = document.getElementById('hargaValue');
const editRoomModal = document.getElementById('editKamarModal');
const editRoomForm = document.getElementById('editKamarForm');
const closeEditRoom = document.getElementById('closeEditKamar');
const cancelEditRoom = document.getElementById('cancelEditKamar');
const editHargaDisplay = document.getElementById('editHargaDisplay');
const editHargaValue = document.getElementById('editHargaValue');
const shouldOpenEditRoom = @json(session('openEditKamar'));
const updateRoomBaseUrl = @json(url('/pemilik/kamar'));
const tenantPageUrl = @json(route('pemilik.daftar-kamar'));
const csrfToken = @json(csrf_token());
const requestedRoomId = @json((string) request('kamar', ''));
const addRoomForm = addRoomModal?.querySelector('.room-form');
const addFacilityInputs = Array.from(document.querySelectorAll('[data-add-facility]'));

function formatRupiah(value) {
    const digits = String(value ?? '').replace(/\D/g, '');
    return digits ? Number(digits).toLocaleString('id-ID') : '';
}

function syncHarga(value) {
    const digits = String(value ?? '').replace(/\D/g, '');
    hargaValue.value = digits;
    hargaDisplay.value = formatRupiah(digits);
}

hargaDisplay?.addEventListener('input', (event) => syncHarga(event.target.value));
syncHarga(hargaValue?.value);

function syncEditHarga(value) {
    const digits = String(value ?? '').replace(/\D/g, '');
    editHargaValue.value = digits;
    editHargaDisplay.value = formatRupiah(digits);
}

editHargaDisplay?.addEventListener('input', (event) => syncEditHarga(event.target.value));

function showAddRoomModal() {
    if (!addRoomModal) return;
    addRoomModal.classList.add('open');
    addRoomModal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    addRoomModal.querySelector('input, button')?.focus();
}

function hideAddRoomModal() {
    if (!addRoomModal) return;
    addRoomModal.classList.remove('open');
    addRoomModal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
    openAddRoom?.focus();
}

openAddRoom?.addEventListener('click', showAddRoomModal);
closeAddRoom?.addEventListener('click', hideAddRoomModal);
cancelAddRoom?.addEventListener('click', hideAddRoomModal);
addRoomModal?.addEventListener('click', (event) => {
    if (event.target === addRoomModal) hideAddRoomModal();
});
document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    if (addRoomModal?.classList.contains('open')) hideAddRoomModal();
    if (editRoomModal?.classList.contains('open')) hideEditRoomModal();
});
if (shouldOpenAddRoom) showAddRoomModal();

function validateAddFacilities() {
    const firstFacility = addFacilityInputs[0];
    const hasSelectedFacility = addFacilityInputs.some(input => input.checked);

    firstFacility?.setCustomValidity(
        hasSelectedFacility ? '' : 'Pilih minimal satu fasilitas kamar.'
    );

    return hasSelectedFacility;
}

addFacilityInputs.forEach(input => input.addEventListener('change', validateAddFacilities));
addRoomForm?.addEventListener('submit', (event) => {
    if (!validateAddFacilities()) {
        event.preventDefault();
        addFacilityInputs[0]?.reportValidity();
    }
});

const roomData = @json($roomDetail);

function showEditRoomModal(roomId, useOldInput = false) {
    const room = roomData[roomId];
    if (!room || !editRoomModal) return;

    editRoomForm.action = `${updateRoomBaseUrl}/${roomId}`;
    document.getElementById('editNomor').value = useOldInput ? @json(old('nomor')) : room.nomor_kamar;
    document.getElementById('editLantai').value = useOldInput
        ? String(@json(old('lantai'))).replace(/\D/g, '')
        : String(room.lantai).replace(/\D/g, '');
    document.getElementById('editTipe').value = useOldInput ? @json(old('tipe')) : room.tipe;
    const statusSelect = document.getElementById('editStatus');
    const normalStatus = room.penghuni_id ? 'Terisi' : 'Kosong';
    statusSelect.innerHTML = room.penghuni_id
        ? '<option value="Terisi">Terisi</option><option value="Maintenance">Maintenance</option>'
        : '<option value="Kosong">Kosong - siap disewa</option><option value="Maintenance">Maintenance - sedang diperbaiki</option>';
    statusSelect.value = useOldInput
        ? @json(old('status'))
        : (room.status_asli === 'maintenance' ? 'Maintenance' : normalStatus);
    document.getElementById('editPanjang').value = useOldInput ? @json(old('panjang')) : room.panjang;
    document.getElementById('editLebar').value = useOldInput ? @json(old('lebar')) : room.lebar;
    syncEditHarga(useOldInput ? @json(old('harga')) : room.harga);

    const selectedFacilities = useOldInput ? @json((array) old('fasilitas', [])) : room.fasilitas;
    document.querySelectorAll('[data-edit-facility]').forEach((input) => {
        input.checked = selectedFacilities.includes(input.value);
    });

    editRoomModal.classList.add('open');
    editRoomModal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    document.getElementById('editNomor').focus();
}

function hideEditRoomModal() {
    editRoomModal?.classList.remove('open');
    editRoomModal?.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
}

closeEditRoom?.addEventListener('click', hideEditRoomModal);
cancelEditRoom?.addEventListener('click', hideEditRoomModal);
editRoomModal?.addEventListener('click', (event) => {
    if (event.target === editRoomModal) hideEditRoomModal();
});

async function deleteRoom(roomId) {
    const room = roomData[roomId];
    if (!room || !confirm(`Hapus Kamar ${room.nomor_kamar}? Tindakan ini tidak dapat dibatalkan.`)) return;

    const response = await fetch(`${updateRoomBaseUrl}/${roomId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
    });
    const result = await response.json();

    if (!response.ok) {
        alert(result.message || 'Kamar gagal dihapus.');
        return;
    }

    window.location.reload();
}

if (shouldOpenEditRoom) showEditRoomModal(shouldOpenEditRoom, true);

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
        d.harga ? 'Rp ' + Number(d.harga).toLocaleString('id-ID') : '-';
    document.getElementById('dSize').textContent  = d.ukuran || '-';
    document.getElementById('dType').textContent  = d.tipe   || '-';

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
    const lapUrl     = `{{ route('pemilik.laporan') }}`;
    const addTenantUrl = `${tenantPageUrl}?tambah_penghuni=1&kamar_id=${roomId}`;

    if (d.status === 'kosong') {
        actionsEl.innerHTML = `
            <button class="btn-outline" type="button"
                onclick="showEditRoomModal(${roomId})">Edit Kamar</button>
            <button class="btn-danger" type="button"
                onclick="deleteRoom(${roomId})">Hapus</button>
            <button class="btn-solid" type="button"
                onclick="window.location='${addTenantUrl}'">Tambah Penghuni</button>`;
    } else if (d.status === 'maintenance') {
        actionsEl.innerHTML = `
            <button class="btn-outline" type="button"
                onclick="showEditRoomModal(${roomId})">Edit Kamar</button>
            <button class="btn-solid" style="background:#8c2040;"
                onclick="window.location='${lapUrl}'">Lihat Laporan</button>`;
    } else if (d.status === 'laporan') {
        actionsEl.innerHTML = `
            <button class="btn-outline" type="button"
                onclick="showEditRoomModal(${roomId})">Edit Kamar</button>
            <button class="btn-solid" style="background:#1565c0;"
                onclick="window.location='${lapUrl}'">Lihat Laporan</button>`;
    } else {
        actionsEl.innerHTML = `
            <button class="btn-outline" type="button"
                onclick="showEditRoomModal(${roomId})">Edit Kamar</button>
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

// Pilih kamar dari halaman Daftar Kamar, atau kamar pertama sebagai fallback.
const targetCard = requestedRoomId
    ? document.querySelector(`.room-card[data-room="${requestedRoomId}"]`)
    : null;
const initialCard = targetCard || document.querySelector('.room-card');
if (initialCard) {
    initialCard.classList.add('selected');
    showDetail(initialCard.dataset.room);
    if (targetCard) {
        initialCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}
</script>
@endpush
