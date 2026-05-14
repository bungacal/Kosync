@extends('layouts.tenant')

@section('title', 'Beranda')

@php($page = 'home')

@section('content')
  <section class="page active">
    <div class="row-between panel">
      <div>
        <p class="eyebrow">Selamat Datang</p>
        <h2>{{ $tenant->name }}</h2>
        <p class="muted">{{ $tenant->kos?->name }} - Kamar {{ $tenant->room?->number ?? '-' }}</p>
      </div>
      <a class="primary-button" href="{{ route('tenant.reports') }}">Buat Laporan</a>
    </div>

    <div class="stats-grid">
      <article class="stat-card"><span>Kos</span><strong>{{ $tenant->kos?->name ?? '-' }}</strong><small>Nama kos</small></article>
      <article class="stat-card"><span>Kamar</span><strong>{{ $tenant->room?->number ?? '-' }}</strong><small>{{ $tenant->room?->floor ?? '-' }}</small></article>
      <article class="stat-card"><span>Laporan Aktif</span><strong>{{ $activeReports->count() }}</strong><small>Pending / Diproses</small></article>
    </div>

    <article class="panel">
      <div class="panel-header"><h2>Laporan Aktif Saya</h2></div>
      <div class="report-list">
        @forelse ($activeReports as $report)
          <div class="report-row">
            <div><h3>{{ $report->issue }}</h3><p>{{ $report->category }} - Kamar {{ $report->room?->number }}</p></div>
            <span class="status-pill {{ $report->status === 'Diproses' ? 'process' : 'pending' }}">{{ $report->status }}</span>
          </div>
        @empty
          <div class="report-row"><div><h3>Belum ada laporan aktif</h3><p>Laporan baru akan tampil di sini.</p></div></div>
        @endforelse
      </div>
    </article>
  </section>
@endsection
