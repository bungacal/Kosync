<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['kos_id', 'penghuni_id', 'kamar_id', 'masalah', 'kategori', 'status', 'assign', 'estimasi', 'nilai_rating', 'ulasan_rating', 'rated_at', 'selesai_pada'])]
class LaporanPerawatan extends Model
{
    protected $table = 'laporan_perawatan';

    protected function casts(): array
    {
        return [
            'rated_at' => 'datetime',
            'selesai_pada' => 'datetime',
        ];
    }

    public function kos(): BelongsTo
    {
        return $this->belongsTo(Kos::class);
    }

    public function penghuni(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penghuni_id');
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class, 'kamar_id');
    }
}
