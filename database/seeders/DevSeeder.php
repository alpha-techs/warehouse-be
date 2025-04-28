<?php

namespace Database\Seeders;

use Database\Seeders\dev\ProductSeeder;
use Database\Seeders\dev\WarehouseSeeder;
use Illuminate\Database\Seeder;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DatabaseSeeder::class,
            ProductSeeder::class,
            WarehouseSeeder::class,
        ]);
    }
}
