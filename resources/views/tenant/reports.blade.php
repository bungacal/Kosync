@extends('layouts.tenant')

@section('title', 'Laporan Saya')

@php($page = 'reports')

@section('content')
  <section class="page active">
    <article class="panel">
      <div class="panel-header"><h2>Buat Laporan Baru</h2></div>
      <form class="report-form" method="POST" action="{{ route('tenant.reports.store') }}">
        @csrf
        <label class="field">
          <span>Nama Kos</span>
          <input type="text" value="{{ $tenant->kos?->name }}" disabled />
        </label>
        <label class="field">
          <span>Nomor Kamar</span>
          <input type="text" value="{{ $tenant->room?->number }}" disabled />
        </label>
        <label class="field span-2">
          <span>Kategori Kerusakan</span>
          <select name="category" required>
            <option value="" selected disabled>Pilih kategori</option>
            <option>Listrik</option>
            <option>Air</option>
            <option>AC</option>
            <option>Pintu</option>
            <option>Lainnya</option>
          </select>
        </label>
        <label class="field span-2">
          <span>Deskripsi Masalah</span>
          <textarea name="issue" placeholder="Contoh: Lampu kamar mati sejak pagi" required>{{ old('issue') }}</textarea>
        </label>
        @if ($errors->any())
          <div class="alert span-2">{{ $errors->first() }}</div>
        @endif
        @if (session('status'))
          <div class="status-pill done span-2">{{ session('status') }}</div>
        @endif
        <button class="success-button span-2" type="submit">Kirim Laporan</button>
      </form>
    </article>

    <article class="panel">
      <div class="panel-header"><h2>Laporan Aktif Saya</h2></div>
      <div class="report-list">
        @forelse ($activeReports as $report)
          <div class="report-row">
            <div><h3>{{ $report->issue }}</h3><p>{{ $report->category }} - Kamar {{ $report->room?->number }}</p></div>
            <span class="status-pill {{ $report->status === 'Diproses' ? 'process' : 'pending' }}">{{ $report->status }}</span>
          </div>
        @empty
          <div class="report-row"><div><h3>Belum ada laporan aktif</h3><p>Laporan yang belum selesai akan tampil di sini.</p></div></div>
        @endforelse
      </div>
    </article>
  </section>
@endsection
