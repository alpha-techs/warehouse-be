<?php

namespace App\Providers;

use App\Contracts\Services\ContainerServiceInterface;
use App\Contracts\Services\CustomerServiceInterface;
use App\Contracts\Services\DashboardServiceInterface;
use App\Contracts\Services\InboundServiceInterface;
use App\Contracts\Services\InventoryServiceInterface;
use App\Contracts\Services\OutboundServiceInterface;
use App\Contracts\Services\ProductServiceInterface;
use App\Services\ContainerService;
use App\Services\CustomerService;
use App\Services\DashboardService;
use App\Services\InboundService;
use App\Services\InventoryService;
use App\Services\OutboundService;
use App\Services\ProductService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Services
        $this->app->bind(ContainerServiceInterface::class, ContainerService::class);
        $this->app->bind(CustomerServiceInterface::class, CustomerService::class);
        $this->app->bind(DashboardServiceInterface::class, DashboardService::class);
        $this->app->bind(InboundServiceInterface::class, InboundService::class);
        $this->app->bind(InventoryServiceInterface::class, InventoryService::class);
        $this->app->bind(OutboundServiceInterface::class, OutboundService::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);

        // DB Query Logs
        DB::listen(function ($query) {
            $logId = request()?->attributes?->get('logId') ?? 'N/A';
            $sql = vsprintf(str_replace('?', "'%s'", $query->sql), $query->bindings);
            Log::channel('sql')->debug("{$sql} ({$query->time}ms)", [
                'logId' => $logId,
            ]);
        });
    }
}
