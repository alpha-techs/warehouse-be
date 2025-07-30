<?php

namespace Database\Factories;

use App\Contracts\Models\InboundStatus;
use App\Models\Inbound;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inbound>
 */
class InboundFactory extends Factory
{
    public function definition(): array
    {
        return [
            'inbound_order_id' => $this->faker->unique()->bothify('INB-####??'),
            'inbound_date' => $this->faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'warehouse_id' => $this->faker->numberBetween(1, 3),
            'warehouse_name' => $this->faker->company . '仓库',
            'customer_id' => 1,
            'customer_name' => '株式会社マルオカジャパン',
            'status' => InboundStatus::PENDING,
        ];
    }
}
