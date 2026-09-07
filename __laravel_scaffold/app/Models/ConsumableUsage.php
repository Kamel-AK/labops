<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsumableUsage extends Model
{
    protected $table = 'consumable_usage';

    protected $fillable = ['equipment_id', 'member_id', 'project_id', 'quantity_used', 'quantity_remaining_after', 'notes'];

    protected function casts(): array
    {
        return ['quantity_used' => 'decimal:2', 'quantity_remaining_after' => 'decimal:2'];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
