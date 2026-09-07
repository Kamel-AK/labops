<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'entity_type', 'entity_id', 'action', 'actor_type', 'performed_by',
        'description', 'old_values', 'new_values', 'created_at',
    ];

    protected function casts(): array
    {
        return ['old_values' => 'array', 'new_values' => 'array', 'created_at' => 'datetime'];
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'performed_by');
    }
}
