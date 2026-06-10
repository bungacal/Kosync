@extends('layouts.owner')

@section('title', 'Chat Penghuni')

@php($page = 'komunikasi')

@section('content')
  <section class="page active">
    <article class="panel chat-panel">
      <div class="panel-header">
        <div>
          <h2>{{ $penghuni->name }}</h2>
          <p class="muted">Kamar {{ $penghuni->kamar?->nomor ?? '-' }}</p>
        </div>
        <a class="ghost-button" href="{{ route('pemilik.komunikasi') }}">Kembali</a>
      </div>

      <div class="message-thread">
        @forelse ($messages as $message)
          <div class="message-bubble {{ $message->pengirim === 'owner' ? 'outgoing' : 'incoming' }}">
            <p>{{ $message->pesan }}</p>
            <small>{{ $message->pengirim === 'owner' ? 'Owner' : 'Penghuni' }} - {{ $message->created_at->format('d M H:i') }}</small>
          </div>
        @empty
          <div class="report-row"><div><h3>Belum ada pesan</h3><p>Mulai percakapan dengan penghuni ini.</p></div></div>
        @endforelse
      </div>

      <form class="message-form" method="POST" action="{{ route('pemilik.komunikasi.send', $penghuni) }}">
        @csrf
        <input type="text" name="pesan" placeholder="Tulis pesan..." required />
        <button class="primary-button" type="submit">Kirim</button>
      </form>
    </article>
  </section>
@endsection
