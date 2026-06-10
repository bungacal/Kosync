<?php

// =============================================================
// app/Models/Penghuni.php
// =============================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Penghuni extends Model
{
    protected $table = 'penghuni';

    protected $fillable = ['nama', 'kamar', 'lantai'];

    // Relasi ke pesan
    public function pesans()
    {
        return $this->hasMany(Pesan::class, 'penghuni_id');
    }

    // Scope: sertakan pesan terakhir
    public function scopeWithLastMessage(Builder $query): Builder
    {
        return $query->addSelect([
            'last_message' => Pesan::select('pesan')
                ->whereColumn('penghuni_id', 'penghuni.id')
                ->latest()
                ->limit(1),
            'last_message_at' => Pesan::select('created_at')
                ->whereColumn('penghuni_id', 'penghuni.id')
                ->latest()
                ->limit(1),
            'last_time' => Pesan::selectRaw("TIME_FORMAT(created_at, '%H.%i')")
                ->whereColumn('penghuni_id', 'penghuni.id')
                ->latest()
                ->limit(1),
        ]);
    }
}


// =============================================================
// app/Models/Pesan.php
// =============================================================

/*
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesan extends Model
{
    protected $table = 'pesan';

    protected $fillable = [
        'penghuni_id',  // int FK
        'pengirim',     // 'owner' | 'penghuni'
        'pesan',        // string
    ];

    public function penghuni()
    {
        return $this->belongsTo(Penghuni::class, 'penghuni_id');
    }
}
*/


// =============================================================
// app/Models/Broadcast.php
// =============================================================

/*
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    protected $table = 'broadcast';

    protected $fillable = [
        'pesan',    // string – isi broadcast
        'target',   // string – "Semua Penghuni" | "Lantai 1" | "Lantai 2"
        'tanggal',  // string – label tanggal tampil (e.g. "Hari ini", "19 April")
    ];
}
*/


// =============================================================
// database/migrations  (contoh)
// =============================================================

/*
// create_penghuni_table
Schema::create('penghuni', function (Blueprint $table) {
    $table->id();
    $table->string('nama');
    $table->string('kamar');
    $table->string('lantai')->nullable();
    $table->timestamps();
});

// create_pesan_table
Schema::create('pesan', function (Blueprint $table) {
    $table->id();
    $table->foreignId('penghuni_id')->constrained('penghuni')->cascadeOnDelete();
    $table->enum('pengirim', ['owner', 'penghuni'])->default('penghuni');
    $table->text('pesan');
    $table->timestamps();
});

// create_broadcast_table
Schema::create('broadcast', function (Blueprint $table) {
    $table->id();
    $table->text('pesan');
    $table->string('target')->default('Semua Penghuni');
    $table->string('tanggal');
    $table->timestamps();
});
*/


// =============================================================
// routes/web.php  – tambahkan route berikut
// =============================================================

/*
use App\Http\Controllers\KomunikasiController;

Route::middleware(['auth'])->group(function () {

    Route::get( '/komunikasi',                  [KomunikasiController::class, 'index'])     ->name('komunikasi.index');
    Route::get( '/komunikasi/{penghuni}',        [KomunikasiController::class, 'show'])      ->name('komunikasi.show');
    Route::post('/komunikasi/{penghuni}/send',   [KomunikasiController::class, 'send'])      ->name('komunikasi.send');
    Route::post('/komunikasi/broadcast',         [KomunikasiController::class, 'broadcast']) ->name('komunikasi.broadcast');

});
*/