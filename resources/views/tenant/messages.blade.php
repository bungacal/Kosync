@extends('layouts.tenant')

@section('title', 'Pesan Saya')

@php($page = 'messages')

@section('content')
  <section class="page active">
    <div class="two-column">
      <article class="panel chat-panel">
        <div class="panel-header"><h2>Chat Dengan Owner</h2></div>
        <div class="message-thread">
          @forelse ($messages as $message)
            <div class="message-bubble {{ $message->pengirim === 'penghuni' ? 'outgoing' : 'incoming' }}">
              <p>{{ $message->pesan }}</p>
              <small>{{ $message->pengirim === 'penghuni' ? 'Penghuni' : 'Owner' }} - {{ $message->created_at->format('d M H:i') }}</small>
            </div>
          @empty
            <div class="report-row"><div><h3>Belum ada pesan</h3><p>Kirim pesan pertama ke owner kos.</p></div></div>
          @endforelse
        </div>
        <form class="message-form" method="POST" action="{{ route('penghuni.messages.send') }}">
          @csrf
          <input type="text" name="pesan" placeholder="Tulis pesan..." required />
          <button class="primary-button" type="submit">Kirim</button>
        </form>
      </article>

      <article class="panel">
        <div class="panel-header"><h2>Broadcast</h2></div>
        <div class="report-list">
          @forelse ($broadcasts as $broadcast)
            <div class="report-row">
              <div><h3>{{ $broadcast->target }}</h3><p>{{ $broadcast->pesan }}</p></div>
              <span class="status-pill">{{ $broadcast->tanggal }}</span>
            </div>
          @empty
            <div class="report-row"><div><h3>Belum ada broadcast</h3><p>Pengumuman owner akan tampil di sini.</p></div></div>
          @endforelse
        </div>
      </article>
    </div>
  </section>
@endsection
