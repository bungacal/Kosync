{{-- resources/views/laporan/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Manajemen Laporan')
@section('page-title', 'Manajemen Laporan')

@section('content')

    {{-- ===== FILTER BAR ===== --}}
    <div class="filter-bar">

        {{-- Filter: Status --}}
        <div class="filter-dropdown-wrap" id="wrap-status">
            <button class="filter-btn" id="btn-status"
                    onclick="toggleDropdown('menu-status', 'btn-status')"
                    aria-haspopup="listbox" aria-expanded="false">
                <span id="label-status">
                    {{ $selectedStatus ? $selectedStatus : 'Semua Status' }}
                </span>
                {{-- Icon: sliders --}}
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4"  y1="6"  x2="20" y2="6"/>
                    <line x1="8"  y1="12" x2="16" y2="12"/>
                    <line x1="11" y1="18" x2="13" y2="18"/>
                </svg>
            </button>

            <div class="filter-menu" id="menu-status" role="listbox">
                @foreach($statusOptions as $opt)
                    <div class="filter-menu-item {{ $selectedStatus === $opt ? 'selected' : '' }}"
                         role="option"
                         onclick="selectFilter('status', '{{ $opt }}', '{{ $opt }}',
                                               'label-status', 'menu-status', 'btn-status')">
                        {{ $opt }}
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Filter: Kategori --}}
        <div class="filter-dropdown-wrap" id="wrap-kategori">
            <button class="filter-btn" id="btn-kategori"
                    onclick="toggleDropdown('menu-kategori', 'btn-kategori')"
                    aria-haspopup="listbox" aria-expanded="false">
                <span id="label-kategori">
                    {{ $selectedKategori ? $selectedKategori : 'Semua Kategori' }}
                </span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4"  y1="6"  x2="20" y2="6"/>
                    <line x1="8"  y1="12" x2="16" y2="12"/>
                    <line x1="11" y1="18" x2="13" y2="18"/>
                </svg>
            </button>

            <div class="filter-menu" id="menu-kategori" role="listbox">
                @foreach($kategoriOptions as $opt)
                    <div class="filter-menu-item {{ $selectedKategori === $opt ? 'selected' : '' }}"
                         role="option"
                         onclick="selectFilter('kategori', '{{ $opt }}', '{{ $opt }}',
                                               'label-kategori', 'menu-kategori', 'btn-kategori')">
                        {{ $opt }}
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- ===== HIDDEN FORM untuk submit filter via GET ===== --}}
    <form id="filter-form" method="GET" action="{{ route('laporan.index') }}" style="display:none">
        <input type="hidden" name="status"   id="input-status"   value="{{ $selectedStatus }}">
        <input type="hidden" name="kategori" id="input-kategori" value="{{ $selectedKategori }}">
    </form>

    {{-- ===== TABLE CARD ===== --}}
    <div class="table-card">
        @if($laporan->isEmpty())
            <div class="empty-state">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="9" y="2" width="6" height="4" rx="1"/>
                    <path d="M7 4H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V6a2 2 0 00-2-2h-2"/>
                    <line x1="9" y1="12" x2="15" y2="12"/>
                    <line x1="9" y1="16" x2="13" y2="16"/>
                </svg>
                <p>Tidak ada laporan yang ditemukan.</p>
            </div>
        @else
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Penghuni</th>
                        <th>Kamar</th>
                        <th>Masalah</th>
                        <th>Status</th>
                        <th>Assign</th>
                        <th>Estimasi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($laporan as $item)
                        <tr>
                            <td class="td-name">{{ $item->penghuni }}</td>
                            <td class="td-room">{{ $item->kamar }}</td>
                            <td class="td-issue">{{ $item->masalah }}</td>
                            <td>
                                @php
                                    $statusClass = match(strtolower($item->status)) {
                                        'pending'  => 'badge-pending',
                                        'diproses' => 'badge-diproses',
                                        'selesai'  => 'badge-selesai',
                                        default    => '',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $item->status }}</span>
                            </td>
                            <td class="{{ $item->assign ? 'td-assign' : 'td-dash' }}">
                                {{ $item->assign ?? '—' }}
                            </td>
                            <td class="{{ $item->estimasi ? 'td-est' : 'td-dash' }}">
                                {{ $item->estimasi ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

@endsection

@push('scripts')
<script>
    /* ---- Dropdown toggle ---- */
    function toggleDropdown(menuId, btnId) {
        const menu = document.getElementById(menuId);
        const btn  = document.getElementById(btnId);
        const isOpen = menu.classList.contains('open');

        // Close all menus first
        document.querySelectorAll('.filter-menu').forEach(m => m.classList.remove('open'));
        document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('open');
            b.setAttribute('aria-expanded', 'false');
        });

        if (!isOpen) {
            menu.classList.add('open');
            btn.classList.add('open');
            btn.setAttribute('aria-expanded', 'true');
        }
    }

    /* ---- Select a filter option and submit form ---- */
    function selectFilter(inputName, value, label, labelId, menuId, btnId) {
        // Update visible label
        document.getElementById(labelId).textContent = label;

        // Update hidden input
        document.getElementById('input-' + inputName).value = value;

        // Close dropdown
        document.getElementById(menuId).classList.remove('open');
        document.getElementById(btnId).classList.remove('open');
        document.getElementById(btnId).setAttribute('aria-expanded', 'false');

        // Submit filter form
        document.getElementById('filter-form').submit();
    }

    /* ---- Close dropdowns when clicking outside ---- */
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.filter-dropdown-wrap')) {
            document.querySelectorAll('.filter-menu').forEach(m => m.classList.remove('open'));
            document.querySelectorAll('.filter-btn').forEach(b => {
                b.classList.remove('open');
                b.setAttribute('aria-expanded', 'false');
            });
        }
    });
</script>
@endpush