<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Member extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'full_name',
        'email',
        'email_verified_at',
        'password_hash',
        'phone',
        'role',
        'access_status',
        'skills',
        'certifications',
        'emergency_contact',
        'join_date',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'skills' => 'array',
            'certifications' => 'array',
            'join_date' => 'date',
            'password_hash' => 'hashed',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function isCoordinator(): bool
    {
        return $this->role === 'coordinator';
    }

    public function isTeamLead(): bool
    {
        return $this->role === 'team_lead';
    }

    public function isVolunteer(): bool
    {
        return $this->role === 'volunteer';
    }

    public function hasGrantedAccess(): bool
    {
        return $this->access_status === 'granted';
    }

    public function ledProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'lead_id');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members')
            ->withPivot('role_in_project', 'joined_at');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function equipmentCheckouts(): HasMany
    {
        return $this->hasMany(EquipmentCheckout::class);
    }
}
