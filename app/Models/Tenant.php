<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'owner_id',
        'name',
        'status',
        'is_active',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
    /**
     * Define which attributes have dedicated columns
     * (others will go into the 'data' JSON column)
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'owner_id',
            'name',
            'email',
            'is_active',
        ];
    }
}