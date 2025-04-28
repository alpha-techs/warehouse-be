<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $this->faker->addProvider(new JapaneseFoodProvider($this->faker));

        return [
            'name' => $this->faker->japaneseFood(),
            'sku' => $this->faker->unique()->bothify('SKU-####??'),
            'leaf_category_id' => $this->faker->numberBetween(1, 2),
            'cargo_mark' => $this->faker->optional()->lexify('MARK-????'),
            'dimension_description' => $this->faker->optional()->sentence,
            'length' => $this->faker->randomFloat(2, 1, 200),
            'width' => $this->faker->randomFloat(2, 1, 200),
            'height' => $this->faker->randomFloat(2, 1, 200),
            'unit_weight' => $this->faker->randomFloat(2, 0.1, 50),
            'total_weight' => $this->faker->randomFloat(2, 0.1, 100),
            'length_unit' => $this->faker->randomElement(['cm', 'm', 'mm']),
            'weight_unit' => $this->faker->randomElement(['kg', 'g']),
            'has_sub_package' => $this->faker->boolean,
            'sub_package_description' => $this->faker->optional()->sentence,
            'sub_package_count' => $this->faker->optional()->numberBetween(1, 10),
            'is_fixed_weight' => $this->faker->boolean,
            'is_active' => $this->faker->boolean(90), // 90% 概率为启用
        ];
    }
}
