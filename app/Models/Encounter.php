<?php

namespace Tki\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Tki\Actions\Encounters\EncounterEvent;
use Tki\Types\EncounterType;
use Carbon\Carbon;

/**
 * @property int $id
 * @property Carbon|null $completed_at
 * @property int $movement_id
 * @property int $sector_id
 * @property int $user_id
 * @property EncounterType $type
 * @property array $data
 *
 * @property-read User $user
 * @property-read Universe $sector
 * @property-read MovementLog $movement
 */
class Encounter extends Model
{
    use HasFactory;

    protected $casts = [
        'type' => EncounterType::class,
        'completed_at' => 'datetime',
        'data' => 'json',
    ];

    protected $fillable = [
        'type',
        'sector_id',
        'user_id',
    ];

    public function action(): ?EncounterEvent
    {
        return $this->type->class($this);
    }

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

    public function persistData(array $data): void
    {
        $this->data = $data;
        $this->save();
    }
}
