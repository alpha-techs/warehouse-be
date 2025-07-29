<?php

namespace Database\Factories;

use App\Contracts\Models\InboundStatus;
use App\Models\InboundItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InboundItem>
 */
class InboundItemFactory extends Factory
{
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 1000);
        $perItemWeight = $this->faker->randomFloat(2, 0.1, 50);
        $totalWeight = $quantity * $perItemWeight;
        $shipName = $this->faker->lastName . '丸';

        return [
            'inbound_id' => $this->faker->numberBetween(1, 50),
            'inbound_date' => $this->faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'warehouse_id' => $this->faker->numberBetween(1, 3),
            'warehouse_name' => $this->faker->company . '仓库',
            'inbound_status' => InboundStatus::PENDING,
            'product_id' => $this->faker->numberBetween(1, 50),
            'product_name' => $this->faker->words(2, true),
            'quantity' => $quantity,
            'per_item_weight' => $perItemWeight,
            'per_item_weight_unit' => $this->faker->randomElement(['kg', 'g', 'lb']),
            'total_weight' => $totalWeight,
            'manufacture_date' => $this->faker->dateTimeBetween('-6 months', '-1 month')->format('Y-m-d'),
            'best_before_date' => $this->faker->dateTimeBetween('+1 month', '+2 years')->format('Y-m-d'),
            'lot_number' => $this->faker->unique()->bothify('LOT-####??'),
            'ship_name' => $shipName,
        ];
    }
}
