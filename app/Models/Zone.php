<?php

namespace Tki\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read Universe[]|Collection $sectors
 */
class Zone extends Model
{
    use HasFactory;

    public function sectors(): HasMany
    {
        return $this->hasMany(Universe::class);
    }
}
