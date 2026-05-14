<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->role === 'owner'
        ? redirect()->route('owner.home')
        : redirect()->route('tenant.home');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/signup', [AuthController::class, 'showTenantSignup'])->name('register.tenant');
    Route::post('/signup', [AuthController::class, 'signupTenant'])->name('register.tenant.store');
    Route::get('/signup-pemilik', [AuthController::class, 'showOwnerSignup'])->name('register.owner');
    Route::post('/signup-pemilik', [AuthController::class, 'signupOwner'])->name('register.owner.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/pemilik', [OwnerController::class, 'home'])->name('owner.home');
    Route::get('/beranda-penghuni', [TenantController::class, 'home'])->name('tenant.home');
});
