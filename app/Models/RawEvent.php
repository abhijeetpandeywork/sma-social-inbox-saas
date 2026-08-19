<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'platform',
        'event_type',
        'event_hash',
        'payload_json',
        'processed',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'processed' => 'boolean',
    ];
}
