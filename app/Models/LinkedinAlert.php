<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([TenantScope::class])]
class LinkedinAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'alert_type',
        'source_url',
        'status',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
