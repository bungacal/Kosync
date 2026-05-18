@extends('layouts.auth')

@section('title', 'Signup Pemilik')

@section('content')
  <div class="auth-heading">
    <p class="eyebrow">Akun Pemilik</p>
    <h1>Signup Pemilik</h1>
  </div>

  <form class="form-grid" method="POST" action="{{ route('register.pemilik.store') }}">
    @csrf
    <label class="field"><span>Nama Pemilik</span><input type="text" name="name" value="{{ old('name') }}" required /></label>
    <label class="field"><span>Nama Kos</span><input type="text" name="kos_name" value="{{ old('kos_name') }}" required /></label>
    <label class="field"><span>Email</span><input type="email" name="email" value="{{ old('email') }}" required /></label>
    <label class="field"><span>Password</span><input type="password" name="password" minlength="6" required /></label>
    @if ($errors->any())
      <div class="alert">{{ $errors->first() }}</div>
    @endif
    <button class="primary-button" type="submit">Daftar Sebagai Pemilik</button>
  </form>

  <p class="auth-link">Sudah punya akun? <a href="{{ route('login') }}">Login</a></p>
@endsection
