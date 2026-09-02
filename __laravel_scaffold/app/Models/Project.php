<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
        'lead_id',
        'requested_by',
        'approved_by',
        'approved_at',
        'start_date',
        'target_end_date',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'start_date' => 'date',
            'target_end_date' => 'date',
        ];
    }
}
