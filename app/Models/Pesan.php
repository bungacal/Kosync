<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['kos_id', 'penghuni_id', 'pengirim', 'pesan'])]
class Pesan extends Model
{
    protected $table = 'pesan';

    public function kos(): BelongsTo
    {
        return $this->belongsTo(Kos::class);
    }

    public function penghuni(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penghuni_id');
    }
}
