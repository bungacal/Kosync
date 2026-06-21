@extends('layouts.owner')

@section('title', 'Daftar Kamar')

@php $page = 'daftar-kamar'; @endphp

@push('styles')
    @vite(['resources/css/daftar-kamar.css'])
@endpush

@section('content')

@if (session('success'))
    <div class="page-alert success">{{ session('success') }}</div>
@endif

{{-- Summary Chips --}}
<div class="summary-chips">
    <div class="chip chip-total">
        <span class="chip-dot"></span>
        Total {{ $totalKamar }} Kamar
    </div>
    <div class="chip chip-terisi">
        <span class="chip-dot"></span>
        Terisi {{ $terisi }}
    </div>
    <div class="chip chip-kosong">
        <span class="chip-dot"></span>
        Kosong {{ $kosong }}
    </div>
    <div class="chip chip-laporan">
        <span class="chip-dot"></span>
        Ada Laporan {{ $adaLaporan }}
    </div>
    <div class="chip chip-maintenance">
        <span class="chip-dot"></span>
        Maintenance {{ $maintenance }}
    </div>
</div>

{{-- Toolbar --}}
<div class="toolbar">
    <div class="toolbar-left">
        {{-- Search --}}
        <div class="search-wrap">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" class="search-input" id="searchInput" placeholder="Cari penghuni atau kamar...">
        </div>

        {{-- Filter Status --}}
        <select class="filter-select" id="filterStatus">
            <option value="">Semua Status</option>
            <option value="terisi">Terisi</option>
            <option value="kosong">Kosong</option>
            <option value="laporan">Ada Laporan</option>
            <option value="maintenance">Maintenance</option>
        </select>

        {{-- Filter Lantai --}}
        <select class="filter-select" id="filterLantai">
            <option value="">Semua Lantai</option>
            @foreach ($lantaiList as $lantai)
                <option value="{{ $lantai }}">{{ $lantai }}</option>
            @endforeach
        </select>
    </div>

    <div class="toolbar-right">
        <button class="btn-select-rooms" type="button" id="startSelection">
            <svg viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            Pilih Kamar
        </button>
        <button class="btn-cancel-selection selection-action" type="button" id="cancelSelection">
            Batal
        </button>
        <button class="btn-delete-selected selection-action" type="submit" form="bulkDeleteForm" id="deleteSelected" disabled>
            <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
            <span id="deleteSelectedLabel">Hapus Terpilih</span>
        </button>
        <button class="btn-add" type="button" id="openTambahPenghuni">
            <svg viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Penghuni
        </button>
    </div>
</div>

<div class="modal-backdrop" id="tambahPenghuniModal" aria-hidden="true">
    <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="tambahPenghuniTitle">
        <div class="modal-header">
            <div>
                <p class="modal-eyebrow">Penghuni Baru</p>
                <h2 id="tambahPenghuniTitle">Tambah Penghuni</h2>
            </div>
            <button class="modal-close" type="button" id="closeTambahPenghuni" aria-label="Tutup">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        @if ($errors->tambahPenghuni->any())
            <div class="form-alert">{{ $errors->tambahPenghuni->first() }}</div>
        @endif

        @if ($kamarKosong->isEmpty())
            <div class="form-alert">Belum ada kamar kosong. Tambahkan kamar atau kosongkan salah satu kamar dulu.</div>
        @endif

        <form class="tenant-form" method="POST" action="{{ route('pemilik.penghuni.store') }}">
            @csrf
            <label class="form-field">
                <span>Nama Penghuni</span>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Sari Wijaya" required>
            </label>

            <label class="form-field">
                <span>Email Login</span>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required>
            </label>

            <label class="form-field">
                <span>Password Awal</span>
                <input type="password" name="password" minlength="6" placeholder="Minimal 6 karakter" required>
            </label>

            <label class="form-field">
                <span>Kamar Kosong</span>
                <select name="kamar_id" required @disabled($kamarKosong->isEmpty())>
                    <option value="" disabled @selected(!old('kamar_id', request('kamar_id')))>
                        {{ $kamarKosong->isEmpty() ? 'Tidak ada kamar kosong' : 'Pilih kamar' }}
                    </option>
                    @foreach ($kamarKosong as $kamar)
                        <option
                            value="{{ $kamar->id }}"
                            @selected((int) old('kamar_id', request('kamar_id')) === $kamar->id)
                        >
                            Kamar {{ $kamar->nomor }} - {{ $kamar->lantai }}
                        </option>
                    @endforeach
                </select>
            </label>

            <div class="modal-actions">
                <button class="btn-secondary" type="button" id="cancelTambahPenghuni">Batal</button>
                <button class="btn-submit" type="submit" @disabled($kamarKosong->isEmpty())>Simpan Penghuni</button>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<form id="bulkDeleteForm" method="POST" action="{{ route('pemilik.kamar.destroy-selected') }}">
    @csrf
    @method('DELETE')
</form>

<div class="table-card">
    <table id="kamarTable">
        <thead>
            <tr>
                <th class="select-column">
                    <input class="row-checkbox" type="checkbox" id="selectAll" aria-label="Pilih semua kamar yang dapat dihapus">
                </th>
                <th>Penghuni</th>
                <th>Kamar</th>
                <th>Lantai</th>
                <th>Harga/bln</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($kamarList as $kamar)
            <tr
                data-room-id="{{ $kamar['id'] }}"
                data-status="{{ $kamar['status'] }}"
                data-lantai="{{ $kamar['lantai'] }}"
                data-search="{{ strtolower($kamar['penghuni'] ?? '') }} {{ $kamar['nomor_kamar'] }}"
            >
                <td class="select-column">
                    <input
                        class="row-checkbox room-select"
                        type="checkbox"
                        name="kamar_ids[]"
                        value="{{ $kamar['id'] }}"
                        form="bulkDeleteForm"
                        aria-label="Pilih kamar {{ $kamar['nomor_kamar'] }}"
                        @disabled(! $kamar['can_delete'])
                        @if(! $kamar['can_delete']) title="Kamar dengan penghuni atau riwayat laporan tidak dapat dihapus" @endif
                    >
                </td>
                {{-- Penghuni --}}
                <td>
                    @if ($kamar['penghuni'])
                        <div class="penghuni-cell">
                            <div class="penghuni-avatar" style="background:#f0e8ee; color:#422034;">
                                {{ strtoupper(substr($kamar['penghuni'], 0, 1)) }}
                            </div>
                            <span class="penghuni-name">{{ $kamar['penghuni'] }}</span>
                        </div>
                    @else
                        <span class="penghuni-empty">— Kosong</span>
                    @endif
                </td>

                {{-- Kamar --}}
                <td><span class="kamar-num">{{ $kamar['nomor_kamar'] }}</span></td>

                {{-- Lantai --}}
                <td><span class="lantai-badge">{{ $kamar['lantai'] }}</span></td>

                {{-- Harga --}}
                <td class="harga">
                    Rp {{ number_format($kamar['harga'], 0, ',', '.') }}
                    <span>/bln</span>
                </td>

                {{-- Status --}}
                <td>
                    @php
                        $statusMap = [
                            'terisi'      => ['label' => 'Terisi',      'class' => 'badge-terisi'],
                            'kosong'      => ['label' => 'Kosong',      'class' => 'badge-kosong'],
                            'laporan'     => ['label' => 'Ada Laporan', 'class' => 'badge-laporan'],
                            'maintenance' => ['label' => 'Maintenance', 'class' => 'badge-maintenance'],
                        ];
                        $s = $statusMap[$kamar['status']] ?? ['label' => ucfirst($kamar['status']), 'class' => ''];
                    @endphp
                    <span class="status-badge {{ $s['class'] }}">{{ $s['label'] }}</span>
                </td>

                {{-- Aksi --}}
                <td>
                    <div class="action-cell">
                        <a class="btn-detail" href="{{ route('pemilik.denah-lantai', ['kamar' => $kamar['id']]) }}">Detail</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7">
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                        <p>Belum ada data kamar.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination info --}}
    <div class="pagination">
        <span class="pagination-info" id="paginationInfo"></span>
        <div class="pagination-btns" id="paginationBtns"></div>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('tambahPenghuniModal');
    const openModal = document.getElementById('openTambahPenghuni');
    const closeModal = document.getElementById('closeTambahPenghuni');
    const cancelModal = document.getElementById('cancelTambahPenghuni');
    const shouldOpenModal = @json(session('openTambahPenghuni', false) || request()->boolean('tambah_penghuni'));

    function showModal() {
        if (!modal) return;
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        modal.querySelector('input, select, button')?.focus();
    }

    function hideModal() {
        if (!modal) return;
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        openModal?.focus();
    }

    openModal?.addEventListener('click', showModal);
    closeModal?.addEventListener('click', hideModal);
    cancelModal?.addEventListener('click', hideModal);
    modal?.addEventListener('click', (event) => {
        if (event.target === modal) hideModal();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal?.classList.contains('open')) hideModal();
    });
    if (shouldOpenModal) showModal();

    const PER_PAGE = 10;
    let currentPage = 1;

    const rows      = Array.from(document.querySelectorAll('#kamarTable tbody tr[data-status]'));
    const search    = document.getElementById('searchInput');
    const fStatus   = document.getElementById('filterStatus');
    const fLantai   = document.getElementById('filterLantai');
    const info      = document.getElementById('paginationInfo');
    const btns      = document.getElementById('paginationBtns');
    const selectAll = document.getElementById('selectAll');
    const startSelection = document.getElementById('startSelection');
    const cancelSelection = document.getElementById('cancelSelection');
    const deleteSelected = document.getElementById('deleteSelected');
    const deleteSelectedLabel = document.getElementById('deleteSelectedLabel');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');
    const roomCheckboxes = Array.from(document.querySelectorAll('.room-select:not(:disabled)'));
    const kamarTable = document.getElementById('kamarTable');

    function setSelectionMode(active) {
        kamarTable.classList.toggle('selection-mode', active);
        document.querySelectorAll('.selection-action').forEach(element => {
            element.classList.toggle('visible', active);
        });
        startSelection.hidden = active;

        if (!active) {
            roomCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }

        updateSelection();
    }

    function updateSelection() {
        const selected = roomCheckboxes.filter(checkbox => checkbox.checked);
        deleteSelected.disabled = selected.length === 0;
        deleteSelectedLabel.textContent = selected.length
            ? `Hapus Terpilih (${selected.length})`
            : 'Hapus Terpilih';
        selectAll.checked = roomCheckboxes.length > 0 && selected.length === roomCheckboxes.length;
        selectAll.indeterminate = selected.length > 0 && selected.length < roomCheckboxes.length;
    }

    selectAll?.addEventListener('change', () => {
        roomCheckboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        updateSelection();
    });

    startSelection?.addEventListener('click', () => setSelectionMode(true));
    cancelSelection?.addEventListener('click', () => setSelectionMode(false));
    roomCheckboxes.forEach(checkbox => checkbox.addEventListener('change', updateSelection));
    bulkDeleteForm?.addEventListener('submit', (event) => {
        const selected = roomCheckboxes.filter(checkbox => checkbox.checked).length;
        if (!selected || !confirm(`Hapus ${selected} kamar yang dipilih? Tindakan ini tidak dapat dibatalkan.`)) {
            event.preventDefault();
        }
    });

    function filtered() {
        const q  = search.value.toLowerCase();
        const st = fStatus.value;
        const lt = fLantai.value;
        return rows.filter(r =>
            (!q  || r.dataset.search.includes(q)) &&
            (!st || r.dataset.status === st) &&
            (!lt || r.dataset.lantai === lt)
        );
    }

    function render() {
        const visible = filtered();
        const total   = visible.length;
        const pages   = Math.max(1, Math.ceil(total / PER_PAGE));
        currentPage   = Math.min(currentPage, pages);
        const start   = (currentPage - 1) * PER_PAGE;
        const end     = start + PER_PAGE;

        rows.forEach(r => r.style.display = 'none');
        visible.forEach((r, i) => r.style.display = (i >= start && i < end) ? '' : 'none');

        info.textContent = total
            ? `Menampilkan ${start + 1}–${Math.min(end, total)} dari ${total} kamar`
            : 'Tidak ada kamar ditemukan';

        btns.innerHTML = '';

        // Prev
        const prev = document.createElement('button');
        prev.className = 'pg-btn';
        prev.innerHTML = `<svg viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>`;
        prev.disabled = currentPage === 1;
        prev.onclick = () => { currentPage--; render(); };
        btns.appendChild(prev);

        // Page numbers
        for (let p = 1; p <= pages; p++) {
            const b = document.createElement('button');
            b.className = 'pg-btn' + (p === currentPage ? ' active' : '');
            b.textContent = p;
            b.onclick = () => { currentPage = p; render(); };
            btns.appendChild(b);
        }

        // Next
        const next = document.createElement('button');
        next.className = 'pg-btn';
        next.innerHTML = `<svg viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>`;
        next.disabled = currentPage === pages;
        next.onclick = () => { currentPage++; render(); };
        btns.appendChild(next);
    }

    search.addEventListener('input',  () => { currentPage = 1; render(); });
    fStatus.addEventListener('change', () => { currentPage = 1; render(); });
    fLantai.addEventListener('change', () => { currentPage = 1; render(); });

    render();
})();
</script>

@endsection
