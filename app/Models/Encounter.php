<?php

namespace Tki\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tki\Types\EncounterType;

/**
 * @property-read User $user
 * @property-read Universe $sector
 * @property-read MovementLog $movement
 * @property Carbon|null $completed_at
 * @property int $sector_id
 * @property int $user_id
 * @property EncounterType $type
 */
class Encounter extends Model
{
    use HasFactory;

    protected $casts = [
        'type' => EncounterType::class,
        'completed_at' => 'datetime',
    ];

    protected $fillable = [
        'type',
        'sector_id',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Universe::class, 'sector_id');
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(MovementLog::class, 'movement_id');
    }
}
