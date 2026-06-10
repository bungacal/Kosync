{{-- resources/views/komunikasi/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Komunikasi – ' . $penghuni->nama)
@section('page-title', 'Komunikasi')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/komunikasi.css') }}">
@endpush

@section('content')

<div class="chat-detail-card">

    {{-- ===== Header ===== --}}
    <div class="chat-detail-header">
        <a href="{{ route('komunikasi.index') }}"
           style="color:inherit; text-decoration:none; display:flex; align-items:center; margin-right:4px;">
            {{-- Icon back --}}
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
        </a>

        @php
            $initials    = strtoupper(substr($penghuni->nama, 0, 2));
            $avatarClass = 'avatar-' . strtolower(str_replace(' ','', substr($penghuni->nama,0,2)));
        @endphp
        <div class="chat-avatar {{ $avatarClass }}">{{ $initials }}</div>
        <div class="chat-name">{{ $penghuni->nama }}</div>
    </div>

    {{-- ===== Messages ===== --}}
    <div class="chat-messages" id="chat-messages">
        @foreach($messages as $msg)
            @php $isOwner = $msg->pengirim === 'owner'; @endphp
            <div class="bubble-wrap {{ $isOwner ? 'outgoing' : 'incoming' }}">
                <div class="bubble">{{ $msg->pesan }}</div>
                <div class="bubble-meta">
                    {{ $isOwner ? 'Owner' : 'Penghuni' }} · {{ $msg->waktu }}
                    @if($isOwner)
                        <span class="bubble-check">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2.5"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- ===== Input area ===== --}}
    <div class="chat-input-area">
        <form method="POST" action="{{ route('komunikasi.send', $penghuni->id) }}"
              style="display:flex; align-items:center; gap:12px; flex:1;">
            @csrf

            <input type="text" name="pesan" class="chat-input"
                   placeholder="Tulis pesan..." autocomplete="off" required />

            <button type="submit" class="chat-send-btn" aria-label="Kirim">
                {{-- Icon send --}}
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"/>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </button>
        </form>

        {{-- Tombol kamera --}}
        <button type="button" class="chat-action-btn" aria-label="Kamera">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/>
                <circle cx="12" cy="13" r="4"/>
            </svg>
        </button>

        {{-- Tombol lampiran --}}
        <button type="button" class="chat-action-btn" aria-label="Lampiran">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19
                         a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/>
            </svg>
        </button>
    </div>

</div>

@endsection

@push('scripts')
<script>
    // Auto-scroll ke bawah saat halaman dimuat
    const chatBox = document.getElementById('chat-messages');
    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
</script>
@endpush