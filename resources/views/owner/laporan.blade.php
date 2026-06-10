@extends('layouts.owner')

@section('title', 'Manajemen Laporan')

@php($page = 'laporan')

@section('content')
  <section class="page active">
    @if (session('status'))
      <div class="status-pill done">{{ session('status') }}</div>
    @endif

    <form class="filter-bar" method="GET" action="{{ route('pemilik.laporan') }}">
      <label class="field filter-field">
        <span>Status</span>
        <select name="status" onchange="this.form.submit()">
          @foreach ($statusOptions as $option)
            <option value="{{ $option }}" @selected(($selectedStatus ?: 'Semua Status') === $option)>{{ $option }}</option>
          @endforeach
        </select>
      </label>
      <label class="field filter-field">
        <span>Kategori</span>
        <select name="kategori" onchange="this.form.submit()">
          @foreach ($kategoriOptions as $option)
            <option value="{{ $option }}" @selected(($selectedKategori ?: 'Semua Kategori') === $option)>{{ $option }}</option>
          @endforeach
        </select>
      </label>
    </form>

    <article class="panel table-panel">
      <div class="table-scroll">
        <table class="data-table">
          <thead>
            <tr>
              <th>Penghuni</th>
              <th>Kamar</th>
              <th>Masalah</th>
              <th>Status</th>
              <th>Assign</th>
              <th>Estimasi</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($laporan as $item)
              <tr>
                <td>{{ $item->penghuni?->name ?? '-' }}</td>
                <td>{{ $item->kamar?->nomor ?? '-' }}</td>
                <td><strong>{{ $item->masalah }}</strong><br><span class="muted">{{ $item->kategori }}</span></td>
                <td><span class="status-pill {{ $item->status === 'Selesai' ? 'done' : ($item->status === 'Diproses' ? 'process' : 'pending') }}">{{ $item->status }}</span></td>
                <td colspan="3">
                  <form class="inline-edit" method="POST" action="{{ route('pemilik.laporan.update', $item) }}">
                    @csrf
                    @method('PATCH')
                    <input type="text" name="assign" value="{{ old('assign', $item->assign) }}" placeholder="Petugas" />
                    <input type="text" name="estimasi" value="{{ old('estimasi', $item->estimasi) }}" placeholder="Estimasi" />
                    <select name="status">
                      @foreach (['Pending', 'Diproses', 'Selesai'] as $status)
                        <option value="{{ $status }}" @selected($item->status === $status)>{{ $status }}</option>
                      @endforeach
                    </select>
                    <button class="primary-button" type="submit">Simpan</button>
                  </form>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="empty-cell">Tidak ada laporan yang ditemukan.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </article>
  </section>
@endsection
