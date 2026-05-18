@extends('layouts.tenant')

@section('title', 'Riwayat Laporan')

@php($page = 'history')

@section('content')
  <section class="page active">
    @if (session('status'))
      <div class="status-pill done">{{ session('status') }}</div>
    @endif

    <article class="panel">
      <div class="panel-header"><h2>Riwayat Laporan Selesai</h2></div>
      <div class="history-list">
        @forelse ($laporan as $report)
          <div class="history-row">
            <div>
              <h3>{{ $report->masalah }}</h3>
              <p>{{ $report->kategori }} - Kamar {{ $report->kamar?->nomor }} - {{ $report->selesai_pada?->format('d M Y') ?? $report->updated_at->format('d M Y') }}</p>
              <span class="status-pill done">Selesai</span>
            </div>
            @if ($report->nilai_rating)
              <strong>Rating {{ $report->nilai_rating }}/5</strong>
            @else
              <form class="row-between" method="POST" action="{{ route('penghuni.laporan.rating', $report) }}">
                @csrf
                <input type="number" name="nilai_rating" min="1" max="5" value="5" aria-label="Rating" />
                <button class="primary-button" type="submit">Kirim Rating</button>
              </form>
            @endif
          </div>
        @empty
          <div class="history-row"><div><h3>Belum ada riwayat</h3><p>Laporan selesai akan tampil di sini.</p></div></div>
        @endforelse
      </div>
    </article>
  </section>
@endsection
