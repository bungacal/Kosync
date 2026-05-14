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
      <select name="role" required>
        <option value="tenant" @selected(old('role', 'tenant') === 'tenant')>Penghuni Kos</option>
        <option value="owner" @selected(old('role') === 'owner')>Pemilik Kos</option>
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
    <a href="{{ route('register.tenant') }}">Signup Penghuni</a>
    <a href="{{ route('register.owner') }}">Signup Pemilik</a>
  </div>
@endsection
