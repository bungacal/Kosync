@extends('layouts.tenant')

@section('title', 'Riwayat Laporan')

@php($page = 'history')

@section('content')
    <section class="page active">
        @if (session('status'))
            <div class="status-pill done">{{ session('status') }}</div>
        @endif

        <article class="panel">
            <div class="panel-header">
                <h2>Riwayat Laporan Selesai</h2>
            </div>
            <div class="history-list">
                @forelse ($laporan as $report)
                    <div class="history-row">
                        <div>
                            <h3>{{ $report->masalah }}</h3>
                            <p>{{ $report->kategori }} - Kamar {{ $report->kamar?->nomor }} -
                                {{ $report->selesai_pada?->format('d M Y') ?? $report->updated_at->format('d M Y') }}</p>
                            <span class="status-pill done">Selesai</span>
                        </div>
                        @if ($report->nilai_rating)
                            <div class="review-summary">
                                <strong>
                                    @for ($i = 1; $i <= 5; $i++)
                                        {!! $i <= $report->nilai_rating ? '&#9733;' : '&#9734;' !!}
                                    @endfor
                                </strong>
                                @if ($report->ulasan_rating)
                                    <small>{{ $report->ulasan_rating }}</small>
                                @endif
                            </div>
                        @else
                            <a class="primary-button" href="{{ route('penghuni.history', ['rating' => $report->id]) }}">
                                Beri Rating
                            </a>
                        @endif
                    </div>
                @empty
                    <div class="history-row">
                        <div>
                            <h3>Belum ada riwayat</h3>
                            <p>Laporan selesai akan tampil di sini.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </article>
    </section>
@endsection
