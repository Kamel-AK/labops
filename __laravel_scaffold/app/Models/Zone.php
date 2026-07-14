<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
    protected $fillable = [
        'name',
        'description',
        'color_code',
        'operating_hour_start',
        'operating_hour_end',
        'status',
    ];

    
    public function spots(): HasMany
    {
        return $this->hasMany(Spot::class);
    }
}
