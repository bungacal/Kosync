<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kos_id', 'tenant_id', 'number', 'floor', 'status'])]
class Room extends Model
{
    public function kos(): BelongsTo
    {
        return $this->belongsTo(Kos::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(MaintenanceReport::class);
    }
}
