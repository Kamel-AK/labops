<?php

namespace App\Models;

use App\Enums\ZoneStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color_code',
        'status',
        'operating_hours_start',
        'operating_hours_end',
    ];

    protected function casts(): array
    {
        return [
            'status' => ZoneStatus::class,
        ];
    }

    public function spots(): HasMany
    {
        return $this->hasMany(Spot::class);
    }
}
