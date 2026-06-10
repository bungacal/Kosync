{{-- resources/views/komunikasi/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Komunikasi')
@section('page-title', 'Komunikasi')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/komunikasi.css') }}">
@endpush

@section('content')

<div class="komunikasi-grid">

    {{-- ===== KIRI: Chat Dengan Penghuni ===== --}}
    <div class="card chat-list" style="padding:0">
        <div class="card-title" style="padding:28px 28px 16px; margin-bottom:0">
            Chat Dengan Penghuni
        </div>

        @forelse($chats as $chat)
            @php
                $initials = strtoupper(substr($chat->penghuni, 0, 2));
                $avatarClass = 'avatar-' . strtolower(str_replace(' ','', substr($chat->penghuni,0,2)));
            @endphp
            <a href="{{ route('komunikasi.show', $chat->penghuni_id) }}" class="chat-item">
                <div class="chat-avatar {{ $avatarClass }}">{{ $initials }}</div>
                <div class="chat-info">
                    <div class="chat-name">{{ $chat->penghuni }}</div>
                    <div class="chat-preview">{{ $chat->last_message }}</div>
                </div>
                <div class="chat-time">{{ $chat->last_time }}</div>
            </a>
        @empty
            <div style="padding:40px 28px; text-align:center; color:#aaa; font-size:14px;">
                Belum ada percakapan.
            </div>
        @endforelse
    </div>

    {{-- ===== KANAN: Broadcast ===== --}}
    <div class="card">

        {{-- Header + filter --}}
        <div class="broadcast-header">
            <h2>Broadcast</h2>
            <div class="bc-filter-wrap">
                <button class="bc-filter-btn" id="bc-btn"
                        onclick="toggleBcFilter()"
                        aria-haspopup="listbox" aria-expanded="false">
                    <span id="bc-label">{{ $selectedTarget ?? 'Semua Penghuni' }}</span>
                    {{-- Icon sliders --}}
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4"  y1="6"  x2="20" y2="6"/>
                        <line x1="8"  y1="12" x2="16" y2="12"/>
                        <line x1="11" y1="18" x2="13" y2="18"/>
                    </svg>
                </button>

                <div class="bc-filter-menu" id="bc-menu" role="listbox">
                    @foreach($targetOptions as $opt)
                        <div class="bc-filter-item {{ ($selectedTarget ?? 'Semua Penghuni') === $opt ? 'selected' : '' }}"
                             role="option"
                             onclick="selectBcTarget('{{ $opt }}')">
                            {{ $opt }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Form broadcast --}}
        <form method="POST" action="{{ route('komunikasi.broadcast') }}">
            @csrf
            <input type="hidden" name="target" id="bc-target-input"
                   value="{{ $selectedTarget ?? 'Semua Penghuni' }}">

            <textarea class="bc-textarea" name="pesan"
                      placeholder="Tulis broadcast di sini"
                      rows="5">{{ old('pesan') }}</textarea>

            @error('pesan')
                <p style="color:#c0392b; font-size:12px; margin-top:6px;">{{ $message }}</p>
            @enderror

            <div class="bc-send-row">
                <button type="submit" class="btn-broadcast">Broadcast</button>
            </div>
        </form>

        {{-- Riwayat broadcast --}}
        <div class="bc-history-title">Riwayat Broadcast</div>
        <div class="bc-history-list">
            @forelse($riwayatBroadcast as $rb)
                <div class="bc-history-item">
                    <div class="bc-dot {{ $loop->first ? 'bc-dot-blue' : 'bc-dot-yellow' }}"></div>
                    <div>
                        <div class="bc-history-text">{{ $rb->pesan }}</div>
                        <div class="bc-history-meta">{{ $rb->tanggal }} | {{ $rb->target }}</div>
                    </div>
                </div>
            @empty
                <p style="font-size:13px; color:#aaa;">Belum ada riwayat broadcast.</p>
            @endforelse
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
    function toggleBcFilter() {
        const menu = document.getElementById('bc-menu');
        const btn  = document.getElementById('bc-btn');
        const open = menu.classList.toggle('open');
        btn.setAttribute('aria-expanded', open);
    }

    function selectBcTarget(value) {
        document.getElementById('bc-label').textContent        = value;
        document.getElementById('bc-target-input').value       = value;
        document.getElementById('bc-menu').classList.remove('open');
        document.getElementById('bc-btn').setAttribute('aria-expanded', 'false');
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.bc-filter-wrap')) {
            document.getElementById('bc-menu').classList.remove('open');
            document.getElementById('bc-btn').setAttribute('aria-expanded', 'false');
        }
    });
</script>
@endpush