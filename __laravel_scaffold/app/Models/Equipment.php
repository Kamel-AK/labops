<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\EquipmentStatus;

class Equipment extends Model
{
    protected $fillable = ['equipment_category_id', 'asset_tag', 'name', 'model_number', 'status', 'notes'];

    protected function casts(): array
    {
        return [
            'status' => EquipmentStatus::class,
        ];
    }

    public function category()
    {
        return $this->belongsTo(EquipmentCategory::class, 'equipment_category_id');
    }
}
