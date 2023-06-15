<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $quantity
 * @property-read Ship $ship
 * @property-read Universe $sector
 */
class SectorDefense extends Model
{
    use HasFactory;

    protected $fillable = [
        'quantity'
    ];

    public function ship(): BelongsTo
    {
        return $this->belongsTo(Ship::class);
    }

    // TODO: Rename to System?
    public function sector(): BelongsTo
    {
        return $this->belongsTo(Universe::class, 'sector_id');
    }
}
