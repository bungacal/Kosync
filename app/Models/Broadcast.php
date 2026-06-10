<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['kos_id', 'pesan', 'target', 'tanggal'])]
class Broadcast extends Model
{
    protected $table = 'broadcast';

    public function kos(): BelongsTo
    {
        return $this->belongsTo(Kos::class);
    }
}
