<?php

namespace Database\Seeders\dev;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory()->count(120)->create();
    }
}
