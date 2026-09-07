<?php

namespace App\Models;

use App\Enums\EquipmentStatus;
use App\Enums\EquipmentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipment';

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
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => EquipmentType::class,
            'status' => EquipmentStatus::class,
            'allow_borrow' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }

    public function currentCustodian(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'current_custodian_id');
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function spot(): BelongsTo
    {
        return $this->belongsTo(Spot::class);
    }

    public function checkouts(): HasMany
    {
        return $this->hasMany(EquipmentCheckout::class);
    }

    public function consumableUsage(): HasMany
    {
        return $this->hasMany(ConsumableUsage::class);
    }

    public function neededByProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_equipment_needs')->withPivot('quantity_needed', 'notes');
    }
}
