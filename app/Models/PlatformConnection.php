<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([TenantScope::class])]
class PlatformConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'platform',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'last_successful_call_at',
        'health_status',
        'platform_account_id',
        'connected_by',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'token_expires_at' => 'datetime',
        'last_successful_call_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
