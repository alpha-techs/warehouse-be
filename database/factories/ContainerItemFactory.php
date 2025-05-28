<?php

namespace Database\Factories;

use App\Models\ContainerItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContainerItem>
 */
class ContainerItemFactory extends Factory
{
    public function definition(): array
    {
        $product = Product::inRandomOrder()->first();

        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => $this->faker->numberBetween(1, 100),
            'manufacture_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'best_before_date' => $this->faker->dateTimeBetween('now', '+2 years'),
        ];
    }
}
