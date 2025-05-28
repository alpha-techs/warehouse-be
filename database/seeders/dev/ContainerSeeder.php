<?php

namespace Database\Seeders\dev;

use App\Models\Container;
use Illuminate\Database\Seeder;

class ContainerSeeder extends Seeder
{
    public function run(): void
    {
        Container::factory()
            ->count(20)
            ->hasItems(rand(1, 5)) // 每个集装箱随机生成1-5个商品项
            ->create();
    }
}
