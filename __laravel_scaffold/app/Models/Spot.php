<?php

namespace App\Models;

use App\Enums\SpotStatus;
use App\Enums\SpotType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Spot extends Model
{
    protected $fillable = [
        'zone_id',
        'name',
        'type',
        'status',
        'capacity',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'type' => SpotType::class,
            'status' => SpotStatus::class,
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
