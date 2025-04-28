<?php

namespace App\Providers;

use App\Contracts\Services\CustomerServiceInterface;
use App\Contracts\Services\InventoryServiceInterface;
use App\Contracts\Services\ProductServiceInterface;
use App\Services\CustomerService;
use App\Services\InventoryService;
use App\Services\ProductService;
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
        $this->app->bind(CustomerServiceInterface::class, CustomerService::class);
        $this->app->bind(InventoryServiceInterface::class, InventoryService::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);
    }
}
