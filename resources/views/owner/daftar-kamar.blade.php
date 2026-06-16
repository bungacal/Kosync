@extends('layouts.owner')

@section('title', 'Manajemen Kos')

@php $page = 'daftar-kamar'; @endphp

@push('styles')
    @vite(['resources/css/daftar-kamar.css'])
@endpush

@section('content')
<section class="owner-section">
    <div class="section-heading">
        <div>
            <p class="eyebrow">Manajemen Kos</p>
            <h2>Daftar Kamar</h2>
            <p class="muted">Pantau kamar, penghuni, status hunian, dan laporan aktif.</p>
        </div>
        <a class="primary-button" href="{{ route('pemilik.denah-lantai') }}">Lihat Denah Lantai</a>
    </div>

    <div class="stats-grid">
        <article class="stat-card"><span>Total Kamar</span><strong>{{ $totalKamar }}</strong><small>Unit kamar</small></article>
        <article class="stat-card"><span>Terisi</span><strong>{{ $terisi }}</strong><small>Penghuni aktif</small></article>
        <article class="stat-card"><span>Kosong</span><strong>{{ $kosong }}</strong><small>Siap ditempati</small></article>
        <article class="stat-card"><span>Laporan Aktif</span><strong>{{ $adaLaporan }}</strong><small>Perlu tindak lanjut</small></article>
    </div>

    <div class="table-card">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kamar</th>
                    <th>Lantai</th>
                    <th>Penghuni</th>
                    <th>Status</th>
                    <th>Harga</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kamarList as $kamar)
                    <tr>
                        <td>Kamar {{ $kamar['nomor_kamar'] }}</td>
                        <td>{{ $kamar['lantai'] }}</td>
                        <td>{{ $kamar['penghuni'] ?? '-' }}</td>
                        <td><span class="status-pill status-{{ $kamar['status'] }}">{{ ucfirst($kamar['status']) }}</span></td>
                        <td>Rp {{ number_format($kamar['harga'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Belum ada data kamar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
