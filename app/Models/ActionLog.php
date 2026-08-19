<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionLog extends Model
{
    use HasFactory;

    protected $table = 'action_log';
    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'platform',
        'action_type',
        'target_id',
        'status',
        'error_message',
        'attempt_count',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
