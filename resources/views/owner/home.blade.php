@extends('layouts.owner')

@section('title', 'Dashboard Pemilik')

@php($page = 'home')

@section('content')
  <section class="page active">
    <div class="row-between panel">
      <div>
        <p class="eyebrow">Pemilik Kos</p>
        <h2>{{ $kos?->nama ?? 'Kos belum dibuat' }}</h2>
        <p class="muted">Kelola laporan, kamar, dan komunikasi penghuni dari satu tempat.</p>
      </div>
      <a class="primary-button" href="{{ route('pemilik.laporan') }}">Kelola Laporan</a>
    </div>

    <div class="stats-grid">
      <article class="stat-card"><span>Total Kamar</span><strong>{{ $kos?->kamar->count() ?? 0 }}</strong><small>Unit kamar</small></article>
      <article class="stat-card"><span>Kamar Terisi</span><strong>{{ $kos?->kamar->where('status', 'Terisi')->count() ?? 0 }}</strong><small>Penghuni aktif</small></article>
      <article class="stat-card"><span>Laporan Aktif</span><strong>{{ $laporanAktif->count() }}</strong><small>Pending / Diproses</small></article>
    </div>

    <article class="panel">
      <div class="panel-header">
        <h2>Laporan Aktif</h2>
        <a class="ghost-button" href="{{ route('pemilik.laporan') }}">Lihat Semua</a>
      </div>
      <div class="report-list">
        @forelse ($laporanAktif->take(5) as $report)
          <div class="report-row">
            <div>
              <h3>{{ $report->masalah }}</h3>
              <p>{{ $report->penghuni?->name }} - Kamar {{ $report->kamar?->nomor }} - {{ $report->kategori }}</p>
            </div>
            <span class="status-pill {{ $report->status === 'Diproses' ? 'process' : 'pending' }}">{{ $report->status }}</span>
          </div>
        @empty
          <div class="report-row"><div><h3>Belum ada laporan aktif</h3><p>Laporan penghuni akan tampil di sini.</p></div></div>
        @endforelse
      </div>
    </article>

    <article class="panel">
      <div class="panel-header">
        <h2>Broadcast Terbaru</h2>
        <a class="ghost-button" href="{{ route('pemilik.komunikasi') }}">Buka Komunikasi</a>
      </div>
      <div class="report-list">
        @forelse ($broadcastTerbaru as $broadcast)
          <div class="report-row">
            <div>
              <h3>{{ $broadcast->target }}</h3>
              <p>{{ $broadcast->pesan }}</p>
            </div>
            <span class="status-pill">{{ $broadcast->tanggal }}</span>
          </div>
        @empty
          <div class="report-row"><div><h3>Belum ada broadcast</h3><p>Pengumuman ke penghuni akan tampil di sini.</p></div></div>
        @endforelse
      </div>
    </article>
  </section>
@endsection
