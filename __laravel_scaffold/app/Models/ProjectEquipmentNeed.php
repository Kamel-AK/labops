<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectEquipmentNeed extends Pivot
{
    public $incrementing = false;

    protected $table = 'project_equipment_needs';

    protected $fillable = ['project_id', 'equipment_id', 'quantity_needed', 'notes'];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}
