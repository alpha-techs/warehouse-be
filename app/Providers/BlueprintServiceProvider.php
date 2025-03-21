<?php

namespace App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

/**
 * @mixin Blueprint
 */
class BlueprintServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Blueprint::macro('address', function () {
            $this->string('postal_code')->nullable()->comment('邮政编码');
            $this->string('detail_address1')->nullable()->comment('详细地址1');
            $this->string('detail_address2')->nullable()->comment('详细地址2');
        });
    }
}
