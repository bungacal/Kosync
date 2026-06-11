@extends('layouts.auth')

@section('title', 'Signup Penghuni')

@section('content')
  <div class="auth-heading">
    <p class="eyebrow">Akun Penghuni</p>
    <h1>Signup Penghuni</h1>
  </div>

  <form class="form-grid" method="POST" action="{{ route('register.penghuni.store') }}">
    @csrf
    <label class="field">
      <span>Nama Lengkap</span>
      <input type="text" name="name" value="{{ old('name') }}" required />
    </label>
    <label class="field">
      <span>Email</span>
      <input type="email" name="email" value="{{ old('email') }}" required />
    </label>
    <label class="field">
      <span>Password</span>
      <input type="password" name="password" minlength="6" required />
    </label>

    {{-- Dropdown pilih kos --}}
    <label class="field">
      <span>Nama Kos</span>
      <select name="kos_id" id="select-kos" required>
        <option value="" selected disabled>Pilih kos</option>
        @foreach ($kosList as $kos)
          <option value="{{ $kos->id }}" @selected((int) old('kos_id') === $kos->id)>
            {{ $kos->nama }}
          </option>
        @endforeach
      </select>
    </label>

    {{-- Dropdown pilih kamar — difilter by kos lewat JS --}}
    <label class="field">
      <span>Nomor Kamar</span>
      <select name="kamar_id" id="select-kamar" required disabled>
        <option value="" selected disabled>Pilih kos terlebih dahulu</option>
      </select>
    </label>

    @if ($errors->any())
      <div class="alert">{{ $errors->first() }}</div>
    @endif

    <button class="success-button" type="submit">Daftar Sebagai Penghuni</button>
  </form>

  <p class="auth-link">Sudah punya akun? <a href="{{ route('login') }}">Login</a></p>

  {{-- Data kamar semua kos dalam bentuk JSON untuk dipakai JS --}}
  <script>
    // Semua kamar kosong dikelompokkan per kos_id
    const kamarPerKos = @json(
      $kosList->mapWithKeys(fn($kos) => [
        $kos->id => $kos->kamar->map(fn($kamar) => [
          'id'     => $kamar->id,
          'nomor'  => $kamar->nomor,
          'lantai' => $kamar->lantai,
        ])
      ])
    );

    const selectedKosId  = {{ (int) old('kos_id', 0) }};
    const selectedKamarId = {{ (int) old('kamar_id', 0) }};

    const selectKos   = document.getElementById('select-kos');
    const selectKamar = document.getElementById('select-kamar');

    function updateKamar(kosId) {
      // Reset dropdown kamar
      selectKamar.innerHTML = '';

      const kamarList = kamarPerKos[kosId] ?? [];

      if (kamarList.length === 0) {
        // Tidak ada kamar kosong di kos ini
        selectKamar.disabled = true;
        const opt = document.createElement('option');
        opt.value    = '';
        opt.disabled = true;
        opt.selected = true;
        opt.textContent = 'Tidak ada kamar tersedia';
        selectKamar.appendChild(opt);
        return;
      }

      // Tambahkan placeholder
      const placeholder = document.createElement('option');
      placeholder.value    = '';
      placeholder.disabled = true;
      placeholder.selected = true;
      placeholder.textContent = 'Pilih nomor kamar';
      selectKamar.appendChild(placeholder);

      // Isi kamar sesuai kos yang dipilih
      kamarList.forEach(kamar => {
        const opt = document.createElement('option');
        opt.value       = kamar.id;
        opt.textContent = `Kamar ${kamar.nomor} (${kamar.lantai})`;

        // Pertahankan pilihan lama jika ada validasi error
        if (kamar.id === selectedKamarId) {
          opt.selected = true;
          placeholder.selected = false;
        }

        selectKamar.appendChild(opt);
      });

      selectKamar.disabled = false;
    }

    // Jalankan saat kos dipilih
    selectKos.addEventListener('change', function () {
      updateKamar(parseInt(this.value));
    });

    // Kalau ada old('kos_id') setelah validasi error, langsung tampilkan kamarnya
    if (selectedKosId) {
      selectKos.value = selectedKosId;
      updateKamar(selectedKosId);
    }
  </script>
@endsection
