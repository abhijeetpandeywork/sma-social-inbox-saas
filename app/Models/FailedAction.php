<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedAction extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'connection',
        'queue',
        'payload',
        'exception',
        'client_id',
        'action_type',
        'attempt_count',
        'failed_at',
    ];

    protected $casts = [
        'failed_at' => 'datetime',
    ];
}
