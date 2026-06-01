<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['pemilik_id', 'nama'])]
class Kos extends Model
{
    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    public function kamar(): HasMany
    {
        return $this->hasMany(Kamar::class);
    }

    public function penghuni(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
