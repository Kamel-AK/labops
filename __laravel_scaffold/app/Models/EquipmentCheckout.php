<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentCheckout extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'member_id',
        'reservation_id',
        'project_id',
        'checked_out_at',
        'expected_return_at',
        'actual_return_at',
        'status',
        'checkout_type',
        'return_condition',
    ];

    protected function casts(): array
    {
        return [
            'checked_out_at' => 'datetime',
            'expected_return_at' => 'datetime',
            'actual_return_at' => 'datetime',
        ];
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

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }
}
