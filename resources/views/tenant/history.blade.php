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
								{{-- @dd($laporan) --}}
								@forelse ($laporan as $report)
										<div class="history-row">
												<div>
														<h3>{{ $report->masalah }}</h3>
														<p>{{ $report->kategori }} - Kamar {{ $report->kamar?->nomor }} -
																{{ $report->selesai_pada?->format('d M Y') ?? $report->updated_at->format('d M Y') }}</p>
														<span class="status-pill done">Selesai</span>
												</div>
												@if ($report->nilai_rating)
														<strong>
																@for ($i = 1; $i <= 5; $i++)
																		{{ $i <= $report->nilai_rating ? '⭐' : '☆' }}
																@endfor
														</strong>
												@else
														<form class="rating-form" method="POST" action="{{ route('penghuni.laporan.rating', $report) }}">
																@csrf

																<div class="rating-stars">
																		@for ($i = 5; $i >= 1; $i--)
																				<input type="radio" id="star{{ $i }}-{{ $report->id }}" name="nilai_rating"
																						value="{{ $i }}" {{ $i == 5 ? 'checked' : '' }}>

																				<label for="star{{ $i }}-{{ $report->id }}">★</label>
																		@endfor
																</div>

																<button class="primary-button" type="submit">
																		Kirim Rating
																</button>
														</form>
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
