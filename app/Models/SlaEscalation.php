<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaEscalation extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'sla_deadline',
        'escalated_to',
        'escalated_at',
        'resolved_at',
    ];

    protected $casts = [
        'sla_deadline' => 'datetime',
        'escalated_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
