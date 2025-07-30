<?php

namespace Database\Seeders\dev;

use App\Models\Inbound;
use Illuminate\Database\Seeder;

class InboundSeeder extends Seeder
{
    public function run(): void
    {
        Inbound::factory()
            ->count(50)
            ->hasItems(rand(1, 5)) // 每个入库单随机生成1-5个商品项
            ->create();
    }
}
