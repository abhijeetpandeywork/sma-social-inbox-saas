<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformHealth extends Model
{
    use HasFactory;

    protected $table = 'platform_health';

    protected $fillable = [
        'platform',
        'consecutive_failures',
        'status',
        'last_checked_at',
    ];

    protected $casts = [
        'last_checked_at' => 'datetime',
    ];

    public function isHealthy(): bool
    {
        return $this->status === 'healthy';
    }

    public function isHeld(): bool
    {
        return $this->status === 'held';
    }
}
