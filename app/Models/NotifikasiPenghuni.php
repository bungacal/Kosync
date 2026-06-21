<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'laporan_perawatan_id',
    'broadcast_id',
    'tipe',
    'ikon',
    'warna',
    'judul',
    'isi',
    'url',
    'read_at',
])]
class NotifikasiPenghuni extends Model
{
    protected $table = 'notifikasi_penghuni';

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function penghuni(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanPerawatan::class, 'laporan_perawatan_id');
    }

    public function broadcast(): BelongsTo
    {
        return $this->belongsTo(Broadcast::class);
    }
}
