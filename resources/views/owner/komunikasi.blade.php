@extends('layouts.owner')

@section('title', 'Komunikasi')

@php($page = 'komunikasi')

@section('content')
  <section class="page active">
    @if (session('status'))
      <div class="status-pill done">{{ session('status') }}</div>
    @endif

    <div class="two-column">
      <article class="panel">
        <div class="panel-header"><h2>Chat Dengan Penghuni</h2></div>
        <div class="chat-list-panel">
          @forelse ($chats as $chat)
            <a class="chat-row" href="{{ route('pemilik.komunikasi.show', $chat) }}">
              <span class="avatar">{{ strtoupper(substr($chat->name, 0, 1)) }}</span>
              <span>
                <strong>{{ $chat->name }}</strong>
                <small>Kamar {{ $chat->kamar?->nomor ?? '-' }} - {{ $chat->last_message }}</small>
              </span>
              <em>{{ $chat->last_time }}</em>
            </a>
          @empty
            <div class="report-row"><div><h3>Belum ada penghuni</h3><p>Chat akan tersedia setelah penghuni terdaftar.</p></div></div>
          @endforelse
        </div>
      </article>

      <article class="panel">
        <div class="panel-header"><h2>Broadcast</h2></div>
        <form class="form-grid" method="POST" action="{{ route('pemilik.komunikasi.broadcast') }}">
          @csrf
          <label class="field">
            <span>Target</span>
            <select name="target">
              @foreach ($targetOptions as $option)
                <option value="{{ $option }}" @selected($selectedTarget === $option)>{{ $option }}</option>
              @endforeach
            </select>
          </label>
          <label class="field">
            <span>Pesan</span>
            <textarea name="pesan" placeholder="Tulis broadcast di sini" required>{{ old('pesan') }}</textarea>
          </label>
          <button class="primary-button" type="submit">Broadcast</button>
        </form>

        <div class="mt-3 report-list">
          @forelse ($riwayatBroadcast as $broadcast)
            <div class="report-row">
              <div><h3>{{ $broadcast->target }}</h3><p>{{ $broadcast->pesan }}</p></div>
              <span class="status-pill">{{ $broadcast->tanggal }}</span>
            </div>
          @empty
            <div class="report-row"><div><h3>Belum ada riwayat</h3><p>Broadcast yang terkirim akan tampil di sini.</p></div></div>
          @endforelse
        </div>
      </article>
    </div>
  </section>
@endsection
