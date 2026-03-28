<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Stancl\Tenancy\Events\TenancyInitialized;
use Stancl\Tenancy\Events\TenantCreated;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Override Livewire update and script routes — بدون tenancy middleware
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)
                ->middleware(['web']);
        });

        Livewire::setScriptRoute(function ($handle) {
            return Route::get('/livewire/livewire.js', $handle)
                ->middleware(['web']);
        });

        // Patch upload-file and preview-file — بس إذا الطلب من tenant domain
        $this->app->booted(function () {
            collect(app('router')->getRoutes())->each(function ($route) {
                if (
                    in_array($route->uri(), [
                        'livewire/upload-file',
                        'livewire/preview-file/{filename}',
                    ])
                ) {
                    $route->middleware([
                        'web',
                        InitializeTenancyByDomain::class,
                    ]);
                }
            });
        });

        // Fix tenant_uploads URL dynamically when tenancy is initialized
        Event::listen(TenancyInitialized::class, function ($event) {
            $tenantId = $event->tenancy->tenant->id;

            // استخدم الدومين الحالي بدل APP_URL
            $currentDomain = request()->getSchemeAndHttpHost();

            config([
                'filesystems.disks.tenant_uploads.url' =>
                    $currentDomain . '/tenant-image/' . $tenantId,
            ]);

            app('filesystem')->forgetDisk('tenant_uploads');
        });
        // Tenant directory scaffolding on creation
        Event::listen(TenantCreated::class, function (TenantCreated $event) {
            $tenantId = $event->tenant->id;

            $directories = [
                storage_path("tenant{$tenantId}/framework/cache"),
                storage_path("tenant{$tenantId}/framework/sessions"),
                storage_path("tenant{$tenantId}/framework/views"),
                storage_path("tenant{$tenantId}/logs"),
                storage_path("tenant{$tenantId}/app/public/uploads/livewire-tmp"),
                storage_path("tenant{$tenantId}/app/public/uploads/categories/logo"),
            ];

            foreach ($directories as $path) {
                if (!file_exists($path)) {
                    mkdir($path, 0775, true);
                }
            }

            if (PHP_OS_FAMILY !== 'Windows') {
                $symlinkPath = public_path("storage/tenant{$tenantId}");
                $targetPath = storage_path("tenant{$tenantId}/app/public");

                if (!file_exists($symlinkPath)) {
                    symlink($targetPath, $symlinkPath);
                }
            }
        });
    }
}