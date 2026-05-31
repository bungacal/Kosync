<?php

// =============================================================
// app/Models/Laporan.php
// =============================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    protected $table = 'laporan';

    protected $fillable = [
        'penghuni',   // string  – nama penghuni
        'kamar',      // string  – nomor / nama kamar (e.g. "101", "Lobby")
        'masalah',    // string  – deskripsi masalah
        'kategori',   // string  – Listrik | Plumbing | AC | …
        'status',     // string  – Pending | Diproses | Selesai
        'assign',     // string  – nama teknisi / petugas (nullable)
        'estimasi',   // string  – estimasi penyelesaian (nullable)
    ];
}


// =============================================================
// database/migrations/xxxx_create_laporan_table.php  (contoh)
// =============================================================

/*
Schema::create('laporan', function (Blueprint $table) {
    $table->id();
    $table->string('penghuni');
    $table->string('kamar');
    $table->string('masalah');
    $table->string('kategori')->nullable();
    $table->string('status')->default('Pending');   // Pending | Diproses | Selesai
    $table->string('assign')->nullable();
    $table->string('estimasi')->nullable();
    $table->timestamps();
});
*/


// =============================================================
// routes/web.php  – tambahkan route berikut
// =============================================================

/*
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManajemenKosController;
use App\Http\Controllers\KomunikasiController;
use App\Http\Controllers\AnalyticsController;

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard',      [DashboardController::class,   'index'])->name('dashboard');
    Route::get('/laporan',        [LaporanController::class,     'index'])->name('laporan.index');
    Route::get('/manajemen-kos',  [ManajemenKosController::class,'index'])->name('manajemen-kos.index');
    Route::get('/komunikasi',     [KomunikasiController::class,  'index'])->name('komunikasi.index');
    Route::get('/analytics',      [AnalyticsController::class,   'index'])->name('analytics.index');

});
*/