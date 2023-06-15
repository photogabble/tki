<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ship extends Model
{
    use HasFactory;

    protected $fillable = [
        'sector_id',
        'cleared_defenses',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    // TODO: Has One BankAccount
}
