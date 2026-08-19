<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RateLimitCounter extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'platform',
        'window_start',
        'call_count',
    ];

    protected $casts = [
        'window_start' => 'datetime',
    ];
}
