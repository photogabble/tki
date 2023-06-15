<?php

namespace Tki\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// TODO Rename as Sector or System?
class Universe extends Model
{
    use HasFactory;

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function planets(): HasMany
    {
        return $this->hasMany(Planet::class, 'sector_id');
    }
}
