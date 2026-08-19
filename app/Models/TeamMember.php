<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class TeamMember extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'team_members';

    protected $fillable = [
        'agency_id',
        'name',
        'email',
        'password',
        'role',
        'assigned_clients',
        'two_factor_enabled',
        'two_factor_secret',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    protected $casts = [
        'assigned_clients' => 'array',
        'two_factor_enabled' => 'boolean',
        'password' => 'hashed',
    ];

    public function isAgencyAdmin(): bool
    {
        return $this->role === 'Agency Admin';
    }

    public function isClientManager(): bool
    {
        return $this->role === 'Client Manager';
    }

    public function isTeamExecutive(): bool
    {
        return $this->role === 'Team Executive';
    }

    public function canAccessClient(int $clientId): bool
    {
        if ($this->isAgencyAdmin()) {
            return true;
        }

        $assigned = $this->assigned_clients ?? [];
        return in_array($clientId, $assigned);
    }
}
