<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kos_id', 'penghuni_id', 'nomor', 'lantai', 'status'])]
class Kamar extends Model
{
    protected $table = 'kamar';

    public function kos(): BelongsTo
    {
        return $this->belongsTo(Kos::class);
    }

    public function penghuni(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penghuni_id');
    }

    public function laporanPerawatan(): HasMany
    {
        return $this->hasMany(LaporanPerawatan::class, 'kamar_id');
    }
}
