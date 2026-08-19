<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PiiAccessLog extends Model
{
    use HasFactory;

    protected $table = 'pii_access_log';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'lead_id',
        'action',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
