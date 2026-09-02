<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'category_id',
        'subcategory',
        'asset_tag',
        'type',
        'status',
        'current_custodian_id',
        'zone_id',
        'spot_id',
        'quantity_total',
        'quantity_available',
        'min_stock_threshold',
        'allow_borrow',
        'max_borrow_days',
        'photo_url',
        'manual_url',
    ];

    protected function casts(): array
    {
        return [
            'allow_borrow' => 'boolean',
        ];
    }
}
