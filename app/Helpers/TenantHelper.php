<?php

namespace App\Helpers;

use Stancl\Tenancy\Facades\Tenancy;

class TenantHelper
{
    public static function cloudinaryFolder(string $subfolder = ''): string
    {
        $tenantId = tenant('id');
        $base = $tenantId ? "tenants/{$tenantId}" : "central";

        return $subfolder ? "{$base}/{$subfolder}" : $base;
    }
}