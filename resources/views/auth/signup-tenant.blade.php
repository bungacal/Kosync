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
      <select name="kos_id" required>
        <option value="" selected disabled>Pilih kos</option>
        @foreach ($kosList as $kos)
          <option value="{{ $kos->id }}" @selected((int) old('kos_id') === $kos->id)>{{ $kos->nama }}</option>
        @endforeach
      </select>
    </label>
    <label class="field">
      <span>Nomor Kamar</span>
      <select name="kamar_id" required>
        <option value="" selected disabled>Pilih kamar kosong</option>
        @foreach ($kosList as $kos)
          @foreach ($kos->kamar as $kamar)
            <option value="{{ $kamar->id }}" @selected((int) old('kamar_id') === $kamar->id)>{{ $kos->nama }} - {{ $kamar->nomor }} ({{ $kamar->lantai }})</option>
          @endforeach
        @endforeach
      </select>
    </label>
    @if ($errors->any())
      <div class="alert">{{ $errors->first() }}</div>
    @endif
    <button class="success-button" type="submit">Daftar Sebagai Penghuni</button>
  </form>

  <p class="auth-link">Sudah punya akun? <a href="{{ route('login') }}">Login</a></p>
@endsection
