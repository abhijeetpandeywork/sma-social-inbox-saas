<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([TenantScope::class])]
class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'platform',
        'source_comment_id',
        'source_dm_id',
        'contact_phone',
        'contact_name',
        'contact_handle',
        'status',
        'score',
        'source_post_id',
        'duplicate_of_lead_id',
        'assigned_to',
        'notes',
        'captured_at',
    ];

    protected $casts = [
        'contact_phone' => 'encrypted',
        'captured_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(TeamMember::class, 'assigned_to');
    }

    public function parentLead()
    {
        return $this->belongsTo(Lead::class, 'duplicate_of_lead_id');
    }

    public function escalations()
    {
        return $this->hasMany(SlaEscalation::class);
    }
}
