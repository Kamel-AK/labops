<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
        'start_date',
        'target_end_date',
        'lead_id',
        'requested_by',
        'approved_by',
        'priority',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'target_end_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'lead_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'approved_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'project_members')
            ->withPivot('role_in_project', 'joined_at');
    }

    public function equipmentNeeds(): BelongsToMany
    {
        return $this->belongsToMany(Equipment::class, 'project_equipment_needs')
            ->using(ProjectEquipmentNeed::class)
            ->withPivot('quantity_needed', 'notes');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function isLedBy(Member $member): bool
    {
        return (int) $this->lead_id === (int) $member->id;
    }

    public function hasMember(Member $member): bool
    {
        return $this->members()->whereKey($member->id)->exists();
    }
}
