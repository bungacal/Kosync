@extends('layouts.auth')

@section('title', 'Login')

@section('content')
  <div class="auth-heading">
    <p class="eyebrow">Masuk Akun</p>
    <h1>Login</h1>
  </div>

  <form class="form-grid" method="POST" action="{{ route('login.store') }}">
    @csrf
    <label class="field">
      <span>Login Sebagai</span>
      <select name="peran" required>
        <option value="penghuni" @selected(old('peran', 'penghuni') === 'penghuni')>Penghuni Kos</option>
        <option value="pemilik" @selected(old('peran') === 'pemilik')>Pemilik Kos</option>
      </select>
    </label>
    <label class="field">
      <span>Email</span>
      <input type="email" name="email" value="{{ old('email') }}" required />
    </label>
    <label class="field">
      <span>Password</span>
      <input type="password" name="password" required />
    </label>
    @if ($errors->any())
      <div class="alert">{{ $errors->first() }}</div>
    @endif
    <button class="primary-button" type="submit">Login</button>
  </form>

  <div class="auth-links">
    <a href="{{ route('register.penghuni') }}">Signup Penghuni</a>
    <a href="{{ route('register.pemilik') }}">Signup Pemilik</a>
  </div>
@endsection
