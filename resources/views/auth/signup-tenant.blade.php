@extends('layouts.auth')

@section('title', 'Signup Penghuni')

@section('content')
  <div class="auth-heading">
    <p class="eyebrow">Akun Penghuni</p>
    <h1>Signup Penghuni</h1>
  </div>

  <form class="form-grid" method="POST" action="{{ route('register.penghuni.store') }}">
    @csrf
    <label class="field"><span>Nama Lengkap</span><input type="text" name="name" value="{{ old('name') }}" required /></label>
    <label class="field"><span>Email</span><input type="email" name="email" value="{{ old('email') }}" required /></label>
    <label class="field"><span>Password</span><input type="password" name="password" minlength="6" required /></label>
    <label class="field">
      <span>Nama Kos</span>
      <select name="kos_id" id="select-kos" required>
        <option value="" selected disabled>Pilih kos</option>
        @foreach ($kosList as $kos)
          <option value="{{ $kos->id }}" @selected((int) old('kos_id') === $kos->id)>{{ $kos->nama }}</option>
        @endforeach
      </select>
    </label>
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

  @php
    $kamarPerKos = $kosList->mapWithKeys(function ($kos) {
      return [
        $kos->id => $kos->kamar->map(function ($kamar) {
          return [
            'id' => $kamar->id,
            'nomor' => $kamar->nomor,
            'lantai' => $kamar->lantai,
          ];
        })->values(),
      ];
    });
  @endphp

  <script>
    const kamarPerKos = {{ Illuminate\Support\Js::from($kamarPerKos) }};
    const selectedKosId = {{ (int) old('kos_id', 0) }};
    const selectedKamarId = {{ (int) old('kamar_id', 0) }};
    const selectKos = document.getElementById('select-kos');
    const selectKamar = document.getElementById('select-kamar');

    function updateKamar(kosId) {
      selectKamar.innerHTML = '';

      const kamarList = kamarPerKos[kosId] || [];
      if (!kamarList.length) {
        const option = document.createElement('option');
        option.value = '';
        option.disabled = true;
        option.selected = true;
        option.textContent = 'Tidak ada kamar kosong';
        selectKamar.appendChild(option);
        selectKamar.disabled = true;
        return;
      }

      const placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.disabled = true;
      placeholder.selected = true;
      placeholder.textContent = 'Pilih kamar kosong';
      selectKamar.appendChild(placeholder);

      kamarList.forEach((kamar) => {
        const option = document.createElement('option');
        option.value = kamar.id;
        option.textContent = `Kamar ${kamar.nomor} (${kamar.lantai})`;

        if (kamar.id === selectedKamarId) {
          option.selected = true;
          placeholder.selected = false;
        }

        selectKamar.appendChild(option);
      });

      selectKamar.disabled = false;
    }

    selectKos.addEventListener('change', (event) => updateKamar(event.target.value));

    if (selectedKosId) {
      updateKamar(selectedKosId);
    }
  </script>
@endsection
