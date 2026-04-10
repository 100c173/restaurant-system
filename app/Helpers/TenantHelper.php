<?php

namespace App\Helpers;

use Stancl\Tenancy\Facades\Tenancy;

class TenantHelper
{
    public static function cloudinaryFolder(string $subfolder = ''): string
    {
        $tenantId = tenant('id'); // e.g. "restaurant_42"
        $base = "tenants/{$tenantId}";
        
        return $subfolder ? "{$base}/{$subfolder}" : $base;
    }
}