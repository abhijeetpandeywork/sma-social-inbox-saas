<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([TenantScope::class])]
class AutomationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'platform',
        'trigger_type',
        'trigger_value',
        'action_type',
        'reply_template_variants',
        'business_hours_variant',
        'is_active',
    ];

    protected $casts = [
        'reply_template_variants' => 'array',
        'business_hours_variant' => 'array',
        'is_active' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
