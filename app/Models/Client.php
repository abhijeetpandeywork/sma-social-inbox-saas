<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_id',
        'name',
        'brand_logo',
        'status',
        'data_retention_months',
    ];

    public function platformConnections()
    {
        return $this->hasMany(PlatformConnection::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function automationRules()
    {
        return $this->hasMany(AutomationRule::class);
    }
}
