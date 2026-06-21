<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KomunikasiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PemilikController;
use App\Http\Controllers\PenghuniController;
use App\Http\Controllers\KamarController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->peran === 'pemilik'
        ? redirect()->route('pemilik.home')
        : redirect()->route('penghuni.home');
});
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/signup', [AuthController::class, 'showPenghuniSignup'])->name('register.penghuni');
    Route::post('/signup', [AuthController::class, 'signupPenghuni'])->name('register.penghuni.store');
    Route::get('/signup-pemilik', [AuthController::class, 'showPemilikSignup'])->name('register.pemilik');
    Route::post('/signup-pemilik', [AuthController::class, 'signupPemilik'])->name('register.pemilik.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/pemilik', [PemilikController::class, 'home'])->name('pemilik.home');
    Route::get('/pemilik/laporan', [LaporanController::class, 'index'])->name('pemilik.laporan');
    Route::patch('/pemilik/laporan/{laporan}', [LaporanController::class, 'update'])->name('pemilik.laporan.update');
    Route::get('/pemilik/komunikasi', [KomunikasiController::class, 'index'])->name('pemilik.komunikasi');
    Route::post('/pemilik/komunikasi/broadcast', [KomunikasiController::class, 'broadcast'])->name('pemilik.komunikasi.broadcast');
    Route::get('/pemilik/komunikasi/{penghuni}', [KomunikasiController::class, 'show'])->name('pemilik.komunikasi.show');
    Route::post('/pemilik/komunikasi/{penghuni}/send', [KomunikasiController::class, 'send'])->name('pemilik.komunikasi.send');
    Route::get('/beranda-penghuni', [PenghuniController::class, 'home'])->name('penghuni.home');
    Route::get('/laporan-saya', [PenghuniController::class, 'reports'])->name('penghuni.reports');
    Route::post('/laporan-saya', [PenghuniController::class, 'storeReport'])->name('penghuni.reports.store');
    Route::get('/pesan-saya', [PenghuniController::class, 'messages'])->name('penghuni.messages');
    Route::post('/pesan-saya', [PenghuniController::class, 'sendMessage'])->name('penghuni.messages.send');
    Route::get('/riwayat-laporan', [PenghuniController::class, 'history'])->name('penghuni.history');
    Route::post('/riwayat-laporan/{laporan}/rating', [PenghuniController::class, 'beriRating'])->name('penghuni.laporan.rating');
    Route::post('/notifikasi-saya/dibaca', [PenghuniController::class, 'markNotificationsRead'])->name('penghuni.notifications.read');
});
Route::middleware(['auth'])->group(function () {
    Route::get('/pemilik/daftar-kamar', [KamarController::class, 'daftarKamar'])
         ->name('pemilik.daftar-kamar');
    Route::get('/pemilik/denah-lantai', [KamarController::class, 'denahLantai'])
         ->name('pemilik.denah-lantai');
    Route::post('/pemilik/kamar', [KamarController::class, 'storeKamar'])
         ->name('pemilik.kamar.store');
    Route::patch('/pemilik/kamar/{id}', [KamarController::class, 'updateKamar'])
         ->name('pemilik.kamar.update');
    Route::post('/pemilik/penghuni', [KamarController::class, 'storePenghuni'])
         ->name('pemilik.penghuni.store');
    Route::delete('/pemilik/kamar/{id}', [KamarController::class, 'destroy'])
         ->name('pemilik.kamar.destroy');
    Route::delete('/pemilik/kamar', [KamarController::class, 'destroySelected'])
         ->name('pemilik.kamar.destroy-selected');
});
