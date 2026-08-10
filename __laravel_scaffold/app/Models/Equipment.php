<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\EquipmentStatus;

class Equipment extends Model
{
    protected $fillable = [
        'name',
        'category_id', 
        'subcategory',
        'asset_tag',
        'type',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => EquipmentStatus::class,
        ];
    }

    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }
}
